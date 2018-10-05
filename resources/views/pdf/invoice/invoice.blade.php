@extends('pdf/invoice/invoice-template')
@section('header')
    Fait le  {{$today}}
    <br>
@endsection

@section('number')
    <h3>Facture N°....</h3>
    <h3>Client/Organization: {{$lab->name}}</h3>
@endsection

@section('content')
    <table class="table table-striped table-bordered" style="width:100%">
        <tr>
            <th>Libellé</th>
            <th>Description</th>
            <th>Prix</th>
        </tr>
        @foreach($packs as $pack)
            <tr>
                <td>{{$congress->name}} {{$pack->label}}</td>
                <td>{{$pack->label}}</td>
                <td>{{$pack->price}}TND</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2">Timbre Fiscal:</td>
            <td colspan="1">0.600 TND</td>
        </tr>
        <tr>
            <td colspan="2">Total TTC:</td>
            @if($displayTaxes)
                <td colspan="1">{{$totalPrice+0.6}} TND</td>
            @else
                <td colspan="1">{{$totalPrice+0.6}} TND</td>
            @endif
        </tr>
    </table>
@endsection

@section('footer')
    <div class="row">
        <div class="col-xs-6" style="text-align: center;">
            Signature du Commercial
            <br>
            <br>
            <div class="boxed" style="width: 65%; height: 120px; margin-left: 15%"></div>
        </div>
        <div class="col-xs-5" style="text-align: center;">Signature & Cachet du Client</div>
        <br>
        <br>
        <div class="boxed" style="width:30%; height: 120px; margin-left: 61%"></div>
    </div>
@endsection