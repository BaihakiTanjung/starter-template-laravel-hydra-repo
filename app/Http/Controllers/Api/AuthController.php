<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\User\UserRepository;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    private $UserRepository;

    public function __construct(UserRepository $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }


    public function login(LoginRequest $request)
    {
        return $this->UserRepository->login($request);
    }

    public function logout(Request $request)
    {
        return $this->UserRepository->logout($request);
    }

    public function me(Request $request)
    {
        return $this->UserRepository->me($request);
    }
}
