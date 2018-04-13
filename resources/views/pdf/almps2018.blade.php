<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        .small-text {
            font-size: 17px;
            margin-left: 10px;
        }

        h1 {
            font-size: 19px;
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
                @if(isset($users[0]))
                    @if(strlen($users[0]->first_name)<8 && strlen($users[0]->last_name)<8)
                        <h1 class="badge-label-1">Dr {{strtoupper(substr($users[0]->first_name,0,1)).substr($users[0]->first_name,1)
                .' '.strtoupper($users[0]->last_name)}}</h1>
                    @else
                        <div class="badge-label-1">
                            <h1 style="margin-left: 5px">
                                Dr {{strtoupper(substr($users[0]->first_name,0,1)).substr($users[0]->first_name,1)}}</h1>
                            <h1 class="small-text">{{strtoupper($users[0]->last_name)}}</h1>
                        </div>
                    @endif

                    <div class="badge-qrCode-1 img-qr_code">
                        <img src="{{public_path().'/QrCode/qr_code_1.png'}}">
                    </div>
                @endif
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                @if(isset($users[1]))
                    @if(strlen($users[1]->first_name)<8 && strlen($users[1]->last_name)<8)
                        <h1 class="badge-label-2">Dr {{strtoupper(substr($users[1]->first_name,0,1)).substr($users[1]->first_name,1)
                .' '.strtoupper($users[1]->last_name)}}</h1>
                    @else
                        <div class="badge-label-2">
                            <h1 style="margin-left: 5px">
                                Dr {{strtoupper(substr($users[1]->first_name,0,1)).substr($users[1]->first_name,1)}}</h1>
                            <h1 class="small-text">{{strtoupper($users[1]->last_name)}}</h1>
                        </div>
                    @endif

                    <div class="badge-qrCode-2 img-qr_code">
                        <img src="{{public_path().'/QrCode/qr_code_2.png'}}">
                    </div>
                @endif
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                @if(isset($users[2]))
                    @if(strlen($users[2]->first_name)<8 && strlen($users[2]->last_name)<8)
                        <h1 class="badge-label-3">Dr {{strtoupper(substr($users[2]->first_name,0,1)).substr($users[2]->first_name,1)
                .' '.strtoupper($users[2]->last_name)}}</h1>
                    @else
                        <div class="badge-label-3">
                            <h1 style="margin-left: 5px">
                                Dr {{strtoupper(substr($users[2]->first_name,0,1)).substr($users[2]->first_name,1)}}</h1>
                            <h1 class="small-text">{{strtoupper($users[2]->last_name)}}</h1>
                        </div>
                    @endif

                    <div class="badge-qrCode-3 img-qr_code">
                        <img src="{{public_path().'/QrCode/qr_code_3.png'}}">
                    </div>
                @endif
            </div>
        </td>
        <td>
            <div class="badge">
                {{--
                                <img class="badge-img" src="{{public_path().'/badge/JPPS3-01.png'}}"/>
                --}}

                @if(isset($users[3]))
                    @if(strlen($users[3]->first_name)<8 && strlen($users[3]->last_name)<8)
                        <h1 class="badge-label-4">Dr {{strtoupper(substr($users[3]->first_name,0,1)).substr($users[3]->first_name,1)
                .' '.strtoupper($users[3]->last_name)}}</h1>
                    @else
                        <div class="badge-label-4">
                            <h1 style="margin-left: 5px">
                                Dr {{strtoupper(substr($users[3]->first_name,0,1)).substr($users[3]->first_name,1)}}</h1>
                            <h1 class="small-text">{{strtoupper($users[3]->last_name)}}</h1>
                        </div>
                    @endif

                    <div class="badge-qrCode-4 img-qr_code">
                        <img src="{{public_path().'/QrCode/qr_code_4.png'}}">
                    </div>
                @endif
            </div>
        </td>
    </tr>
</table>
</body>
</html>