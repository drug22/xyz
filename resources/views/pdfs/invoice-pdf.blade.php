<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        /* HEADER TABLE - TD CA FLEX */
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td:first-child {
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            vertical-align: top;
            padding-right: 20px;
        }

        .header-table td:last-child {
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            vertical-align: top;
            padding-left: 20px;
        }

        /* FOOTER TABLE - TD CA FLEX */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            border-top: 1px solid #ccc;
            table-layout: fixed;
            font-size: 8px;
            line-height: 1.3;
        }

        .footer-table td:first-child {
            width: 33.33%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            vertical-align: top;
            padding: 15px 10px 0 0;
        }

        .footer-table td:nth-child(2) {
            width: 33.33%;
            display: flex;
            flex-direction: column;
            align-items: center;
            vertical-align: top;
            padding: 15px 5px 0 5px;
        }

        .footer-table td:last-child {
            width: 33.33%;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            vertical-align: top;
            padding: 15px 0 0 10px;
        }

        .company-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 9px;
            color: #666;
            margin-bottom: 10px;
        }

        .contact-info {
            font-size: 9px;
            line-height: 1.3;
        }

        .customer-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .customer-address {
            font-size: 10px;
            line-height: 1.3;
        }

        .invoice-details {
            margin: 20px 0;
            font-size: 10px;
        }

        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 15px 0;
        }

        .overview-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        .main-table th {
            background-color: #f8f8f8;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .main-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            border-top: 1px solid #333;
        }

        .tax-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }

        .tax-table th {
            background-color: #f8f8f8;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .tax-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }

        .amount-due {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0;
        }

        .payment-note {
            font-size: 9px;
            margin: 10px 0;
            line-height: 1.4;
        }

        .reverse-charge {
            font-size: 9px;
            font-weight: bold;
            margin: 10px 0;
        }

    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td>
            <!-- Company Info - FLEX START -->
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-address">
                @if($company['address'])
                    {{ $company['address'] }} •
                @endif
                @if($company['city'])
                    {{ $company['city'] }}
                @endif
                @if($company['postal_code'])
                    {{ $company['postal_code'] }} •
                @endif
                @if($company['country'])
                    {{ $company['country'] }}
                @endif
            </div>
            <div class="contact-info">
                @if($company['phone'])
                    Tel.: {{ $company['phone'] }}<br>
                @endif
                @if($company['email'])
                    {{ $company['email'] }}<br>
                @endif
                @if($company['website'])
                    {{ $company['website'] }}
                @endif
            </div>
        </td>

        <td style="text-align: right">
            <!-- Customer Info - FLEX END -->
            <div class="customer-name">{{ $invoice->customer_name }}</div>
            @if($invoice->is_business && isset($invoice->company_details['company_name']))
                <div class="customer-name">{{ $invoice->company_details['company_name'] }}</div>
            @endif
            <div class="customer-address">
                @if($invoice->customer_address && isset($invoice->customer_address['street']))
                    {{ $invoice->customer_address['street'] }}<br>
                @endif
                @if($invoice->customer_address)
                    @if(isset($invoice->customer_address['postal_code']))
                        {{ $invoice->customer_address['postal_code'] }}
                    @endif
                    @if(isset($invoice->customer_address['city']))
                        {{ $invoice->customer_address['city'] }}
                    @endif
                    <br>
                @endif
                @if($invoice->customerCountry)
                    {{ $invoice->customerCountry->name }}
                @endif
            </div>
        </td>
    </tr>
</table>

<!-- Separator -->
<div style="border-bottom: 1px solid #ccc; margin-bottom: 20px;"></div>

<!-- Invoice Details -->
<div class="invoice-details">
    @if($invoice->customer_vat_number)
        Customer ID: {{ $invoice->id }}<br>
        VAT Reg. No.: {{ $invoice->customer_vat_number }}<br>
    @endif
    Invoice no.: {{ $invoice->invoice_number }}<br>
    Invoice date: {{ $invoice->invoice_date->format('d/m/Y') }}
</div>

<!-- Invoice Title -->
<div class="invoice-title">Invoice {{ $invoice->invoice_number }}</div>

<!-- Overview -->
<div class="overview-title">Overview</div>

<!-- Main Table -->
<table class="main-table">
    <thead>
    <tr>
        <th style="width: 40%;">Service</th>
        <th style="width: 15%;" class="text-center">Period</th>
        <th style="width: 15%;" class="text-right">Total (excl. VAT)</th>
        <th style="width: 15%;" class="text-right">Tax</th>
        <th style="width: 15%;" class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ $invoice->package_details['name'] ?? 'Service' }}</td>
        <td class="text-center">{{ $invoice->invoice_date->format('m/Y') }}</td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->tax_amount, 2) }}
            @if($invoice->tax_amount == 0)
                A7
            @endif
        </td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
    <tr class="total-row">
        <td><strong>Total</strong></td>
        <td></td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->base_amount, 2) }}
            </strong>
        </td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->tax_amount, 2) }}
            </strong>
        </td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->total_amount, 2) }}
            </strong>
        </td>
    </tr>
    </tbody>
</table>

<!-- Tax Breakdown -->
<table class="tax-table">
    <thead>
    <tr>
        <th style="width: 20%;">Tax code</th>
        <th style="width: 20%;" class="text-right">Tax rate</th>
        <th style="width: 20%;" class="text-right">Total (excl. VAT)</th>
        <th style="width: 20%;" class="text-right">Tax</th>
        <th style="width: 20%;" class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>A7</td>
        <td class="text-right">{{ $invoice->tax_rate }}%</td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->tax_amount, 2) }}
        </td>
        <td class="text-right">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
    <tr class="total-row">
        <td><strong>Total</strong></td>
        <td></td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->base_amount, 2) }}
            </strong>
        </td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->tax_amount, 2) }}
            </strong>
        </td>
        <td class="text-right">
            <strong>
                @if($invoice->currency === 'EUR')
                    €
                @else
                    {{ $invoice->currency }}
                @endif
                {{ number_format($invoice->total_amount, 2) }}
            </strong>
        </td>
    </tr>
    </tbody>
</table>

<!-- Amount Due -->
<div class="amount-due">
    Amount due:
    @if($invoice->currency === 'EUR')
        €
    @else
        {{ $invoice->currency }}
    @endif
    {{ number_format($invoice->total_amount, 2) }}
</div>

<!-- Payment Note -->
<div class="payment-note">
    @if($invoice->isProforma())
        This is a proforma invoice. Payment is required before service delivery.
    @else
        The invoice amount will soon be debited from your payment method.
    @endif
</div>

<!-- Reverse Charge -->
@if($invoice->reverse_vat_applied || $invoice->tax_amount == 0)
    <div class="reverse-charge">
        @if($invoice->customer_vat_number)
            Domestic turnover is not taxable. Your VAT registration number is: {{ $invoice->customer_vat_number }} - Reverse Charge!
        @else
            {{ $invoice->tax_note ?? 'Tax exemption applied.' }}
        @endif
    </div>
@endif

<!-- FOOTER TABLE - TD FLEX DIRECT -->
<table class="footer-table">
    <tr>
        <td>
            <!-- FLEX START -->
            <strong>{{ $company['name'] }}</strong><br>
            @if($company['address'])
                {{ $company['address'] }}<br>
            @endif
            @if($company['city'])
                {{ $company['postal_code'] }} {{ $company['city'] }} | {{ $company['country'] }}<br>
            @endif
            @if($company['phone'])
                Tel.: {{ $company['phone'] }}<br>
            @endif
            @if($company['email'])
                {{ $company['email'] }} | {{ $company['website'] }}
            @endif
        </td>

        <td style="text-align: center">
            <!-- FLEX CENTER -->
            <strong>Bank details:</strong><br>
            Deutsche Bank AG<br>
            IBAN: DE92 7607 0012 0750 0077 00<br>
            BIC: DEUTDEMM760
        </td>

        <td style="text-align: right">
            <!-- FLEX END -->
            <strong>CEO:</strong> Management Team<br>
            @if($company['registration_number'])
                Registration Office: {{ $company['registration_number'] }}<br>
            @endif
            @if($company['vat_number'])
                VAT Reg. No.: {{ $company['vat_number'] }}
            @endif
        </td>
    </tr>
</table>
</body>
</html>
