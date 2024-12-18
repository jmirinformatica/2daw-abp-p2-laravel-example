<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class ApiStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_list(): void
    {
        // List all statuses using API web service
        $response = $this->getJson("/api/statuses");

        // Check response
        $response->assertOk();

        // Check JSON properties
        $response->assertJson([
            "success" => true,
            "data"    => true, // any value
        ]);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->whereAllType([
                'success' => 'boolean',
                'data.0.id' => 'integer',
                'data.0.name' => 'string',
            ])
        );
    }
}
