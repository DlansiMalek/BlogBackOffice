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
            font-size:xx-small
        }

        h3 {
            line-height: 2;
            font-weight: 800;
            font-size: x-small;
            font-weight: bold;
        }
        ul, li {
            list-style: square;
            margin-bottom: -16px;
        }
        .content {
            margin-top: 60px;
        }
    </style>
</head>

<body style="padding: 0px; margin: 0px;">
    <div style="border: 2px solid darkgray; border-radius:5px; padding: 1% 5%; width: 50%; margin:auto ;" id="title-container">
        <div style="text-align:center; width: 100%;">
            <b>{{$congress->name}}</b>
        </div>
    </div>
    <div class="content">
        @foreach($meetings as $key => $meeting)
        <ul>
            <h3>Meeting {{$key +1}}</h3>
            <li>
                <h4> Nom du meeting : {{$meeting->name}} </h4>
            </li>
            <li>
                @if($meeting->user_meeting[0]->organizer)
                <h4> Organisateur : {{$meeting->user_meeting[0]->organizer->last_name}} {{$meeting->user_meeting[0]->organizer->first_name}}</h4>
                @endif
            </li>
            <li>
                @if($meeting->user_meeting[0]->participant)
                <h4> Participant(s): {{$meeting->user_meeting[0]->participant->last_name}} {{$meeting->user_meeting[0]->participant->first_name}}</h4>
                @endif
            </li>
            <li>
                <h4> @if($meeting->start_date)
                    Date et heure du début : {{date('d/m/Y : H:i',strtotime($meeting->start_date))}}</h4>
                @endif
            </li>
            <li>
                <h4>
                    @if($meeting->end_date)
                    Date et heure du fin : {{date('d/m/Y : H:i',strtotime($meeting->end_date))}}
                    @endif
                </h4>
            </li>
        </ul>
        @endforeach
    </div>
</body>

</html>