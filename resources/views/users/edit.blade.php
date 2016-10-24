@extends('layouts.app')

@section('title', 'Edit Profile | DoSomething.org')

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
                    <div class="container__block -narrow">
                        <form method="POST" action="">
                            {{ method_field('PATCH') }}

                            <p>{{ $user->id }}</p>

                            <div class="form-item -padded">
                                <label class="field-label">First Name:</label>
                                <input type="text" class="text-field" value="{{ $user->first_name }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="first_name" class="field-label">Last Name:</label>
                                <input type="text" name="first_name" class="text-field" value="{{ $user->last_name }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="email" class="field-label">Email:</label>
                                <input type="email" name="email" class="text-field" value="{{ $user->email }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="mobile" class="field-label">Cell #:</label>
                                <input type="text" name="mobile" class="text-field" value="{{ $user->mobile }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="addr_street1" class="field-label">Address Street 1:</label>
                                <input type="text" name="addr_street1" class="text-field" value="{{ $user->addr_street1 }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="addr_street2" class="field-label">Address Street 2:</label>
                                <input type="text" name="addr_street2" class="text-field" value="{{ $user->addr_street2 }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="addr_city" class="field-label">City:</label>
                                <input type="text" name="addr_city" class="text-field" value="{{ $user->addr_city }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="addr_state" class="field-label">State:</label>
                                <input type="text" name="addr_state" class="text-field" value="{{ $user->addr_state }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="addr_zip" class="field-label">Zip Code:</label>
                                <input type="text" name="addr_zip" class="text-field" value="{{ $user->addr_zip }}" />
                            </div>

                            <div class="form-item -padded">
                                <label for="country" class="field-label">Country:</label>
                                <input type="text" name="country" class="text-field" value="{{ $user->country }}" />
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </article>
    </main>
@stop
