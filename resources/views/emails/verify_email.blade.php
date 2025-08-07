<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SpeedySites - Verify Your Email Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f7f7;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            margin: 20px auto;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        h1 {
            text-align: center;
            color: #333333;
        }
        strong {
            color: #333333;
        }
        p {
            color: #555555;
        }
        .button {
            display: inline-block;
            background-color:#5063f0;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center !important;
        }
        .sitelogo {
            max-width: 160px;
        }
        .row{
            max-width: 600px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div style="text-align: center;">
                <img src="https://app.speedysites.in/img/logo/speedysites-beta-1.png" alt="SpeedySites Logo" style="max-width: 160px;">
            </div>
            <p><strong>{{ $greeting }}</strong></p>
            <p>Welcome to <strong>SpeedySites</strong>, and thank you for registering with us!</p>
            <p>To get started, please click the button below to verify your email address and activate your account.</p>
            @if(!empty($verificationUrl))
                <p>
                    <a href="{{ url($verificationUrl) }}" class="button">
                        {{ 'Verify Email Address' ?? 'Action' }}
                    </a>
                </p>
            @endif
            <p>This verification link is valid for <strong>60 minutes</strong>. If it expires, you can request a new one.</p>
            <p>If you have already verified your email address, you may safely ignore this message. Your account will not be activated until verification is complete.</p>
            <p>Thank you,<br>The SpeedySites Team</p>
            @if(!empty($verificationUrl))
                <hr>
                <p class="footer">
                    If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser: <a href="{{ url($verificationUrl) }}">{{ url($verificationUrl) }}</a>
                </p>
            @endif
        </div>
    </div>
</body>
</html>
