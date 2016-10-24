@extends('layouts.app')

@section('title', 'Profile | DoSomething.org')

@section('content')
    <main role="main">
        <article class="profile">
            <header role="banner" class="header">
                <div class="wrapper">
                    <h1 class="header__title">Edit Profile</h1>
                </div>
            </header>

            <section class="container -padded">
                <div class="wrapper">
                    <div class="container__block -centered">
                        <p>{{ $user->id }}</p>
                        <p>{{ $user->first_name.' '.$user->last_name }}</p>
                        <p>{{ $user->email }}</p>
                        <p>{{ $user->mobile }}</p>
                        <p>{{ $user->addr_street1 }}</p>
                        <p>{{ $user->addr_street2 }}</p>
                        <p>{{ $user->addr_city }}</p>
                        <p>{{ $user->addr_state }}</p>
                        <p>{{ $user->addr_zip }}</p>
                        <p>{{ $user->country }}</p>
                    </div>
                </div>
            </section>
        </article>
    </main>
@stop
