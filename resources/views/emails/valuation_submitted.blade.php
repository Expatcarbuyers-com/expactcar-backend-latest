<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #f24026; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; color: #666; width: 120px; display: inline-block; }
        .value { color: #000; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 20px; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Valuation Request</h2>
        </div>
        <div class="content">
            <p>A new car valuation request has been submitted through the website.</p>
            
            <div class="field">
                <span class="label">Reference:</span>
                <span class="badge">{{ $booking->reference_number }}</span>
            </div>
            
            <hr>
            
            <h3>Car Details</h3>
            <div class="field">
                <span class="label">Vehicle:</span>
                <span class="value">{{ $booking->year }} {{ $booking->make_name }} {{ $booking->model_name }}</span>
            </div>
            <div class="field">
                <span class="label">Variant:</span>
                <span class="value">{{ $booking->variant_name }}</span>
            </div>
            <div class="field">
                <span class="label">Mileage:</span>
                <span class="value">{{ number_format($booking->mileage) }} KM</span>
            </div>
            
            <hr>
            
            <h3>Customer Contact</h3>
            <div class="field">
                <span class="label">Name:</span>
                <span class="value">{{ $booking->name }}</span>
            </div>
            <div class="field">
                <span class="label">Phone:</span>
                <span class="value">{{ $booking->phone }}</span>
            </div>
            <div class="field">
                <span class="label">Email:</span>
                <span class="value">{{ $booking->email }}</span>
            </div>
            
            <hr>
            
            <p style="font-size: 13px; color: #666;">
                <strong>IP Address:</strong> {{ $booking->ip_address }}<br>
                <strong>Date:</strong> {{ $booking->created_at->format('M d, Y H:i:s') }}
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} ExpatCarBuyers. All rights reserved.
        </div>
    </div>
</body>
</html>
