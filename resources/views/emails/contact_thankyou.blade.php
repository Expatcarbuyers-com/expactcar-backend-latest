@extends('emails.app')

@section('content')

   <h3>Hello {{ $contact->name }},</h3>
    <p>
        Thank you for contacting "Expat Car Buyers"!. <strong>We have received your message.</strong> Our team will review your inquiry and get back to you shortly.<br>
        Your submitted details are as below:-<br>
    </p>
    <table style="width:100%;border:1px solid #ddd;border-spacing:0px">
        <tbody>
            <tr>
                <td colspan="2" style="background:#dddddd;padding:15px 15px 15px 15px;font-weight:600;font-size:18px">INQUIRY DETAILS</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Subject:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $contact->subject ?? 'General Contact' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Message:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $contact->message }}</td>
            </tr>
        </tbody>
    </table><br>

    <table style="width:100%;border:1px solid #ddd;border-spacing:0px">
        <tbody>
            <tr>
                <td colspan="2" style="background:#dddddd;padding:15px 15px 15px 15px;font-weight:600;font-size:18px">YOUR DETAILS</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Name:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $contact->name }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Email:</td>
                <td style="border:1px solid #ddd;padding:10px"><a href="mailto:{{ $contact->email }}" target="_blank">{{ $contact->email }}</a></td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Phone no:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $contact->phone ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
    <p></p>
@stop
