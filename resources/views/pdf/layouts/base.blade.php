<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Document')</title>
    <style>
        @page {
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 5mm 0;
        }

        h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 3mm 0;
        }

        a {
            color: #0000FF;
            text-decoration: underline;
        }

        .header {
            margin-bottom: 10mm;
        }

        .header-row {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .separator {
            border-bottom: 0.5mm solid #000;
            margin: 2mm 0;
        }

        .tenant-address {
            text-align: right;
            margin-bottom: 5mm;
        }

        .invoice-info {
            margin-bottom: 7mm;
        }

        .invoice-info-row {
            display: table;
            width: 100%;
        }
        .invoice-info-row p {
            margin: 0;
        }

        .invoice-info-labels {
            display: table-cell;
            width: 45mm;
            vertical-align: top;
        }

        .invoice-info-values {
            display: table-cell;
            width: 50mm;
            vertical-align: top;
        }

        .invoice-info-tenant {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }

        table.events-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 8pt;
        }

        table.events-table th,
        table.events-table td {
            border: 1px solid #000;
            padding: 2mm;
            text-align: left;
        }

        table.events-table th {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        table.events-table .price-col {
            text-align: right;
            width: 25mm;
        }

        table.events-table .date-col {
            width: 30mm;
        }

        .totals-section {
            margin-top: 5mm;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        table.totals-table td {
            border: 1px solid #000;
            padding: 2mm;
        }

        table.totals-table .label-col {

        }

        table.totals-table .price-col {
            text-align: right;
            width: 25mm;
        }

        table.totals-table .total-row td {
            font-weight: bold;
        }

        .message {
            margin-top: 2mm;
            font-size: 10pt;
            line-height: 1.5;
        }
        .before-payment-placeholder {
            page-break-after: always;
        }
        .payment-section {
            page-break-before: always;
            margin-top: 10mm;
            page-break-inside: avoid;
            position: absolute;
        }
        .payment-section-sepa, .payment-section-international {
            bottom: 50px;
            left: 0;
            right: 0;
            margin: auto;
            width: fit-content;
        }
        .payment-section-swiss {
            bottom: -40px;
            left: -45px;
        }

        .payment-info {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 5mm;
            font-size: 9pt;
        }

        .payment-info h3 {
            font-size: 11pt;
            margin: 0 0 3mm 0;
        }

        .payment-info p {
            margin: 1mm 0;
        }

        .payment-info .label {
            font-weight: bold;
            display: inline-block;
            width: 40mm;
        }

        .qr-code {
            text-align: center;
            margin: 5mm 0;
        }

        .qr-code img {
            max-width: 50mm;
            max-height: 50mm;
        }

        /* Swiss QR Bill fixes for DomPDF */
        #qr-bill {
            page-break-inside: avoid;
        }

        #qr-bill table {
            border-collapse: collapse;
        }

        #qr-bill td {
            vertical-align: top;
        }

        #qr-bill-currency,
        #qr-bill-amount {
            float: none !important;
            display: inline-block;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
