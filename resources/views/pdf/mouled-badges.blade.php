<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        h1 {
            font-size: 19px;
            color: black;
        }

        .img-qr_code {
            width: 60px;
            height: 60px;
        }

        .badge {
            position: relative;
        }

        .badge-img {
            position: absolute;
            left: 0;
            top: 0;
        }

        .badge-label-1 {
            position: absolute;
            top: 195px;
            left: 660px;
            margin-right: 10px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-label-2 {
            position: absolute;
            top: 457px;
            left: 660px;
            margin-right: 10px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-label-3 {
            position: absolute;
            top: 718px;
            left: 660px;
            margin-right: 10px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-label-4 {
            position: absolute;
            top: 979px;
            left: 660px;
            margin-right: 10px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-qrCode-1 {
            position: absolute;
            top: 215px;
            left: 62px;
            bottom: 255px;
        }

        .badge-qrCode-2 {
            position: absolute;
            top: 479px;
            left: 62px;
            bottom: 255px;
        }

        .badge-qrCode-3 {
            position: absolute;
            top: 741px;
            left: 62px;
            bottom: 255px;
        }

        .badge-qrCode-4 {
            position: absolute;
            top: 1003px;
            left: 62px;
            bottom: 255px;
        }
    </style>
</head>
<body>
<img src="{{public_path().'/badge/mouled_invitation_ticket.png'}}"/>
<table>
    <tr>
        <td>
            <div class="badge">
                <h1 class="badge-label-1">N° {{strtoupper($users[0]->last_name)}}</h1>
                <div class="badge-qrCode-1 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_1.png'}}">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                <h1 class="badge-label-2">N° {{strtoupper($users[1]->last_name)}}</h1>
                <div class="badge-qrCode-2 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_2.png'}}">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                <h1 class="badge-label-3">N° {{strtoupper($users[2]->last_name)}}</h1>
                <div class="badge-qrCode-3 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_3.png'}}">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                <h1 class="badge-label-4">N° {{strtoupper($users[3]->last_name)}}</h1>
                <div class="badge-qrCode-4 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_4.png'}}">
                </div>
            </div>
        </td>
    </tr>
</table></body></html>