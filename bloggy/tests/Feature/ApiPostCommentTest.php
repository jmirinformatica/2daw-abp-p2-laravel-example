<?php

namespace Tests\Feature;

use Tests\ApiTestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiPostCommentTest extends ApiTestCase
{
    public static int $testPostId;
    
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        self::$validData = [
            "comment" => "My comment",
        ];
        self::$invalidData =  [
            "comment" => "",
        ];
    }

    public function test_first()
    {
        // Create fake author
        $this->_testCreateUser();
        // Create fake post
        Sanctum::actingAs(self::$testUser);
        $response = $this->postJson("/api/posts", [
            "body"   => "My awesome post!",
            "upload" => self::_createValidFakeFile(),
            "status" => Status::PUBLISHED,
        ]);
        $data = $response->getData()->data;
        self::$testPostId = $data->id;
    }
    
    public function test_comment_list()
    {
        Sanctum::actingAs(self::$testUser);
        // List all posts using API web service
        $pid = self::$testPostId;
        $response = $this->getJson("/api/posts/{$pid}/comments");
        // Check OK response
        $this->_test_ok($response);
    }

    public function test_comment_create() : object
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $pid = self::$testPostId;
        $response = $this->postJson("/api/posts/{$pid}/comments", self::$validData);
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

    public function test_comment_create_error()
    {
        Sanctum::actingAs(self::$testUser);
        // Upload fake post using API web service
        $pid = self::$testPostId;
        $response = $this->postJson("/api/posts/{$pid}/comments", self::$invalidData);
        // Check ERROR response
        $this->_test_error($response);
        // Check validation errors
        $response->assertInvalid(["comment"]);
    }

    /**
     * @depends test_comment_create
     */
    public function test_comment_read(object $comment)
    {
        Sanctum::actingAs(self::$testUser);
        // Read one post
        $pid = self::$testPostId;
        $response = $this->getJson("/api/posts/{$pid}/comments/{$comment->id}");
        // Check OK response
        $this->_test_ok($response);
        // Check JSON exact values
        $response->assertJsonPath("data.comment", 
            fn ($comment) => !empty($comment)
        );
    }
    
    public function test_comment_read_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $pid = self::$testPostId;
        $response = $this->getJson("/api/posts/{$pid}/comments/{$id}");
        $this->_test_notfound($response);
    }

    /**
     * @depends test_comment_create
     */
    public function test_comment_delete(object $comment)
    {
        Sanctum::actingAs(self::$testUser);
        // Delete one post using API web service
        $pid = self::$testPostId;
        $response = $this->deleteJson("/api/posts/{$pid}/comments/{$comment->id}");
        // Check OK response
        $this->_test_ok($response);
    }

    public function test_comment_delete_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $pid = self::$testPostId;
        $response = $this->deleteJson("/api/posts/{$pid}/comments/{$id}");
        $this->_test_notfound($response);
    }
    
    public function test_last()
    {
        // Delete fake post
        Post::destroy(self::$testPostId);
        // Delete fake author
        $this->_testDeleteUser();
    }
}