<?php

namespace App\Services\Reservation;

use App\Models\Invoice;
use App\Models\Reservation;
use Ccharz\LaravelEpcQr\EPCQR;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sprain\SwissQrBill\DataGroup\Element\AdditionalInformation;
use Sprain\SwissQrBill\DataGroup\Element\StructuredAddress;
use Sprain\SwissQrBill\DataGroup\Element\CreditorInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentAmountInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentReference;
use Sprain\SwissQrBill\PaymentPart\Output\HtmlOutput\HtmlOutput;
use Sprain\SwissQrBill\QrBill;
use Sprain\SwissQrBill\Reference\QrPaymentReferenceGenerator;

class PDFService
{
    /**
     * Get the WebDAV path for a prebook PDF.
     */
    public static function getPrebookFilename(Reservation $reservation): string
    {
        $entite = preg_replace("/[^\w]/u", '', html_entity_decode($reservation->tenant->display_name()));

        return $reservation->room->slug . '/' . $reservation->created_at->format('Y') . '/'
            . $reservation->id . '_' . $entite . '_Pré-réservation_' . $reservation->room->slug . '.pdf';
    }

    /**
     * Get the WebDAV path for an invoice PDF.
     */
    public static function getInvoiceFilename(Invoice $invoice): string
    {
        $reservation = $invoice->reservation;
        $entite = preg_replace("/[^\w]/u", '', html_entity_decode($reservation->tenant->display_name()));

        return $reservation->room->slug . '/' . $reservation->created_at->format('Y') . '/'
            . $invoice->number . '_' . $entite . '_Facture.pdf';
    }

    /**
     * Get the WebDAV path for a reminder PDF.
     */
    public static function getReminderFilename(Invoice $invoice): string
    {
        $reservation = $invoice->reservation;
        $entite = preg_replace("/[^\w]/u", '', html_entity_decode($reservation->tenant->display_name()));

        return $reservation->room->slug . '/' . $reservation->created_at->format('Y') . '/'
            . $invoice->number . '_' . $entite . '_Rappel.pdf';
    }

    /**
     * Generate a prebook PDF and return the PDF content as a string.
     */
    public function generatePrebookingPDF(Reservation $reservation): string
    {
        $html = view('pdf.prebook', [
            'reservation' => $reservation,
            'room' => $reservation->room,
            'owner' => $reservation->room->owner,
            'tenant' => $reservation->tenant,
        ])->render();

        return $this->renderToPdf($html);
    }

    /**
     * Generate an invoice PDF and return the PDF content as a string.
     */
    public function generateInvoicePDF(Reservation $reservation): string
    {
        $owner = $reservation->room->owner;
        $invoice = $reservation->invoice;

        $html = view('pdf.invoice', [
            'reservation' => $reservation,
            'room' => $reservation->room,
            'owner' => $owner,
            'tenant' => $reservation->tenant,
            'invoice' => $invoice,
            'paymentHtml' => $this->generatePaymentHtml($invoice),
        ])->render();

        return $this->renderToPdf($html);
    }

    /**
     * Generate a reminder PDF and return the PDF content as a string.
     */
    public function generateReminderPDF(Invoice $invoice): string
    {
        $reservation = $invoice->reservation;
        $owner = $reservation->room->owner;

        $html = view('pdf.reminder', [
            'reservation' => $reservation,
            'room' => $reservation->room,
            'owner' => $owner,
            'tenant' => $reservation->tenant,
            'invoice' => $invoice,
            'paymentHtml' => $this->generatePaymentHtml($invoice),
        ])->render();

        return $this->renderToPdf($html);
    }

    /**
     * Render HTML to PDF using DomPDF.
     */
    private function renderToPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Generate payment instructions HTML based on owner configuration.
     */
    private function generatePaymentHtml(Invoice $invoice): ?string
    {
        $owner = $invoice->owner;
        $instructions = $owner->payment_instructions;

        if (! $instructions || empty($instructions['type'])) {
            return null;
        }

        return match ($instructions['type']) {
            'international' => view('pdf.partials.payment-international', compact('instructions', 'invoice'))->render(),
            'sepa' => $this->generateSepaPaymentHtml($invoice, $instructions),
            'swiss' => $this->generateSwissQrBillHtml($invoice, $instructions),
            default => null,
        };
    }

    /**
     * Generate SEPA payment HTML with EPC QR code.
     */
    private function generateSepaPaymentHtml(Invoice $invoice, array $instructions): string
    {
        $qrDataUri = EPCQR::amount((float) $invoice->amount)
            ->receiver($instructions['iban'], $instructions['bic'] ?? '', $instructions['account_holder'])
            ->text('Facture ' . $invoice->number)
            ->build()
            ->getDataUri();

        return view('pdf.partials.payment-sepa', compact('instructions', 'invoice', 'qrDataUri'))->render();
    }

    /**
     * Generate Swiss QR Bill HTML.
     */
    private function generateSwissQrBillHtml(Invoice $invoice, array $instructions): string
    {
        $qrBill = QrBill::create();

        // Creditor (owner)
        $qrBill->setCreditor(
            StructuredAddress::createWithStreet(
                $instructions['account_holder'],
                $instructions['address']['street'],
                null,
                $instructions['address']['zip'],
                $instructions['address']['city'],
                $instructions['address']['country'] ?? 'CH'
            )
        );

        $qrBill->setCreditorInformation(
            CreditorInformation::create($instructions['iban'])
        );

        // Payment amount
        $qrBill->setPaymentAmountInformation(
            PaymentAmountInformation::create(
                $invoice->owner->getCurrency(),
                (float) $invoice->amount
            )
        );

        // Payment reference
        $besrId = $instructions['besr_id'] ?? null;
        if ($besrId) {
            // Generate structured QR reference using BESR-ID
            // Convert invoice number to numeric-only customer reference (max 20 digits)
            $customerReference = $this->getCustomerRef($invoice);
            $referenceNumber = QrPaymentReferenceGenerator::generate($besrId, $customerReference);

            $qrBill->setPaymentReference(
                PaymentReference::create(
                    PaymentReference::TYPE_QR,
                    $referenceNumber
                )
            );
        } else {
            // No BESR-ID (e.g., PostFinance) - use non-structured reference
            $qrBill->setPaymentReference(
                PaymentReference::create(
                    PaymentReference::TYPE_NON,
                    null
                )
            );

            // Add invoice number as additional information
            $qrBill->setAdditionalInformation(
                AdditionalInformation::create('Facture ' . $invoice->number)
            );
        }

        // Debtor (tenant)
        $tenant = $invoice->reservation->tenant;
        $qrBill->setUltimateDebtor(
            StructuredAddress::createWithStreet(
                $tenant->display_name(),
                $tenant->street,
                null,
                $tenant->zip,
                $tenant->city,
                'CH',
            )
        );

        // Map locale for Swiss QR Bill
        $locale = $this->mapLocaleForSwissQr($invoice->owner->getLocale());

        // Generate HTML output
        $output = new HtmlOutput($qrBill, $locale);

        $swissQrBillHtml = $output->getPaymentPart();

        return view('pdf.partials.payment-swiss', compact('instructions', 'swissQrBillHtml'))->render();
    }

    /**
     * Convert invoice number to numeric-only string for QR reference.
     * Invoice format: "2026-00001" -> "202600001"
     */
    private function getCustomerRef(Invoice $invoice): string
    {
        // Remove all non-numeric characters
        $ref = crc32($invoice->reservation->room->name) . $invoice->number;
        $numeric = preg_replace('/[^0-9]/', '', $ref);

        // Ensure it's not longer than 20 digits (QR reference limit for customer part)
        return substr($numeric, 0, 20);
    }

    /**
     * Map locale to Swiss QR Bill supported locales.
     */
    private function mapLocaleForSwissQr(string $locale): string
    {
        return match (substr($locale, 0, 2)) {
            'de' => 'de',
            'fr' => 'fr',
            'it' => 'it',
            default => 'en',
        };
    }
}
