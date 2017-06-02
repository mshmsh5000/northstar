@extends('layouts.app')

@section('title', 'Unsubscribe | DoSomething.org')

@section('content')

	<div class="container__block -centered ">
		<p>Don’t want to receive emails about this competition?</p>

		<form role="form" method="GET" class="form-actions -padded" action="{{ url('unsubscribe/competition') }}">
			<input type="submit" class="button" value="{{ trans('Unsubscribe') }}" />
		</form>
	</div>

@stop
