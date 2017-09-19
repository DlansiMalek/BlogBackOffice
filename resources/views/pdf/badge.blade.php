<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        .images {
            position: relative;
            display: inline-block;
        }

        .images h1 {
            position: absolute;
            width: 100%;
            text-align: center;
            bottom: 450px;
            left: 0;
        }

        .images .qrCode {
            position: absolute;
            width: 150px;
            bottom: 0;
            left: 0;

        }
    </style>
</head>
<body>
<div class="images">
    <img style="height: 100%" src="{{public_path().'/badge/badge.jpg'}}"/>
    <h1>{{$name}}</h1>
    <img class="qrCode" src="{{public_path().'/QRcode/qrcode.png'}}">
</div>
</body>
</html>