<?php

namespace App\Http\Controllers;

use App\StravaApi\Auth as StravaApiAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StravaAuthController extends Controller
{
    public function __construct(
        protected StravaApiAuth $stravaApiAuth,
    )
    {}

    public function login(Request $request): RedirectResponse
    {
        return redirect($this->stravaApiAuth->getLoginUrl());
    }

    public function exchangeToken(Request $request): int
    {
        $code = $request->query('code');

        $athlete = $this->stravaApiAuth->handleUserLogin($code);

        return $athlete->id;
    }
}
