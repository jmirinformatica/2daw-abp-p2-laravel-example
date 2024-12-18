<?php

namespace Tests\Feature;

use Tests\ApiTestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Status;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiPostTest extends ApiTestCase
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        self::$validData = [
            "body"   => "My awesome post!",
            "upload" => self::_createValidFakeFile(),
            "status" => Status::PUBLISHED,
        ];
        $data = self::$validData;
        $data["body"] = "";
        $data["upload"] = self::_createInvalidFakeFile();
        self::$invalidData = $data;
    }

    public function test_first()
    {
        $this->_testCreateUser();
    }

    public function test_post_list()
    {
        Sanctum::actingAs(self::$testUser);
        // List all posts using API web service
        $response = $this->getJson("/api/posts");
        // Check OK response
        $this->_test_ok($response);
    }

    public function test_post_create() : object
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $response = $this->postJson("/api/posts", self::$validData );
        // Check OK response
        $this->_test_ok($response, 201);
        // Check validation errors
        $params = array_keys(self::$validData);
        $response->assertValid($params);
        // Check JSON dynamic values
        $response->assertJsonPath("data.id", 
            fn ($id) => !empty($id)
        );
        // Read, update and delete dependency!!!
        $json = $response->getData();
        return $json->data;
    }

    public function test_post_create_error()
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $response = $this->postJson("/api/posts", self::$invalidData );
        // Check ERROR response
        $this->_test_error($response);
        // Check validation errors
        $response->assertInvalid(["body", "upload"]);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_read(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Read one post
        $response = $this->getJson("/api/posts/{$post->id}");
        // Check OK response
        $this->_test_ok($response);
        // Check JSON exact values
        $response->assertJsonPath("data.body", 
            fn ($body) => !empty($body)
        );
    }
    
    public function test_post_read_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->getJson("/api/posts/{$id}");
        $this->_test_notfound($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_update(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $data = self::$validData;
        $data["body"] = "Updating body!";
        $response = $this->putJson("/api/posts/{$post->id}", $data);
        // Check OK response
        $this->_test_ok($response);
        // Check validation errors
        $response->assertValid(["upload"]);
        // Check JSON exact values
        $response->assertJsonPath("data.body", $data["body"]);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_update_error(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $response = $this->putJson("/api/posts/{$post->id}", self::$invalidData);
        // Check ERROR response
        $this->_test_error($response);
        // Check validation errors
        $response->assertInvalid(["body", "upload"]);
    }

    public function test_post_update_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->putJson("/api/posts/{$id}", self::$validData);
        $this->_test_notfound($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_like(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Like fake post using API web service
        $response = $this->postJson("/api/posts/{$post->id}/likes");
        // Check ERROR response
        $this->_test_ok($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_like_again(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Like fake post using API web service
        $response = $this->postJson("/api/posts/{$post->id}/likes");
        // Check ERROR response
        $this->_test_anyerror($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_unlike(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Unlike fake post using API web service
        $response = $this->deleteJson("/api/posts/{$post->id}/likes");
        // Check ERROR response
        $this->_test_ok($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_unlike_again(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Unlike fake post using API web service
        $response = $this->deleteJson("/api/posts/{$post->id}/likes");
        // Check NOT FOUND response
        $this->_test_notfound($response);
    }

    /**
     * @depends test_post_create
     */
    public function test_post_delete(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Delete one post using API web service
        $response = $this->deleteJson("/api/posts/{$post->id}");
        // Check OK response
        $this->_test_ok($response);
    }

    public function test_post_delete_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->deleteJson("/api/posts/{$id}");
        $this->_test_notfound($response);
    }

    public function test_last()
    {
        $this->_testDeleteUser();
    }
}