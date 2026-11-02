<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録画面が表示されること
     */
    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertOk();
    }

    /**
     * 正しい情報で POST /register 実行時、ユーザーがDBに保存され、ログイン状態でリダイレクトされること。
     */
    public function test_users_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * バリデーションエラー時、DBに保存されず、エラーメッセージとともに元の画面にリダイレクトされること。
     */
    public function test_users_cannot_register_with_invalid_data(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'unmatched',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-email',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /**
     * ログイン画面が表示されること
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    /**
     * 正しいクレデンシャルで POST /login 実行時、認証が成功すること。
     */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    /**
     * 誤ったクレデンシャル時、認証に失敗しエラーメッセージが返ること。
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    /**
     * ログイン状態のユーザーが GET /login または GET /register にアクセスした際、書籍一覧（/）へリダイレクトされること。
     */
    public function test_authenticated_users_are_redirected_when_visiting_login_or_register(): void
    {
        $user = User::factory()->create();

        $responseForLogin = $this->actingAs($user)->get('/login');
        $responseForLogin->assertRedirect('/');

        $responseForRegister = $this->actingAs($user)->get('/register');
        $responseForRegister->assertRedirect('/');
    }

    /**
     * ログイン状態で POST /logout 実行時、セッションが破棄されログイン画面にリダイレクトされること。
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
