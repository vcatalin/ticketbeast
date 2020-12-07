<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('authentication.login');
    }

    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (!Auth::attempt($credentials)) {
            return redirect('/login')
                ->withInput(['email' => $request->input('email')])
                ->withErrors([
                    'email' => ['These credentials do not match our records.'],
            ]);
        }

        return redirect('/backstage/concerts/new');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
