!DOCTYPE html>
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
            class="bold-style">a été enregistrée</span>
</p>
<ul>
    @foreach($accesss as $access)
        <li>
            {{$access->name}} du {{$access->theoric_start_data}} au {{$access->theoric_end_data}}
        </li>
    @endforeach
</ul>
<br/>
Veuillez procéder au paiement des frais d’inscription à l’atelier par un virement de la somme de {{$user->price}} dt
au nom de la Société Tunisienne des Sciences Médicales au RIB suivant: <span class="bold-style">12206000540500036081, Agence UIB El Menzah 6,</span>
puis téléchargez votre reçu sur ce lien : {{$link}}
<br/>
Vous aurez 72 heures pour procéder au paiement, dépassé ce délai, nous sommes au regret d’annuler votre pré-inscription.
<br/>
Au plaisir de vous voir parmi nous.<br/>
Bureau de la STSM.<br/>
</body>
</html>