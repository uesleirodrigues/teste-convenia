<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Efetuar login e gerar o token de autenticação.
     */
    public function login(Request $request)
    {
        // Validar os dados de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Retornar erro se a validação falhar
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Verificar se o usuário existe
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        // Gerar o token JWT
        try {
            $token = JWTAuth::attempt($request->only('email', 'password'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível criar o token'], 500);
        }

        if (!$token) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        return response()->json([
            'message' => 'Login bem-sucedido',
            'token' => $token,
        ]);
    }

    /**
     * Efetuar logout e revogar o token de autenticação.
     */
    public function logout(Request $request)
    {
        // Revogar o token atual do usuário
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logout bem-sucedido']);
    }

    /**
     * Método para obter os detalhes do usuário autenticado.
     */
    public function user(Request $request)
    {
        // Obter o usuário autenticado
        try {
            $user = JWTAuth::user();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível recuperar os dados do usuário'], 500);
        }

        return response()->json($user);
    }
}
