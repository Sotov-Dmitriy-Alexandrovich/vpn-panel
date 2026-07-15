@extends('layouts.app')
@section('content')
<h2>Веб-консоль</h2>
<div class="console-box"><input type="text" id="cmd-input" placeholder="Введите команду..."><button onclick="runCmd()">Выполнить</button><pre id="cmd-output"></pre></div>
<script>initConsole();</script>
@endsection
