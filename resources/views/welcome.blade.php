<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="https://www.balajiwafers.com/wp-content/themes/custom/img/fav_logo.png">
    <title>Balaji Inventory</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background-image: linear-gradient(-45deg, #b2c6e7, #0081bb);
            display: flex;
            justify-content: center;
            height: 100vh;
        }
        .mobile-wrapper {
            width: 360px;
            height: 740px;
            background-color: #ececec;
            background-image: linear-gradient(-45deg, #ececec, #ffffff);
            border: 12px solid #333;
            border-radius: 36px;
            position: relative;
            overflow: hidden;
            margin: 20px 0;
        }
        .mobile-wrapper:before {
            content: "";
            width: 175px;
            height: 25px;
            background: #333;
            border-bottom-right-radius: 20px;
            border-bottom-left-radius: 20px;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translatex(-50%);
            z-index: 1000;
        }
        .d-flex {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .flex-column {
            flex-direction: column;
        }
        .loader-wrapper {
            height: 100%;
            width: auto;
            padding: 0 25px;
            font-family: "Roboto", sans-serif;
            line-height: 1.4;
            margin: 0 auto;
        }

        .loader-wrapper.dark {
            background-color: #1b1b1b;
            color: #ececec;
        }
        .version {
            text-align: center;
        }
        .version p {
            margin-top: 0 !important;
        }
        .colaborate {
            padding: 16px 0 30px 0;
            width: 100%;
            justify-content: space-between;
        }
        .colaborate img {
            height: 32px;
        }
    </style>
</head>

<body>
    <div class="mobile-wrapper">
        <div class="loader-wrapper d-flex flex-column">
            <div class="d-flex flex-column" style="flex-grow: 1; ">
                <div class="logo">
                    <img src="https://www.balajiwafers.com/wp-content/themes/custom/img/BalajiWafers.svg" width="200"
                        alt="Logo" />
                    <h1 style="text-align: center;color: #403092"><b>Inventory</b></h1>
                </div>
            </div>
            <small style="margin-bottom: 0; align-self: center; font-size: 12px;"><b>Design & Develop By  <a
                    href="">Vrutti IT Solutions</a></b></small>
            <div class="d-flex" style=" width: 100%; ">
                <div class="colaborate d-flex">
                </div>
            </div>
        </div>
    </div>
</body>

</html>
