<div class="payment-section payment-section-sepa">
    <div class="payment-info">
        <h3>Instructions de paiement SEPA</h3>

        <div style="display: table; width: 100%;">
            <div style="display: table-cell; vertical-align: top; width: 60%;">
                <p><span class="label">Bénéficiaire:</span> {{ $instructions['account_holder'] }}</p>
                <p><span class="label">IBAN:</span> {{ $instructions['iban'] }}</p>
                @if(!empty($instructions['bic']))
                    <p><span class="label">BIC:</span> {{ $instructions['bic'] }}</p>
                @endif

                <div style="margin-top: 3mm; border-top: 1px solid #ddd; padding-top: 3mm;">
                    <p><span class="label">Montant:</span> <strong>{{ currency($invoice->amount, $invoice->owner) }}</strong></p>
                    <p><span class="label">Référence:</span> Facture {{ $invoice->number }}</p>
                </div>

                @if(!empty($instructions['vat_number']))
                    <p style="margin-top: 3mm;"><span class="label">N° TVA:</span> {{ $instructions['vat_number'] }}</p>
                @endif
            </div>
            <div style="display: table-cell; vertical-align: top; width: 40%; text-align: center;">
                <div class="qr-code">
                    <img src="{{ $qrDataUri }}" alt="QR Code SEPA">
                    <p style="font-size: 8pt; margin-top: 2mm;">Scanner pour payer</p>
                </div>
            </div>
        </div>
    </div>
</div>
