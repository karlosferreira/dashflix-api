<?php

// app/Http/Controllers/Auth/SocialController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $user = Socialite::driver($provider)->user();

        // Verificar se o usuário já existe no banco de dados
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // Login do usuário existente
            Auth::login($existingUser, true);
        } else {
            // Criar um novo usuário
            $newUser = new User();
            $newUser->name = $user->getName();
            $newUser->email = $user->getEmail();
            $newUser->password = bcrypt(Str::random(16));
            $newUser->save();

            // Login do novo usuário
            Auth::login($newUser, true);
        }

        return redirect()->intended(route('home'));
    }
}