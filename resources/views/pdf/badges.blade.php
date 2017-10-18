<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
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
            top: 200px;
            left: 0;
            margin-left: 80px;
            margin-top: 50px;
            width: 100%;
        }

        .badge-label-2 {
            position: absolute;
            top: 200px;
            left: 460px;
            margin-right: 70px;
            margin-top: 50px;
            width: 100%;
        }

        .badge-label-3 {
            position: absolute;
            bottom: 380px;
            left: 0;
            margin-left: 80px;
            margin-right: 70px;
            margin-top: 50px;
            width: 100%;
        }

        .badge-label-4 {
            position: absolute;
            bottom: 380px;
            left: 460px;
            margin-right: 70px;
            margin-top: 50px;
            width: 100%;
        }

        .badge-qrCode-1 {
            position: absolute;
            margin-top: 500px;
            left: 290px;
            bottom: 210px;
            width: 80px;
            height: 80px;
        }

        .badge-qrCode-2 {
            position: absolute;
            margin-top: 500px;
            right: 65px;
            bottom: 215px;
            width: 80px;
            height: 80px;
        }

        .badge-qrCode-3 {
            position: absolute;
            left: 290px;
            bottom: 280px;
            width: 80px;
            height: 80px;
        }

        .badge-qrCode-4 {
            position: absolute;
            right: 65px;
            bottom: 285px;
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>
<table border="1">
    <tr>
        <td>
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                <h1 class="badge-label-1">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>
                <div class="badge-qrCode-1">
                    <img src="{{public_path().'/QRcode/qrcode.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>

                <h1 class="badge-label-2">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>
                <div class="badge-qrCode-2">
                    <img src="{{public_path().'/QRcode/qrcode.png'}}">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>

                <h1 class="badge-label-3">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>
                <div class="badge-qrCode-3">
                    <img src="{{public_path().'/QRcode/qrcode.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>

                <h1 class="badge-label-4">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>

                <div class="badge-qrCode-4">
                    <img src="{{public_path().'/QRcode/qrcode.png'}}">
                </div>
            </div>
        </td>
    </tr>

</table>
</body>
</html>