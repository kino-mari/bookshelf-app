<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * APIログインしてトークンを発行する
     */
    public function login(Request $request): JsonResponse
    {
        // メールアドレスとパスワードを検証
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // メールアドレスからユーザーを取得
        $user = User::where('email', $credentials['email'])->first();

        // ユーザーが存在しない、またはパスワードが違う場合
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ], 401);
        }

        // SanctumのAPIトークンを発行
        $token = $user->createToken('api-token')->plainTextToken;

        // トークンとユーザー情報をJSONで返す
        return response()->json([
            'message' => 'ログインしました。',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }
}
