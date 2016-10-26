@extends('app')

@section('title', 'Forgot Password | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>Forgot your password?</h1>
                <h3>We’ve all been there. Reset by entering your email.</h3>
            </div>
            <div class="container__block -centered">
                @if (count($errors) > 0)
                    <div class="validation-error fade-in-up">
                        <h4>Hmm, there were some issues with that submission:</h4>
                        <ul class="list -compacted">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form role="form" method="POST" action="{{ url('/password/email') }}">
                    {{ csrf_field() }}

                    <div class="form-item">
                        <label for="email" class="field-label">Email address</label>
                        <input name="email" type="text" class="text-field" placeholder="puppet-sloth@example.org" value="{{ $email or old('email') }}">
                    </div>

                    <div class="form-actions -padded">
                        <input type="submit" class="button" value="Request New Password">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <ul>
                    <li><a href="{{ url('login') }}">Log in to an existing account</a></li>
                    <li><a href="{{ url('register') }}">Create a DoSomething.org account</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop
