<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Inscription Congrés</h2>
<p>Vous étes :</p>
<ul>
    <li><strong>Nom</strong> : {{ $nom }}</li>
    <li><strong>Prenom</strong> : {{ $prenom }}</li>
    <li><strong>CIN</strong> : {{ $CIN }}</li>
</ul>
<p>Nous vous envoyons cet e-mail pour vous demander de confirmer votre inscription à La III ème
    Rencontre Franco-Maghrébine de Néonatalogie.<br>
    Pour confirmer veuillez activer votre compte avec ce lien : </p><br>
<a href="{{$link}}">Confirmer Votre e-mail</a>

</body>
</html>