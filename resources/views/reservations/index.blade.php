@extends('layouts.app')
@section('title','Moje rezervace')
@section('content')
<h1>Moje rezervace</h1>
<a href="{{ route('reservations.create') }}">Vytvořit rezervaci</a>
<ul>
@foreach($reservations as $r)
    <li>{{ $r->room->name }} — {{ $r->start_at }} → {{ $r->end_at }} — <a href="{{ route('reservations.show',$r) }}">detail</a></li>
@endforeach
</ul>
@endsection