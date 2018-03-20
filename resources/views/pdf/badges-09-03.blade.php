<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        h1 {
            font-size: 28px;
            color: #23235D;
            font-weight: bold;
        }

        .img-qr_code {
            width: 90px;
            height: 90px;
            position: absolute;
        }

        .badge {
            position: relative;
        }

        .badge-img {
            position: absolute;
            left: 0;
            top: 0;
        }

        .label {
            position: absolute;
            width: 100%;
        }

        .qrcode-pos-x {
            margin-left: 170px;
            left: 30px;
        }

        .label-pos-x {
            margin-left: 250px;
            left: 50px;
        }

        .label-1-pos-y {
            top: 130px;
            margin-top: 80px;
        }

        .qrcode-1-pos-y {
            top: 250px;
        }

        .label-2-pos-y {
            top: 510px;
            margin-top: 50px;
        }

        .qrcode-2-pos-y {
            top: 590px;
        }

        .label-3-pos-y {
            bottom: 180px;
        }

        .qrcode-3-pos-y {
            bottom: 110px;
        }
    </style>
</head>
<body>
<img src="{{public_path().'/badge/badge-09-03.png'}}"/>
<table>
    <tr>
        <td>
            <div class="badge">

                {{--
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}
                <h1 class="label label-pos-x label-1-pos-y">
                    {{strtoupper(substr($users[0]->first_name,0,1)).substr($users[0]->first_name,1)
                    .' '.strtoupper($users[0]->last_name)}}</h1>
                <div class="qrcode-pos-x qrcode-1-pos-y img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_1.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                <h1 class="label label-pos-x label-2-pos-y">
                    {{strtoupper(substr($users[1]->first_name,0,1)).strtolower(substr($users[1]->first_name,1))
                    .' '.strtoupper($users[1]->last_name)}}</h1>
                <div class="qrcode-pos-x qrcode-2-pos-y img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_2.png'}}">
                </div>
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                <h1 class="label label-pos-x label-3-pos-y">
                    {{strtoupper(substr($users[2]->first_name,0,1)).substr($users[2]->first_name,1)
                    .' '.strtoupper($users[2]->last_name)}}</h1>
                <div class="qrcode-pos-x qrcode-3-pos-y  img-qr_code">
                    <img src="{{public_path().'/QRcode/qrcode_3.png'}}">
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>