<!DOCTYPE html>
<html lang="fr">

<head>
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <meta charset="utf-8">
    <style>
        html,
        body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        html,
        body,
        h4 {
            line-height: 1.5;
            font-weight: 600;
            font-size: small;
        }

        .content {
            margin-top: 60px;
        }
    </style>
</head>

<body style="padding: 0px; margin: 0px;">
    <div style="border: 2px solid darkgray; border-radius:5px; padding: 1% 5%; width: 50%; margin:0% 25% ;" id="title-container">
        <div style="text-align:center; width: 100%;">
            <b>{{$congress->name}}</b>
        </div>
    </div>

    <div class="content">
        <ul>
            <li>
                <h4> Nom du meeting : {{$meeting->name}} </h4>
            </li>
            <li>
                <h4> Organisateur : {{$organizer->first_name}} {{$organizer->last_name}}</h4>
            </li>
            <li>
                <h4> Participant : {{$participant->first_name}} {{$participant->last_name}}</h4>
            </li>

            <li>
                <h4> @if($meeting->start_date)
                    Date et heure du début : {{date('d/m/Y : H:i',strtotime($meeting->start_date))}}</h4>
            </li>
            <li>
                <h4>
                    @if($meeting->end_date)
                    Date et heure du fin : {{date('d/m/Y : H:i',strtotime($meeting->end_date))}}
                    @endif
                    @endif
                </h4>
            </li>
        </ul>
    </div>
</body>

</html>