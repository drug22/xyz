<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif;font-size: 10px;color: #333;margin: 0;padding: 20px;line-height: 1.4;">

<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 30px;table-layout: fixed;">
    <tr>
        <td style="width: 50%;vertical-align: top;padding-right: 20px;text-align: left;">
            <div style="font-weight: bold;font-size: 12px;margin-bottom: 3px;">{{ $company['name'] }}</div>
            <div style="font-size: 9px;color: #666;margin-bottom: 10px;">
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
            <div style="font-size: 9px;line-height: 1.3;">
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
        <td style="width: 50%;vertical-align: top;padding-left: 20px;text-align: right;">
            <div style="font-weight: bold;font-size: 11px;margin-bottom: 3px;">{{ $invoice->customer_name }}</div>
            @if($invoice->is_business && isset($invoice->company_details['company_name']))
                <div style="font-weight: bold;font-size: 11px;">{{ $invoice->company_details['company_name'] }}</div>
            @endif
            <div style="font-size: 10px;line-height: 1.3;">
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

<!-- Linie separator -->
<div style="border-bottom: 1px solid #ccc;margin-bottom: 20px;"></div>

<!-- Detalii -->
<div style="margin: 20px 0;font-size: 10px;text-align:left;">
    @if($invoice->customer_vat_number)
        Customer ID: {{ $invoice->id }}<br>
        VAT Reg. No.: {{ $invoice->customer_vat_number }}<br>
    @endif
    Invoice no.: {{ $invoice->invoice_number }}<br>
    Invoice date: {{ $invoice->invoice_date->format('d/m/Y') }}
</div>

<div style="font-size:16px;font-weight:bold;margin: 20px 0 15px 0;">Invoice {{ $invoice->invoice_number }}</div>
<div style="font-size:12px;font-weight:bold;margin: 20px 0 10px 0;">Overview</div>

<!-- Tabel principal -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:15px 0;font-size:10px;">
    <tr style="background:#f8f8f8;">
        <th align="left" style="padding:8px 6px;border-bottom:1px solid #ddd;width:40%;">Service</th>
        <th align="center" style="padding:8px 6px;border-bottom:1px solid #ddd;width:15%;">Period</th>
        <th align="right" style="padding:8px 6px;border-bottom:1px solid #ddd;width:15%;">Total (excl. VAT)</th>
        <th align="right" style="padding:8px 6px;border-bottom:1px solid #ddd;width:15%;">Tax</th>
        <th align="right" style="padding:8px 6px;border-bottom:1px solid #ddd;width:15%;">Total</th>
    </tr>
    <tr>
        <td style="padding:6px;border-bottom:1px solid #eee;">{{ $invoice->package_details['name'] ?? 'Service' }}</td>
        <td align="center" style="padding:6px;border-bottom:1px solid #eee;">{{ $invoice->invoice_date->format('m/Y') }}</td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
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
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px;font-weight:bold;" colspan="2">Total</td>
        <td align="right" style="padding:6px;font-weight:bold;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td align="right" style="padding:6px;font-weight:bold;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->tax_amount, 2) }}
        </td>
        <td align="right" style="padding:6px;font-weight:bold;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
</table>

<!-- Tax Breakdown -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:20px 0;font-size:10px;">
    <tr style="background:#f8f8f8;">
        <th align="left" style="padding:6px;border-bottom:1px solid #ddd;width:20%;">Tax code</th>
        <th align="right" style="padding:6px;border-bottom:1px solid #ddd;width:20%;">Tax rate</th>
        <th align="right" style="padding:6px;border-bottom:1px solid #ddd;width:20%;">Total (excl. VAT)</th>
        <th align="right" style="padding:6px;border-bottom:1px solid #ddd;width:20%;">Tax</th>
        <th align="right" style="padding:6px;border-bottom:1px solid #ddd;width:20%;">Total</th>
    </tr>
    <tr>
        <td style="padding:6px;border-bottom:1px solid #eee;">A7</td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">{{ $invoice->tax_rate }}%</td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->tax_amount, 2) }}
        </td>
        <td align="right" style="padding:6px;border-bottom:1px solid #eee;">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
    <tr>
        <td style="font-weight:bold;padding:6px" colspan="2">Total</td>
        <td align="right" style="font-weight:bold;padding:6px">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->base_amount, 2) }}
        </td>
        <td align="right" style="font-weight:bold;padding:6px">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->tax_amount, 2) }}
        </td>
        <td align="right" style="font-weight:bold;padding:6px">
            @if($invoice->currency === 'EUR')
                €
            @else
                {{ $invoice->currency }}
            @endif
            {{ number_format($invoice->total_amount, 2) }}
        </td>
    </tr>
</table>

<!-- Amount Due -->
<div style="font-size:12px;font-weight:bold;margin: 20px 0;">
    Amount due:
    @if($invoice->currency === 'EUR')
        €
    @else
        {{ $invoice->currency }}
    @endif
    {{ number_format($invoice->total_amount, 2) }}
</div>

<!-- Payment Note -->
<div style="font-size:9px;margin:10px 0;line-height:1.4;">
    @if($invoice->isProforma())
        This is a proforma invoice. Payment is required before service delivery.
    @else
        The invoice amount will soon be debited from your payment method.
    @endif
</div>

<!-- Reverse Charge -->
@if($invoice->reverse_vat_applied || $invoice->tax_amount == 0)
    <div style="font-size:9px;font-weight:bold;margin:10px 0;">
        @if($invoice->customer_vat_number)
            Domestic turnover is not taxable. Your VAT registration number is: {{ $invoice->customer_vat_number }} - Reverse Charge!
        @else
            {{ $invoice->tax_note ?? 'Tax exemption applied.' }}
        @endif
    </div>
@endif

<!-- FOOTER TABLE -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:40px;border-top:1px solid #ccc;font-size:8px;">
    <tr>
        <td style="width:33%;vertical-align:top;padding:15px 10px 0 0;text-align:left;">
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
        <td style="width:33%;vertical-align:top;padding:15px 5px 0 5px;text-align:center;">
            <strong>Bank details:</strong><br>
            Deutsche Bank AG<br>
            IBAN: DE92 7607 0012 0750 0077 00<br>
            BIC: DEUTDEMM760
        </td>
        <td style="width:33%;vertical-align:top;padding:15px 0 0 10px;text-align:right;">
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
