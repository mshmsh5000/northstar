@extends('app')

@section('title', 'Log In | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>You're logged in.</h1>
            </div>
            <div class="container__block -centered">
                <p><a href="{{ url('logout') }}" class="button -secondary">Log out</a></p>
                <p class="footnote">(That's all for now.)</p>
            </div>
        </div>
    </div>
@stop
