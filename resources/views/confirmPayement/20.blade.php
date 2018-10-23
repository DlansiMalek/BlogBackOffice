<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        .important-word {
            font-weight: bold;
            color: red;
            font-size: 20px;
        }

        .bold-style {
            font-weight: bold;
        }
    </style>
</head>
<body>
<p>
    Cher collègue, <br/>
    Votre pré-inscription aux ateliers à l’espace Arena, Lac 1 dans le cadre du 46ème Congrès Médical Maghrébin <span
            class="bold-style">est confirmée: </span>
</p>
<ul>
    @foreach($accesss as $access)
        <li>
            {{$access->name}} du <span class="bold-style">{{$access->theoric_start_data}}</span>
            au <span class="bold-style">{{$access->theoric_end_data}}</span>
        </li>
    @endforeach
</ul>
<br/>
Vous trouverez ci-joint votre badge à imprimer avant la date du congrés.
<br/>
Si vous voulez changer vos ateliers à travers ce lien: <a href="{{$link}}">Lien</a>
<br/>
En cas de problème technique, veuillez contacter le numéro suivant : +216 53 780 474
<br/>
Au plaisir de vous voir parmi nous.<br/>
Bureau de la STSM.<br/>
</body>
</html>