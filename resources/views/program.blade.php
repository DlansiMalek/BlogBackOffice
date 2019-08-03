<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">
    <style>
        .body {
            font-size: 0.75em;
        }
    </style>
</head>
<body style="padding: 0px; margin: 0px;">
<div style="border: 2px solid darkgray; border-radius:5px; padding: 1% 5%; width: 50%; margin:0% 25%"
     id="title-container">
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

@if($congress->config->logo)
    <div style="text-align: center; width: 100%">
        <img src="{{\App\Services\Utils::$baseUrl."congress/".$congress->congress_id."/logo"}}" height="96px"
             style="margin: 10px 0px"/>
    </div>
@endif

{{--<table class="table table-bordered" style="table-layout: auto">--}}
{{--    <div style="border-bottom:  1px gray solid;"></div>--}}
<div class="body">
    <?php $oldkey = null?>
    @foreach(array_keys($accesses) as $start_date)
        @if(!$oldkey || $accesses[$start_date][0]['day'] != $accesses[$oldkey][0]['day'])
            <div style="width: 93.9%; border: 1px gray solid;padding: 10px;margin-bottom: 8px">
                {{$accesses[$start_date][0]['day']}}
            </div>
        @endif
        <div style="width: 100%;">
            <nobr>
                <div style="width: 10%; display: inline-block;border: 1px gray solid;padding: 10px;">
                    {{$accesses[$start_date][0]['time']}}
                </div>
                @foreach($accesses[$start_date] as $access)
                    <div style="width:<?= (string)((84 - sizeof($accesses[$start_date]) * 2) / sizeof($accesses[$start_date])) . '%'?>;
                                 display: inline-block;border: 1px gray solid;padding: 10px; margin-left: -5px">
                        <b>{{$access['name']}}</b>
                        <br>
{{--                        @foreach(array_keys($access['chairs']) as $index)--}}
{{--                            {{$access['chairs'][$index]['first_name']}} {{$access['chairs'][$index]['last_name']}}--}}
{{--                            @if ($index!=sizeof($access['chairs'])-1)--}}
{{--                                ,--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
                    </div>
                @endforeach
            </nobr>
        </div>

        {{--        {{ (string)(80/sizeof($accesses[$start_date])).'%'}}--}}
        <?php $oldkey = $start_date?>
    @endforeach

    <script src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>
</div>
</body>
</html>