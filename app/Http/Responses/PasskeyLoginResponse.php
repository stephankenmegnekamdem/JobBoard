<?php

namespace App\Http\Responses;

use App\Support\HomeRoute;
use Illuminate\Http\JsonResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

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
