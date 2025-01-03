<?php

namespace Tests\Feature\Api;

use Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\Depends;
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
        // Valid data
        self::$validData = [
            "title"     => "Super title",
            "body"      => "My awesome post!",
            "status_id" => Status::PUBLISHED,
        ];
        // Invalid data
        $data = self::$validData;
        $data["body"] = "";
        $data["status_id"] = 5;
        self::$invalidData = $data;
    }

    public function test_post_list()
    {
        // List all posts using API web service
        $response = $this->getJson("/api/posts");
        // Check OK response
        $this->_test_ok($response);
        // Check JSON
        $response->assertJson(fn (AssertableJson $json) =>
            $json->whereAllType([
                'success' => 'boolean',
                'data.0.id' => 'integer',
                'data.0.title' => 'string',
                'data.0.body' => 'string',
                'data.0.author.name' => 'string',
                'data.0.status.name' => 'string',
            ])
        );
    }

    public function test_create_post_author()
    {
        // Create user once
        $this->_testCreateUser();
    }

    public function test_post_create() : object
    {
        // Create fake post using API web service
        Sanctum::actingAs(self::$testUser);
        $response = $this->postJson("/api/posts", self::$validData);
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
        // Create fake post using API web service
        $response = $this->postJson("/api/posts", self::$invalidData );
        // Check ERROR response
        $this->_test_validation_error($response);
        // Check validation errors
        $response->assertInvalid(["body", "status_id"]);
    }

    #[Depends('test_post_create')]
    public function test_post_read(object $post)
    {
        // Read one post
        $response = $this->getJson("/api/posts/{$post->id}");
        // Check OK response
        $this->_test_ok($response);
        // Check JSON exact values
        $response->assertJson(fn (AssertableJson $json) =>
            $json->whereAllType([
                'success' => 'boolean',
                'data.id' => 'integer',
                'data.title' => 'string',
                'data.body' => 'string',
                'data.author.name' => 'string',
                'data.status.name' => 'string',
            ])
        );
    }
    
    public function test_post_read_notfound()
    {
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->getJson("/api/posts/{$id}");
        $this->_test_notfound($response);
    }

    #[Depends('test_post_create')]
    public function test_post_update(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Update fake post using API web service
        $data = self::$validData;
        $data["body"] = "Updating body!";
        $data["status_id"] = Status::DRAFT;
        $response = $this->putJson("/api/posts/{$post->id}", $data);
        // Check OK response
        $this->_test_ok($response);
        // Check validation errors
        $response->assertValid(["body","status_id"]);
        // Check JSON exact values
        $response->assertJsonPath("data.body", $data["body"]);
        $response->assertJsonPath("data.status.id", $data["status_id"]);
    }

    #[Depends('test_post_create')]
    public function test_post_update_error(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Update fake post using API web service
        $response = $this->putJson("/api/posts/{$post->id}", self::$invalidData);
        // Check ERROR response
        $this->_test_validation_error($response);
        // Check validation errors
        $response->assertInvalid(["status_id"]);
    }
    
    public function test_post_update_notfound()
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->putJson("/api/posts/{$id}", self::$validData);
        $this->_test_notfound($response);
    }

    /*****************************************************************/

    #[Depends('test_post_create')]
    public function test_comment_create(object $post) : object
    {
        Sanctum::actingAs(self::$testUser);
        // Create fake comment using API web service
        $validData = [
            "comment" => "My comment",
        ];
        $response = $this->postJson("/api/posts/{$post->id}/comments", $validData);
        // Check OK response
        $this->_test_ok($response, 201);
        // Check validation errors
        $params = array_keys($validData);
        $response->assertValid($params);
        // Check JSON dynamic values
        $response->assertJsonPath("data.id", 
            fn ($id) => !empty($id)
        );
        // Read, update and delete dependency!!!
        $json = $response->getData();
        return $json->data;
    }

    #[Depends('test_post_create')]
    public function test_comment_create_error(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // Create fake comment using API web service
        $invalidData =  [
            "comment" => "",
        ];
        $response = $this->postJson("/api/posts/{$post->id}/comments", $invalidData);
        // Check ERROR response
        $this->_test_validation_error($response);
        // Check validation errors
        $response->assertInvalid(["comment"]);
    }

    #[Depends('test_post_create')]
    public function test_comment_list(object $post)
    {
        Sanctum::actingAs(self::$testUser);
        // List all posts using API web service
        $response = $this->getJson("/api/posts/{$post->id}/comments");
        // Check OK response
        $this->_test_ok($response);
        // Check JSON
        $response->assertJson(fn (AssertableJson $json) =>
        $json->whereAllType([
            'success' => 'boolean',
            'data.0.id' => 'integer',
            'data.0.comment' => 'string',
            'data.0.post.title' => 'string',
            'data.0.user.name' => 'string',
        ])
    );
    }

    #[Depends('test_comment_create')]
    public function test_comment_delete(object $comment)
    {
        Sanctum::actingAs(self::$testUser);
        // Delete one post using API web service
        $response = $this->deleteJson("/api/posts/{$comment->post->id}/comments/{$comment->id}");
        // Check OK response
        $this->_test_ok($response);
    }

    #[Depends('test_comment_create')]
    public function test_comment_delete_notfound(object $comment)
    {
        Sanctum::actingAs(self::$testUser);
        // Check NOT FOUND scenario
        $id = "not_exists";
        $response = $this->deleteJson("/api/posts/{$comment->post->id}/comments/{$id}");
        $this->_test_notfound($response);
    }

    /*****************************************************************/

    #[Depends('test_post_create')]
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

    public function test_delete_post_author()
    {
        // Delete user at the end
        $this->_testDeleteUser();
    }
}