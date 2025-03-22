<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="{{ url('productLiveAlertEmail/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- <link rel = "icon" href = "images/logoicon.png" type = "image/x-icon"> -->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ url('productLiveAlertEmail/css/animate.min.css') }}" />
    <!-- <link rel="stylesheet" type="text/css" href="{{ url('productLiveAlertEmail/css/style.css') }}"> -->
    <title>Pre Register Alert</title>
    <style>
        :root {
            --c4e-primaryColor-1: #001935;
            --c4e-primaryColor-2: #26ae61;
            --c4e-primaryColor-5: #fff;
            --c4e-primaryColor-6: #000;
            --c4e-font-family: 'Poppins', sans-serif;
        }

        * {
            padding: 0;
            margin: 0;
            text-decoration: none;
            box-shadow: border-box;
        }

        header.header {
            background-color: #fff;
            padding: 50px 0px;
        }

        .full-wrap {
            /* display: flex; */
            margin: 0px auto;
            border: 1px solid black;
            padding: 20px 39px 20px;
            max-width: 650px;
        }

        .full-wrap img {
            width: 22%;
            display: flex;
            justify-content: flex-start;
            margin: 0px auto;
            align-items: center;
            padding: 0px 0px 10px;
        }

        .main-wrapper {
            padding: 30px 0px 35px 0px;
            background-color: #1d2b64;
            transition: 0.3s;
        }

        .text-wrapper {
            text-align: center;
            padding: 10px 0px 10px;
            color: #fff;
        }

        .text-wrapper h4 {
            font-size: 23px;
            line-height: 35px;
        }

        .text-wrapper h2 {
            font-size: 40px;
            line-height: 55px;
            padding-bottom: 35px;
        }

        a.start-button {
            padding: 15px 30px;
            background: #fff;
            border-radius: 7px;
            text-decoration: none;
            color: #1d2b64;
            font-size: 18px;
            font-weight: 600;
            transition: 0.3s;
        }

        .image-wrapper {
            display: flex;
            justify-content: center;
            padding: 0px 0px 0px;
            width: 100%;
            margin: 0px auto;
            align-items: center;
            text-align: center;
        }

        a.start-button:hover {
            background-color: #df3650;
            text-align: center;
            color: #ffffff;
            border-radius: 7px;
            line-height: 20px;
            transition: 0.3s;
        }

        .image-wrapper img {
            width: 70%;
        }

        .text-area {
            padding: 30px 0px;
        }

        .text-area p {
            text-align: center;
            display: flex;
            justify-content: center;
            margin: 0px auto;
            font-size: 22px;
            word-break: break-all;
            font-family: ui-sans-serif;
        }

        .button-start {
            display: flex;
            padding: 0px 0px 30px;
            justify-content: center;
            gap: 20px;
            align-items: center;
        }

        a.button1 {
            background-color: #1d2b64;
            text-align: center;
            padding: 15px 20px;
            color: #ffffff;
            border-radius: 7px;
            display: block;
            mso-padding-alt: 0;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            white-space: nowrap;
            line-height: 20px;
            transition: 0.3s;
        }

        a.button2 {
            color: #df3650;
            white-space: nowrap;
            font-weight: normal;
            font-family: Helvetica, Arial, sans-serif;
            /* width: 100%; */
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        a.button2:hover {
            background-color: #df3650;
            text-align: center;
            padding: 15px 20px;
            color: #ffffff;
            border-radius: 7px;
            mso-padding-alt: 0;
            text-decoration: none;
            white-space: nowrap;
            line-height: 20px;
            transition: 0.3s;
        }

        a.button1:hover {
            color: #1d2b64;
            border: 2px solid #1d2b64;
            background-color: transparent;
        }

        .footer-section {
            padding: 20px 0px 0px;

        }

        .footer-section h3 {
            text-align: center;
            font-size: 25px;
            font-family: ui-sans-serif;
        }

        .soical-links {
            display: flex;
            margin: 0px auto;
            padding: 10px 0px 0px;
            justify-content: center;
        }

        .soical-links a img {
            width: 100%;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="full-wrap">
                <img src="{{ url('img/speedy-logo-3.png') }}">


                <div class="main-wrapper">
                    <div class="text-wrapper">
                        <h4>Welcome to Speedy Sites</h4>
                        <h2>Let's get ready for service</h2>
                        <a href="https://speedysites.in/" class="start-button">let's Start Now</a>
                    </div>
                    <div class="image-wrapper">
                        <img src="{{ url('img/datailedimg1.png') }}">
                    </div>
                </div>

                <div class="text-area">
                    <p>This handy tool helps you create dummy text for all your layout needs.</p>
                    <p>We are gradually adding new functionality and we welcome your suggestions and feedback.</p>
                </div>
                <div class="button-start" style="display: flex;justify-content: center;">
                    <a href="https://speedysites.in/" class="button1">let's Start Now</a>

                    <a href="https://speedysites.in/learn-more/" class="button2">Learn More <i class="fa fa-long-arrow-right"></i></a>
                </div>

                <hr>

                <div class="footer-section">
                    <h3>
                        Stay up to date with our latest news & features
                    </h3>

                    <div class="soical-links" style="display: flex;justify-content: center;gap:20px;">
                        <a href=""><img src="{{ url('img/icons8-facebook-circled.gif') }}"></a>
                        <a href="https://www.linkedin.com/company/code-4-each/"><img src="{{ url('img/icons8-linkedin-logo.gif') }}"></a>
                        <a href="https://wa.me/7973630617"><img src="{{ url('img/icons8-whatsapp.gif') }}"></a>
                        <a href="mailto:info@code4each.com"><img src="{{ url('img/icons8-gmail-logo.gif') }}"></a>
                    </div>
                </div>

            </div>
        </div>

    </header>



    <script src="{{ url('productLiveAlertEmail/js/code.jquery.com_jquery-3.6.4.min.js') }}"></script>
    <script src="{{ url('productLiveAlertEmail/js/cdn.jsdelivr.net_npm_bootstrap@5.0.2_dist_js_bootstrap.bundle.min.js') }}"></script>
</body>

</html>
