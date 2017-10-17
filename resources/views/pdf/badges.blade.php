<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        .badge {
            width: 100%; /* for IE 6 */
            position: relative;
        }

        .badge-img {
            position: absolute;
            left: 0;
            top: 0;
        }

        .badge-label {
            position: absolute;
            top: 200px;
            left: 0;
            width: 100%;
        }

        .badge-qrCode {
            position: absolute;
            width: 150px;
            margin-top: 670px;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
<table border="1">
    <tr>
        <td width="50%" height="50%">

            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                <h1 class="badge-label">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>
                <img class="badge-qrCode" src="{{public_path().'/QRcode/qrcode.png'}}">
            </div>
        </td>
        <td width="50%" height="50%">
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                <h1 class="badge-label">{{$users[1]->first_name.' '.$users[1]->last_name}}</h1>
                <img class="badge-qrCode" src="{{public_path().'/QRcode/qrcode.png'}}">
            </div>
        </td>
    </tr>

    <tr>
        <td width="50%" height="50%">

            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                <h1 class="badge-label">{{$users[0]->first_name.' '.$users[0]->last_name}}</h1>
                <img class="badge-qrCode" src="{{public_path().'/QRcode/qrcode.png'}}">
            </div>
        </td>
        <td width="50%" height="50%">
            <div class="badge">
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                <h1 class="badge-label">{{$users[1]->first_name.' '.$users[1]->last_name}}</h1>
                <img class="badge-qrCode" src="{{public_path().'/QRcode/qrcode.png'}}">
            </div>
        </td>
    </tr>
</table>
</body>
</html>