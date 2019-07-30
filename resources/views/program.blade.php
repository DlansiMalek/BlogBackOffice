<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">
</head>
<body style="margin:5%">
<div class="row">
    <div style="border: 2px solid darkgray; border-radius:5px; padding: 1% 5%;" id="logo-title-container"
         class="col-sm-6 offset-sm-3">
        <div style="text-align:center; width: 100%;">
            <b>{{$congress->name}}</b>
            <br>
            Tunis, Tunisie
            {{-- TODO Add real location here--}}
            <br>
            @if($congress->start_date)
                {{date('d/m/Y',strtotime($congress->start_date))}}
                @if($congress->end_date)
                    - {{date('d/m/Y',strtotime($congress->end_date))}}
                @endif
            @endif
        </div>
    </div>
</div>

@if($congress->config->logo)
    <div style="text-align: center; width: 100%">
        <img src="{{\App\Services\Utils::$baseUrl."congress/".$congress->congress_id."/logo"}}" height="96px"
             style="margin: 10px 0px"/>
    </div>
@endif

<script src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
<script src="{{asset('js/bootstrap.min.js')}}"></script>

</body>
</html>