<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Cher collègue, <br/>
    Le comité d'organisation des <span style="font-weight: bold"> {{$congressName}}</span>
    , qui se tiendront les 21 et 22 septembre 2018 à l'espace Arena, a le plaisir de vous annoncer que votre inscription
    aux: <br>
</p>
<ul>
    @foreach($accesss as $access)
    <li>
        {{$access->name}}
    </li>
    @endforeach
</ul>
<br/>
ont été retenue.<br/>
Vous recevrez prochainement un mail explicatif concernant votre particpation.
<br/>
Cordialement<br/>
Pour le comité d’organisation<br/>
Dr Syrine Bellakhal
</body>
</html>