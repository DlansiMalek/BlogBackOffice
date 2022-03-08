<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        .body {
            font-size: 0.75em;
        }

        ul, li {
            margin: 0px 5px;
            padding: 0;
        }
    </style>
</head>
<body style="padding: 0px; margin: 0px;">
<div style="border: 2px solid darkgray; border-radius:5px; padding: 1% 5%; width: 50%; margin:0% 25%"
     id="title-container">
    <div style="text-align:center; width: 100%;">
       <b>{{$meeting->name}}</b>
       <br>
       @if($meeting->start_date)
            {{date('d/m/Y',strtotime($meeting->start_date))}}
            @if($meeting->end_date)
                - {{date('d/m/Y',strtotime($meeting->end_date))}}
            @endif
        @endif
        <br>
    </div>
</div>

</body>
</html>