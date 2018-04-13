<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        h1 {
            font-size: 20px;
            color: #182650;
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
            top: 240px;
            left: 110px;
            width: 100%;
        }

        .badge-label-2 {
            position: absolute;
            top: 240px;
            left: 560px;
            width: 100%;
        }

        .badge-qrCode-1 {
            position: absolute;
            top: 330px;
            left: 5px;
        }

        .badge-qrCode-2 {
            position: absolute;
            left: 450px;
            top: 330px;
        }

        .badge-label-4 {
            position: absolute;
            bottom: 170px;
            left: 560px;
            width: 100%;
        }

        .badge-label-3 {
            position: absolute;
            bottom: 170px;
            left: 110px;
            width: 100%;
        }

        .badge-qrCode-3 {
            position: absolute;
            bottom: 50px;
            left: 5px;
        }

        .badge-qrCode-4 {
            position: absolute;
            left: 450px;
            bottom: 50px;
        }
    </style>
</head>
<body>
<img src="{{public_path().'/badge/AMLPS_2018.png'}}"/>
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
                    <img src="{{public_path().'/qr_code_1.png'}}">
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
                    <img src="{{public_path().'/qr_code_2.png'}}">
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
                    <img src="{{public_path().'/qr_code_3.png'}}">
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
                    <img src="{{public_path().'/qr_code_4.png'}}">
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>