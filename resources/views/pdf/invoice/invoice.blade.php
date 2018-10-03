@extends('pdf/invoice/invoice-template')
@section('header')
    Date:
    <br>
    Labo: {{$lab->name}}
    <br>
@endsection

@section('number')
    <h3>Facture N°....</h3>
@endsection

@section('content')
    <table class="table table-striped table-bordered" style="width:100%">
        <tr>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Mobile</th>
            <th>Prix</th>
        </tr>
        @foreach($participants as $l)
            <tr>
                <td>{{$l->first_name}} </td>
                <td>{{$l->last_name}}</td>
                <td>{{$l->mobile}}</td>
                <td>{{$l->price}}TND</td>
            </tr>
        @endforeach
        @if ($displayTaxes)
            <tr>
                <td></td>
                <td></td>
                <td colspan="2" style="text-align: center; font-weight: bold;">Total HT:</td>
                <td colspan="2" style="text-align: center; font-weight: bold;">{{$totalPrice}} DT</td>
            </tr>
            @foreach($taxes as $key=>$t)
                <tr>
                    <td></td>
                    <td></td>
                    <td colspan="2" style="text-align: center; font-weight: bold;">{{$key}} {{$percentages[$key]}}%</td>
                    <td style="text-align: center; font-weight: bold;" colspan="2">{{$t}}DT</td>
                </tr>
            @endforeach
        @endif
        <tr>
            <td></td>
            <td></td>
            <td colspan="2" style="text-align: center; font-weight: bold;">Timbre Fiscal:</td>
            <td colspan="2" style="text-align: center; font-weight: bold;">0.600 DT</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td colspan="2" style="text-align: center; font-weight: bold;">Total TTC:</td>
            @if($displayTaxes)
                <td colspan="2" style="text-align: center; font-weight: bold;">{{$totalPrice+0.6}} DT</td>
            @else
                <td colspan="2" style="text-align: center; font-weight: bold;">{{$totalPrice+0.6}} DT</td>
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