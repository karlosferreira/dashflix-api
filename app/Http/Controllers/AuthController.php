<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Gerar um token JWT para o usuário
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token, 
                'user' => $user
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Registro de um novo usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validar os dados do formulário
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Se a validação falhar, retornar os erros
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Criar um novo usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Gerar um token JWT para o usuário
        $token = JWTAuth::fromUser($user);

        // Retornar os dados do usuário junto com o token
        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * Logout do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function deleteUser(Request $request, $id)
    {
        // Validate if the authenticated user is the same user being deleted
        // (You can implement additional logic based on roles/permissions)
        if ($id != Auth::user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
    
        $user->delete();
    
        return response()->json(['message' => 'Usuário excluído com sucesso!']);
    }
    
    public function updateUser(Request $request, $id)
    {
        // Validate if the authenticated user is the same user being edited
        // (You can implement additional logic based on roles/permissions)
        if ($id != Auth::user()->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,'.$id, // Exclude the user itself from email uniqueness validation
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
    
        $user->update($request->all());
    
        return response()->json(['message' => 'Usuário atualizado com sucesso!']);
    }
    
}
