<!DOCTYPE HTML>
<html>
<head>
    <title>Facture</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <style>
        .boxed {
            border-radius: 20px;
            -moz-border-radius: 10px 10px 10px 10px;
            -webkit-border-radius: 10px 10px 10px 10px;
            border: 1px solid #000000;
            width: 110%;
            margin-left: 2%;
            margin-top: -1%;
            margin-bottom: 2%;
            border-color: #ddd;
            padding: 1.2%;
            padding-left: 4%;

        }

        p {
            margin: 0.2%;
            font-size: 70%;
        }

        table, td, tr, th {
            border: 1px solid black;
            text-align: center;
        }


    </style>
</head>
<body>
<div class="row">
    <div class="col-xs-5" style="padding-top: -3%">
        <h2 style="padding-left: 30%;">VayeCongress</h2>
        <h6 style="padding-left: 50%; color: green; margin-top: -1.6%">Vayetek SARL</h6>
        <center>
            <p>Immeuble Espace Tunis Bloc F, 3 éme étage</p>
            <p>Montplaisir, Tunis 1073</p>
            <p> (+216) 53 780 474</p>
        </center>
    </div>
    <div class="col-xs-5" style="padding-top: 2%">
        <div class="boxed">
            @yield('header')
        </div>
    </div>
</div>
@yield('number')
<div class="row" style="margin-top: 2%">
    @yield('content')
</div>
@yield('footer')
</body>


</html>