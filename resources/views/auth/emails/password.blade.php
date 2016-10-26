No worries! We've got you. Just use the link below to reset your password:<br/><br/>

<a href="{{ $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>

