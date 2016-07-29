@extends('app')

@section('title', 'Log In | DoSomething.org')

@section('content')
    <header role="banner" class="header">
        <div class="wrapper">
            <h1 class="header__title">Log In</h1>
            <p class="header__subtitle">Please log in to continue!</p>
        </div>
    </header>

    <div class="container">
        <div class="wrapper">
            <div class="container__block -narrow">
                <p>Hey, <strong>Application Name</strong> wants you to log in.</p>

                <form action="#">
                    <input type="hidden" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>
@stop
