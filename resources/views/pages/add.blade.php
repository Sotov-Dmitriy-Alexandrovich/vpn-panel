@extends('layouts.app')
@section('content')
<h2>Добавить пользователя</h2>
<form id="add-form"><input type="text" id="client-name" placeholder="Имя пользователя" required><button type="submit">Подключить</button></form>
<p id="add-result"></p>
<script>initAddForm();</script>
@endsection
