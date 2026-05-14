@extends('emails.app')

@section('content')

   <h3>Hello {{ $booking->name }},</h3>
    <p>
        Thank you for choosing "Expat Car Buyers" services!. <strong>We have received your valuation request.</strong> We will evaluate your <strong>{{ $booking->year }}, {{ $booking->make_name }} {{ $booking->model_name }}</strong> and get in touch with you shortly.<br>
        Your submitted details are as below:-<br>
    </p>
    <table style="width:100%;border:1px solid #ddd;border-spacing:0px">
        <tbody>
            <tr>
                <td colspan="2" style="background:#dddddd;padding:15px 15px 15px 15px;font-weight:600;font-size:18px">CAR DETAILS</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Reference Number:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->reference_number }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Year:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->year }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Make:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->make_name }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Model:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->model_name }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Variant:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->variant_name }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Mileage:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->mileage }}</td>
            </tr>
        </tbody>
    </table><br>

    <table style="width:100%;border:1px solid #ddd;border-spacing:0px">
        <tbody>
            <tr>
                <td colspan="2" style="background:#dddddd;padding:15px 15px 15px 15px;font-weight:600;font-size:18px">APPOINTMENT DETAILS</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Date:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->date ?? 'TBD' }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Time:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->time ?? 'TBD' }}</td>
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
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->name }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Email:</td>
                <td style="border:1px solid #ddd;padding:10px"><a href="mailto:{{ $booking->email }}" target="_blank">{{ $booking->email }}</a></td>
            </tr>
            <tr>
                <td style="border:1px solid #ddd;padding:10px">Phone no:</td>
                <td style="border:1px solid #ddd;padding:10px">{{ $booking->phone }}</td>
            </tr>
        </tbody>
    </table>
    <p></p>
@stop
