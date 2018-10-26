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
    Votre pré-inscription
    @if($user->isPoster ==1)
        au congrès
        @if(sizeof($accesss)>0)
            et
        @endif
    @endif
</p>
@if(sizeof($accesss)>0)
    <p>
        à (aux) l'atelier(s) :
    </p>
    <ul>
        @foreach($accesss as $access)
            <li>
                {{$access->name}} <span class="bold-style">qui se déroulera
            le {{\App\Services\Utils::convertDateFrench($access->theoric_start_data)}}
                    de {{\App\Services\Utils::getTimeFromDateTime($access->theoric_start_data)}}
                    à {{\App\Services\Utils::getTimeFromDateTime($access->theoric_end_data)}}
            </span>
            </li>
        @endforeach
    </ul>
@endif
<br/>
à l’espace Arena, Lac 1 dans le cadre du 46ème Congrès Médical Maghrébin <span
        class="bold-style">a été enregistrée.</span>
<br/>
Veuillez procéder au paiement des frais d’inscription à (aux) l’atelier(s) par un virement de la somme de <span
        class="bold-style">{{$user->price}}
    dt</span>
au nom de la Société Tunisienne des Sciences Médicales au RIB suivant: <span class="bold-style">12206000540500036081, Agence UIB El Menzah 6,</span>
puis téléchargez votre reçu sur ce lien: <a href="{{$link}}">Lien</a>
<br/>
Vous aurez <span class="bold-style">72 heures</span> pour procéder au paiement, dépassé ce délai, nous sommes au regret
d’annuler votre pré-inscription.
<br/>
En cas de problème technique, veuillez contacter le numéro suivant : +216 53 780 474.
<br/>
Au plaisir de vous voir parmi nous.<br/>
Bureau de la STSM.<br/>
</body>
</html>