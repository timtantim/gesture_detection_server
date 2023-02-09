<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;


    public function setUp():void
    {
        parent::setUp();
        // Important code goes here.
        \Artisan::call('passport:install');
    }

    /**
     * 測試登入用戶
     *
     * @return void
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create();
        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];
        $this->json('POST','/api/authentication',$body,['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    /**
     * 測試登入token 是否合法
     *
     * @return void
     */
    public function test_token_is_valid()
    {
        $user = User::factory()->create();
        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];
        $response=$this->json(
            'POST',
            '/api/authentication',
            $body,
            ['Accept' => 'application/json']);
        $response_content_decode=json_decode($response->getOriginalContent());
        $access_token=$response_content_decode->access_token;
        $headers = [
            'Authorization' => "Bearer $access_token",
            'Accept'=> 'application/json'
        ];
        $this->json(
            'get', 
            '/api/validate_token', 
            [], 
            $headers)->assertStatus(200);
    }

   /**
     * 測試Refresh Token
     *
     * @return void
     */
    public function test_refresh_token()
    {
        $user = User::factory()->create();
        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];
        $response=$this->json(
            'POST',
            '/api/authentication',
            $body,
            ['Accept' => 'application/json']);
        $response_content_decode=json_decode($response->getOriginalContent());
        $refresh_token=$response_content_decode->refresh_token;
        $headers = [
            'Refreshtoken' => $refresh_token,
            'Accept'=> 'application/json'
        ];
        $this->json(
            'post', 
            '/api/refresh_oauth_token', 
            [], 
            $headers)->assertStatus(200);
    }

    /**
     * 測試Revoke Token
     *
     * @return void
     */
    public function test_revoke_token()
    {
        $user = User::factory()->create();
        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];
        $response=$this->json(
            'POST',
            '/api/authentication',
            $body,
            ['Accept' => 'application/json']);
        $response_content_decode=json_decode($response->getOriginalContent());
        $access_token=$response_content_decode->access_token;
        $headers = [
            'Authorization' => "Bearer $access_token",
            'Accept'=> 'application/json'
        ];
        $this->json(
            'post', 
            '/api/revoke_token', 
            [], 
            $headers)->assertStatus(200);
    }
}
