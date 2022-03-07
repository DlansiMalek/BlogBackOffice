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
        Tunis, Tunisie
        {{-- TODO Add real location here--}}
        <br>
    </div>
</div>

</body>
</html>