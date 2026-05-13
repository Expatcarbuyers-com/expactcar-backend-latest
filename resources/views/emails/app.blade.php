<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <meta http-equiv="content-type" content="text/html charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>wecashanycar</title>
        <link href="{{ asset('css/open-sans.css') }}" rel="stylesheet">
        <style type="text/css">
            html,body{
                width:100%;
                height:100%;
                margin:0;
            }
            .padding-cong{
                padding:20px 10px;
            }
            .padd-x-30{
                padding:0 30px;
            }
            .mail-container{
                width:800px;
                max-width:800px;
                margin:15px auto;
            }
            body b{
                font-size:16px;
            }
            body{
                font-size:14px;
            }
            .padding-cong{
                padding:10px 15px;
            }
            .padd-x-30{
                padding:0 15px;
            }
            .mail-container{
                width:calc(100% - 30px);
                padding:15px;
                margin:15px auto;
            }
        </style>
    </head>
    <body bgcolor="#f6f6f6;" style="font-family: 'Open Sans', sans-serif;    background-color: #f6f6f6;">
        <table cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" style="max-width:800px;background:#fff;" class="mail-container">
            <tr>
                <td>
                    <table style="width:100%;text-align:center;" class="padding-cong">
                        <tr>
                            <td><img src="{{ asset('front/images/logo-email.jpg') }}" loading="lazy" width="250" alt="" onerror="this.onerror=null; this.src='{{ asset('front/images/logo.png') }}'">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Toll free :<span style="color:red;">056 177 4555</span></b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width:100%;background:#eeeeee;">
                        <tr>
                            <td style="font-size:20px;text-align:center;" class="padding-cong">
                                <b>Congratulation ! Your Car Selling Appointment Is Confirmed!</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                    <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
                        <!-- Body content -->
                        <tr>
                            <td class="content-cell">
                                @yield('content')
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:30px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="padd-x-30">
                    <table style="width:100%;border:1px solid #ccc;border-spacing:0px;">
                        <tr>
                            <td colspan="2" style="background:#dddddd;padding:15px 15px 15px 15px;font-weight:600;font-size:18px;">KINDLY BRING THE FOLLOWING THINGS TO YOUR APPOINTMENT</td>
                        </tr>
                        <tr>
                            <td>
                                <ul style="padding-left:35px;">
                                    <li>Vehicle Registration Card i.e Mulkiya Driving Licence</li>
                                    <li>Vehicle Service History(if any)</li>
                                    <li>For bank Loan ,liability or release letter (if any)</li>
                                    <li>Emirates ID or Passport</li>
                                    <li>Original Key and Spare(If available)</li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:30px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="padd-x-30">
                    <table>
                        <tr>
                            <td>
                                <b>if you have any enquiries in the meantime please don not hesitate to contact us on email <span style="color:red">contact@expatcarbuyers.com</span> or call us on <span>
                                056 177 4555</span>!</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:20px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="padd-x-30">
                    <table>
                        <tr>
                            <td>
                                <b>We look forward to serving you !</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:20px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="padd-x-30">
                    <table>
                        <tr>
                            <td>
                                <b>ECB Team</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:20px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td style="height:20px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width:100%;">
                        <tr>
                            <td style="background:#dddddd;padding:15px 15px 15px 15px;text-align:center;">©{{ date('Y') }}  expatcarbuyers. All Right Reserved</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
