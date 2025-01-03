<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Depends;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiTokenTest extends TestCase
{   
    public static array $testUserData;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        // Create test user (BD store later)
        $name = "test_" . time();
        self::$testUserData = [
            "name"      => "{$name}",
            "email"     => "{$name}@mailinator.com",
            "password"  => "12345678"
        ];
    }

    public function test_register()
    {
        // Create user using API web service
        $data = self::$testUserData;
        $response = $this->postJson('/api/register', $data);
        // Check response
        $response->assertOk();
        // Check validation errors
        $response->assertValid(["name"]);
        $response->assertValid(["email"]);
        $response->assertValid(["password"]);
        // Check TOKEN response
        $this->_test_token($response);
    }

    public function test_register_error()
    {
        // Create user using API web service
        $response = $this->postJson('/api/register', [
            "name"      => "",
            "email"     => "mailinator.com",
            "password"  => "12345678",
        ]);
        // Check response
        $response->assertStatus(422);
        // Check validation errors
        $response->assertInvalid(["name"]);
        $response->assertInvalid(["email"]);
        // Check JSON properties
        $response->assertJson([
            "message" => true, // any value
            "errors"  => true, // any value
        ]);       
        // Check JSON dynamic values
        $response->assertJsonPath("message",
            fn ($message) => !empty($message) && is_string($message)
        );
        $response->assertJsonPath("errors",
            fn ($errors) => is_array($errors)
        );
    }
    
    #[Depends('test_register')]
    public function test_login()
    {
        // Login using API web service
        $data = self::$testUserData;
        $response = $this->postJson('/api/login', $data);
        // Check response
        $response->assertOk();
        // Check validation errors
        $response->assertValid(["email","password"]);
        // Check TOKEN response
        $this->_test_token($response);
    }

    public function test_login_invalid()
    {
        // Login using API web service
        $response = $this->postJson('/api/login', [
            "email"     => "notexists@mailinator.com",
            "password"  => "12345678",
        ]);
        // Check response
        $response->assertStatus(401);
        // Check JSON properties
        $response->assertJson([
            "success" => false,
            "message" => true, // any value
        ]);
        // Check validation errors
        $response->assertValid(["email","password"]);
    }

    #[Depends('test_login')]
    public function test_logout()
    {
        $user = new User(self::$testUserData);
        Sanctum::actingAs(
            $user,
            ['*'] // grant all abilities to the token
        );
        // Logout using API web service
        $response = $this->postJson('/api/logout');
        // Check response
        $response->assertOk();
        // Check JSON properties
        $response->assertJson([
            "success" => true,
            "message" => true, // any value
        ]);
    }

    public function test_logout_unathourized()
    {
        // Logout using API web service
        $response = $this->postJson('/api/logout');
        // Check response
        $response->assertStatus(401);
        // Check JSON properties
        $response->assertJson([
            "message" => true, // any value
        ]);
    }

    #[Depends('test_register')]
    public function test_user()
    {
        $user = new User(self::$testUserData);
        Sanctum::actingAs(
            $user,
            ['*'] // grant all abilities to the token
        );
        // Get user data using API web service
        $response = $this->getJson('/api/user');
        // Check response
        $response->assertOk();
        // Check JSON properties
        $response->assertJson([
            "success" => true,
            "user"    => true, // any value
        ]);
        $response->assertJson(
            fn (AssertableJson $json) =>
                $json->where("user.name", $user->name)
                    ->where("user.email", $user->email)
                    ->missing("user.password")
                    ->has("user.role")
                    ->etc()
        );
    }

    public function test_user_unathourized()
    {
        // Get user data using API web service
        $response = $this->getJson('/api/user');
        // Check response
        $response->assertStatus(401);
        // Check JSON properties
        $response->assertJson([
            "message" => true, // any value
        ]);
    }

    protected function _test_token($response)
    {
        // Check JSON properties
        $response->assertJson([
            "success"   => true,
            "authToken" => true, // any value
            "tokenType" => true, // any value
        ]);
        // Check JSON dynamic values
        $response->assertJsonPath("authToken",
            fn ($authToken) => !empty($authToken)
        );
        // Check JSON exact values
        $response->assertJsonPath("tokenType", "Bearer");
    }
}