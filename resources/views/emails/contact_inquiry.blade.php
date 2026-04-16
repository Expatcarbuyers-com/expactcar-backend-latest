<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #1e293b; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .label { font-weight: bold; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
        .value { color: #0f172a; font-size: 15px; }
        .message-box { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px; }
        .footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">New Website Inquiry</h2>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">Customer Name</span>
                <span class="value">{{ $contact->name }}</span>
            </div>
            
            <div class="field">
                <span class="label">Email Address</span>
                <span class="value">{{ $contact->email }}</span>
            </div>

            @if($contact->phone)
            <div class="field">
                <span class="label">Phone Number</span>
                <span class="value">{{ $contact->phone }}</span>
            </div>
            @endif

            <div class="field" style="border-bottom: none;">
                <span class="label">Subject</span>
                <span class="value">{{ $contact->subject ?? 'General Inquiry' }}</span>
            </div>
            
            <div class="label">Message</div>
            <div class="message-box">
                {!! nl2br(e($contact->message)) !!}
            </div>
            
            <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                <strong>Submitted on:</strong> {{ $contact->created_at->format('M d, Y H:i:s') }}
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} ExpatCarBuyers. This is an automated notification.
        </div>
    </div>
</body>
</html>
