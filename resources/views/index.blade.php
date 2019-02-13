@extends('layouts.app')
@section('content')
<div class="container">
    <employeeform-component 
        :employeedata="{{json_encode($employeeData)}}" 
        formroute="{{route('getHours')}}" maxdate="{{$maxDate}}"></employeeform-component>
</div>
@endsection
