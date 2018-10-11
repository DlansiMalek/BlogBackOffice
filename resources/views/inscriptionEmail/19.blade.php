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
    Le comité d'organisation de la <span
            class="bold-style"> 9èmes  Journées Médicales de l'Hôpital des FSI-La Marsa</span>,
    qui se tiendront les 21 et 22 septembre 2018 à l'espace Arena, a le plaisir de vous annoncer que votre inscription
    aux sessions et ateliers suivants a été retenue: <br>
</p>
<ul>
    @foreach($accesss as $access)
        <li>
            {{$access->name}}
        </li>
    @endforeach
</ul>
<br/>
Pour faciliter l'accès rapide aux salles et pour un bon déroulement du programme veuillez respecter les instructions
suivantes:
<br/>
<h2>
    Préparez vous à l'avance!
</h2>
<ol>
    <li>Venez au moins une demi-heure avant le début de l'atelier ou de la session.</li>
    <li>Votre badge contenant votre QR code vous est envoyé en pièce jointe à ce mail,<span class="important-word"> imprimez le à l'avance
        ,découpez le </span>
        et ramenez le avec vous.
    </li>
    <li>
        Dès votre arrivée passez directement à la borne d'accueil n°3 pour effectuer le <span class="bold-style">paiement</span>
        : 30 dt pour les
        médecins, 20 dt pour les résidents et les internes, 20dt pour le personnel paramédical, les administratifs et
        les agents d'accueil (sauf si prise en charge par l’hôpital.
    </li>
    <li>
        Une fois le paiement effectué, passez à la <span class="bold-style">borne n°4</span> pour avoir votre <span
                class="bold-style">cartable</span>
        et votre <span class="bold-style">porte badge </span>dans
        lequel vous mettrez le badge que vous avez déjà imprimé
    </li>
    <li>
        Pour valider votre présence votre <span class="important-word">badge sera scanné</span> au niveau
        du <span class="bold-style">hall des
            inscriptions</span> et <span class="bold-style">à l'entrée de
        chaque salle</span> (ateliers et sessions), ayez donc la courtoisie de le présenter à chaque fois aux membres de
        notre
        équipe d'organisation.
    </li>
    <li>
        Si vous désirez <span class="bold-style">modifier votre choix d'atelier</span>, vous pouvez repasser à l'accueil
        <span class="bold-style">(borne 5)</span> , vous serez
        inscrit à l'atelier que vous choisissez par les membres de notre équipe selon la disponibilité des places
    </li>
    <li>
        Enfin n'oubliez pas que tous nos <span class="bold-style">ateliers et sessions <span class="important-word">sont accréditées par l'INEAS </span>(INASanté)</span>,
        un <span class="important-word">minimum de
            présence</span> de 75 min pour les ateliers de 90 mn, de 100 min pour les sessions de 120 minutes et 50 min
        pour
        l'atelier d'une heure sont donc indispensables pour avoir les <span
                class="bold-style">attestations de présence</span> respectives qui seront
        délivrées à la <span class="bold-style">borne d'accueil n°6</span>.
    </li>
</ol>

<br/>
Au plaisir de vous voir parmi nous,<br/>
Bien cordialement,<br/>
Le comité d'organisation des 9èmes Journées Médicales de l'hôpital des FSI.<br/>
</body>
</html>