<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use App\Models\User;

abstract class ApiTestCase extends TestCase
{
    public static User $testUser;
    public static array $validData = [];
    public static array $invalidData = [];
    
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        // Create test user (BD store later)
        $name = "test_" . time();
        self::$testUser = new User([
            "name"      => "{$name}",
            "email"     => "{$name}@mailinator.com",
            "password"  => "12345678"
        ]);
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        
        // TODO Delete user after test...
        // $user = User::where('email', self::$testUser->email)->first();
        // $user->delete();
    }

    protected static function _createValidFakeFile()
    {
        return UploadedFile::fake()
            ->image("valid.png")
            ->size(500 /*KB*/);
    }
    
    protected static function _createInvalidFakeFile()
    {
        return UploadedFile::fake()
            ->image("invalid.png")
            ->size(5000 /*KB*/); // Invalid size
    }

    protected function _test_ok($response, $status = 200)
    {
        // Check JSON response
        $response->assertStatus($status);
        // Check JSON properties
        $response->assertJson([
            "success" => true,
            "data"    => true, // any value
        ]);
        // Check JSON dynamic values
        $response->assertJsonPath("data", 
            fn ($data) => is_array($data)
        );
    }

    protected function _test_error($response)
    {
        // Check response
        $response->assertStatus(422);
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
    
    protected function _test_notfound($response)
    {
        $this->_test_anyerror($response, 404);
    }
    
    protected function _test_anyerror($response, $status=500)
    {
        // Check JSON response
        $response->assertStatus($status);
        // Check JSON properties
        $response->assertJson([
            "success" => false,
            "message" => true // any value
        ]);
        // Check JSON dynamic values
        $response->assertJsonPath("message", 
            fn ($message) => !empty($message) && is_string($message)
        );
    }

    protected function _testCreateUser()
    {
        // Create user (DB)
        self::$testUser->save();
        // Check user exists
        $this->assertDatabaseHas('users', [
            'email' => self::$testUser->email,
        ]);
    }

    public function _testDeleteUser()
    {
        // Delete user (DB)
        self::$testUser->delete();
        // Check user not exists
        $this->assertDatabaseMissing('users', [
            'email' => self::$testUser->email,
        ]);
    }
}