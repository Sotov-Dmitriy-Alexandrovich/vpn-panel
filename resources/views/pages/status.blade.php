@extends('layouts.app')
@section('content')
<h2>Статус сервера</h2>
<pre id="status-output">Загрузка...</pre>
<script>loadStatus();</script>
@endsection
