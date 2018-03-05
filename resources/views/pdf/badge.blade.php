<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>

        .badge {
            position: relative;
        }

        .badge-img {
            width: 100%;
            position: absolute;
            left: 0;
            top: 0;
        }

        .img-qr_code {
            width: 90px;
            height: 90px;
        }

        .badge-qrCode {
            position: absolute;
        }

    </style>
</head>
<body>
<table>
    <tr>
        <td>
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/ticket.jpg'}}"/>
                <div class="badge-qrCode img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode.png'}}">
                </div>
            </div>
        </td>
    </tr>
</table>

</body>
</html>