<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;
use App\Support\HomeRoute;

class PasskeyLoginResponse implements PasskeyLoginResponseContract
{


    public function toResponse($request): Response
    {
        $home = HomeRoute::for($request->user());

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => redirect()->intended($home)->getTargetUrl()], 200)
            : redirect()->intended($home);
    }
}
