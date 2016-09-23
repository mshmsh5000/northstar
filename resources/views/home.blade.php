@extends('app')

@section('title', 'Profile | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <article class="figure -medium">
                    <div class="figure__media">
                        <img class="avatar" alt="avatar" src="{{ $user->avatar or asset('avatar-placeholder.png') }}" />
                    </div>
                    <div class="figure__body">
                        You are logged in as <strong>{{ $user->displayName() }}</strong>.
                    </div>
                </article>
            </div>
            <div class="container__block -centered">
                <div class="form-actions"><a href="{{ url('logout') }}" class="button -secondary">Log out</a></div>
            </div>
        </div>
    </div>
@stop
