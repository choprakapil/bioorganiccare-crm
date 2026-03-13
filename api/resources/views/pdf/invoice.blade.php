<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; font-size: 14px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .clinic-name { font-size: 24px; font-weight: bold; color: #4f46e5; margin: 0; }
        .invoice-title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .meta-section { margin-bottom: 40px; }
        .meta-col { width: 48%; display: inline-block; vertical-align: top; }
        .label { font-size: 10px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; border-bottom: 1px solid #eee; padding: 10px; font-size: 11px; text-transform: uppercase; color: #666; }
        td { padding: 12px 10px; border-bottom: 1px solid #f9f9f9; }
        .text-right { text-align: right; }
        .section-header { background: #f8fafc; padding: 5px 10px; font-weight: bold; font-size: 12px; color: #4f46e5; }
        .totals-section { float: right; width: 300px; }
        .total-row { padding: 10px 0; border-bottom: 1px solid #eee; }
        .total-row.final { border-bottom: none; font-size: 18px; font-weight: bold; color: #000; padding-top: 15px; }
        .total-row.discount { color: #f59e0b; }
        .total-row.paid { color: #059669; }
        .total-row.balance { color: #dc2626; border-top: 2px solid #eee; margin-top: 10px; }
        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; font-size: 10px; color: #999; text-align: center; }
        .quantity { font-size: 12px; color: #666; margin-left: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="clinic-name">{{ $invoice->doctor->clinic_name ?? 'Dental Clinic' }}</p>
        <div class="invoice-title">Medical Invoice #INV-{{ $invoice->id }}</div>
    </div>

    <div class="meta-section">
        <div class="meta-col">
            <div class="label">Patient Details</div>
            <strong>{{ $invoice->patient->name }}</strong><br>
            Phone: {{ $invoice->patient->phone }}<br>
            Date: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d M, Y') }}
        </div>
        <div class="meta-col" style="text-align: right;">
            <div class="label">Service Provider</div>
            <strong>Dr. {{ $invoice->doctor->name }}</strong><br>
            {{ $invoice->doctor->clinic_name }}<br>
            Status: {{ $invoice->status }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Service/Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $procedures = $invoice->items->filter(fn($i) => $i->type === 'Procedure' || (!$i->type && !$i->inventory_id));
                $medicines = $invoice->items->filter(fn($i) => $i->type === 'Medicine' || $i->inventory_id);
            @endphp

            @if($procedures->count() > 0)
                <tr><td colspan="2" class="section-header">Clinical Procedures</td></tr>
                @foreach($procedures as $item)
                <tr>
                    <td>{{ $item->name }} @if($item->teeth) <small>(Teeth: {{ $item->teeth }})</small> @endif</td>
                    <td class="text-right">₹{{ number_format($item->fee, 2) }}</td>
                </tr>
                @endforeach
            @endif

            @if($medicines->count() > 0)
                <tr><td colspan="2" class="section-header">Pharmacy & Medicines</td></tr>
                @foreach($medicines as $item)
                <tr>
                    <td>
                        {{ $item->name }} 
                        @if($item->quantity > 1) <span class="quantity">x {{ $item->quantity }} Units</span> @endif
                    </td>
                    <td class="text-right">₹{{ number_format($item->fee, 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row text-right">
            <span>Subtotal:</span>
            <strong>₹{{ number_format($invoice->subtotal, 2) }}</strong>
        </div>
        @if($invoice->discount_amount > 0)
        <div class="total-row discount text-right">
            <span>Discount Applied:</span>
            <strong>-₹{{ number_format($invoice->discount_amount, 2) }}</strong>
        </div>
        @endif
        <div class="total-row final text-right">
            <span>Net Payable:</span>
            <strong>₹{{ number_format($invoice->total_amount, 2) }}</strong>
        </div>
        <div class="total-row paid text-right">
            <span>Amount Collected:</span>
            <strong>₹{{ number_format($invoice->paid_amount, 2) }}</strong>
        </div>
        <div class="total-row balance text-right">
            <span>Balance Outstanding:</span>
            <strong>₹{{ number_format($invoice->balance_due, 2) }}</strong>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated document and does not require a physical signature. Powered by DentFlow CRM.
    </div>
</body>
</html>
