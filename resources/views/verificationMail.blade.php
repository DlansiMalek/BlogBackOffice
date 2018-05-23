<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Validation de Votre Inscrption dans le congrés {{ $congress_name }}</h2>

<p>Vous étes :</p>
<ul>
    <li><strong>Nom</strong> : {{ $first_name }}</li>
    <li><strong>Prenom</strong> : {{ $last_name }}</li>
</ul>
<p>Pour confirmer veuillez activer votre compte avec ce lien : </p>

<p><u><a href="{{$link}}">Confirmer votre mail</a> </u></p>
</body>
</html>