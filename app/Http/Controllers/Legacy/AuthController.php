<?php

namespace Northstar\Http\Controllers\Legacy;

use Illuminate\Http\Request;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Transformers\TokenTransformer;
use Northstar\Http\Transformers\UserTransformer;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Northstar\Auth\Registrar;

class AuthController extends Controller
{
    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var TokenTransformer
     */
    protected $transformer;

    /**
     * Validation rules for login routes.
     * @var array
     */
    protected $loginRules = [
        'email' => 'email|required_without:mobile',
        'mobile' => 'required_without:email',
        'password' => 'required',
    ];

    /**
     * AuthController constructor.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->transformer = new TokenTransformer();

        $this->middleware('scope:user');
        $this->middleware('throttle', ['only' => ['verify']]);
    }

    /**
     * Verify user credentials without making a session.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function verify(Request $request)
    {
        $request = normalize('credentials', $request);
        $this->validate($request, $this->loginRules);

        $credentials = $request->only('email', 'mobile', 'password');
        $user = $this->registrar->resolve($credentials);

        if (! $this->registrar->validateCredentials($user, $credentials)) {
            throw new UnauthorizedHttpException(null, 'Invalid credentials.');
        }

        return $this->item($user, 200, [], new UserTransformer());
    }
}
