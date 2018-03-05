<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        h1 {
            font-size: 19px;
            color: white;
        }

        .img-qr_code {
            width: 90px;
            height: 90px;
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
            top: 165px;
            left: 0;
            margin-left: 70px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-label-2 {
            position: absolute;
            top: 165px;
            left: 495px;
            margin-right: 70px;
            margin-top: 60px;
            width: 100%;
        }

        .badge-label-3 {
            position: absolute;
            bottom: 225px;
            left: 0;
            margin-left: 70px;
            margin-right: 70px;
            margin-top: 48px;
            width: 100%;
        }

        .badge-label-4 {
            position: absolute;
            bottom: 225px;
            left: 495px;
            margin-right: 10px;
            margin-top: 48px;
            width: 100%;
        }

        .badge-qrCode-1 {
            position: absolute;
            margin-top: 435px;
            left: 265px;
            bottom: 255px;
        }

        .badge-qrCode-2 {
            position: absolute;
            margin-top: 435px;
            right: 10px;
            bottom: 255px;
        }

        .badge-qrCode-3 {
            position: absolute;
            left: 265px;
            bottom: 60px;
        }

        .badge-qrCode-4 {
            position: absolute;
            right: 10px;
            bottom: 60px;
        }
    </style>
</head>
<body>
<img src="{{public_path().'/badge/Badge_preinscription_04.png'}}"/>
<table>
    <tr>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}
                <h1 class="badge-label-1">{{strtoupper(substr($users[0]->first_name,0,1)).substr($users[0]->first_name,1)
                .' '.strtoupper($users[0]->last_name)}}</h1>
                <div class="badge-qrCode-1 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_1.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                <h1 class="badge-label-2">{{strtoupper(substr($users[1]->first_name,0,1)).strtolower(substr($users[1]->first_name,1))
                .' '.strtoupper($users[1]->last_name)}}</h1>
                <div class="badge-qrCode-2 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_2.png'}}">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                <h1 class="badge-label-3">{{strtoupper(substr($users[2]->first_name,0,1)).substr($users[2]->first_name,1)
                .' '.strtoupper($users[2]->last_name)}}</h1>
                <div class="badge-qrCode-3 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_3.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                <h1 class="badge-label-4">{{strtoupper(substr($users[3]->first_name,0,1)).substr($users[3]->first_name,1)
                .' '.strtoupper($users[3]->last_name)}}</h1>
                <div class="badge-qrCode-4 img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_4.png'}}">
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>