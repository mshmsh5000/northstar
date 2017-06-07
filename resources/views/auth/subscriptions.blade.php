@extends('layouts.app')

@section('title', 'Unsubscribe | DoSomething.org')

@section('content')

    <div class="container__block -centered ">
        <p>Don’t want to receive emails about this competition?</p>

        <form role="form" method="POST" class="form-actions -padded" action="{{ url('unsubscribe') }}">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <input type="hidden" name="competition" value="{{ Request::get('competition_id') }}">
            <input type="hidden" name="user" value="{{ Request::get('northstar_id') }}">
            <input type="submit" class="button" value="{{ trans('Unsubscribe') }}" />
        </form>
    </div>

@stop
