@extends('layouts.app')

@section('title', 'Create Account | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <h1>Create a DoSomething.org account to get started!</h1>
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

        <form id="profile-registration-form" method="POST" action="{{ url('register') }}">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-item">
                <label for="first_name" class="field-label">
                    <div class="validation">
                        <div class="validation__label">First Name <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="first_name" type="text" id="first_name" class="text-field required js-validate" placeholder="What do we call you?"value="{{ old('first_name') }}" autofocus data-validate="first_name" data-validate-required />
            </div>

            <div class="form-item">
                <label for="birthdate" class="field-label">
                    <div class="validation">
                        <div class="validation__label">Birthday <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="birthdate" type="text" id="birthdate" class="text-field required js-validate" placeholder="MM/DD/YYYY" value="{{ old('birthdate') }}" data-validate="birthday" data-validate-required />
            </div>

            <div class="form-item">
                <label for="email" class="field-label">
                    <div class="validation">
                        <div class="validation__label">Email address <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="email" type="text" id="email" class="text-field required js-validate" placeholder="puppet-sloth@example.org" value="{{ old('email') }}" data-validate="email" data-validate-required />
            </div>

            <div class="form-item">
                <label for="mobile" class="field-label">Cell Number <em>(optional)</em></label>
                <input name="mobile" type="text" id="mobile" class="text-field js-validate" placeholder="(555) 555-5555" value="{{ old('mobile') }}" data-validate="phone" />
            </div>

            <div class="form-item">
                <label for="password" class="field-label">
                    <div class="validation">
                        <div class="validation__label">Password <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="password" type="password" id="password" class="text-field required js-validate" placeholder="6+ characters... make it tricky!" data-validate="password" data-validate-required data-validate-trigger="#password_confirmation" />
            </div>

            <div class="form-item">
                <label for="password_confirmation" class="field-label">
                    <div class="validation">
                        <div class="validation__label">Confirm Password <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="password_confirmation" type="password" id="password_confirmation" class="text-field required js-validate" placeholder="Just double checking!" data-validate="match" data-validate-required data-validate-match="#password" />
            </div>

            <div class="form-actions -padded">
                <input type="submit" class="button" value="Create New Account">
            </div>
        </form>
    </div>

    <div class="container__block -centered">
        <ul>
            <li><a href="{{ url('login') }}">Log in to an existing account</a></li>
        </ul>
    </div>
@stop
