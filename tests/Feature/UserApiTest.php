<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_users_list_with_pagination()
    {
        User::factory()->count(25)->create();

        $response = $this->getJson('/users');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'ip',
                    'comment',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                '*' => [
                    'url',
                    'label',
                    'active',
                ]
            ],
            'next_page_url',
            'prev_page_url',
            'per_page',
            'path',
            'to',
            'total',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
        ]);

        // We check that the response contains pagination data.
        $response->assertJsonFragment([
            'per_page' => 10,
            'current_page' => 1,
        ]);

        // We check that there are 10 users in the response (if pagination returned 10 records)
        $response->assertJsonCount(10, 'data');
    }

    public function test_get_user_by_id()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/users/{$user->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'ip' => $user->ip,
            'comment' => $user->comment,
        ]);
    }

    public function test_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'ip' => '192.168.1.1',
            'comment' => 'This is a comment',
        ];

        $response = $this->postJson('/users', $data);

        $response->assertStatus(201);

        $response->assertJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'ip' => '192.168.1.1',
            'comment' => 'This is a comment',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'ip' => '192.168.1.1',
            'comment' => 'This is a comment',
        ]);

        // Checking that the password has been hashed correctly
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_create_user_validation_error()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Incorrect email
            'password' => 'password123',
        ];

        $response = $this->postJson('/users', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']); // Expecting an error for the email field
    }

    public function test_update_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'ip' => '192.168.1.100',
            'comment' => 'Updated comment',
            'password' => 'newpassword123', // New password
        ];

        $response = $this->putJson("/users/{$user->id}", $data);

        $response->assertStatus(200);

        $response->assertJson([
            'name' => 'Updated Name',
            'ip' => '192.168.1.100',
            'comment' => 'Updated comment',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'ip' => '192.168.1.100',
            'comment' => 'Updated comment',
        ]);

        // Check that the password was updated and properly hashed
        $user = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_update_user_not_found()
    {
        $data = [
            'name' => 'Updated Name',
            'ip' => '192.168.1.100',
            'comment' => 'Updated comment',
        ];

        $response = $this->putJson('/users/99999', $data); // ID that does not exist in the database

        $response->assertStatus(404);

        // Check that the response contains the error message
        $response->assertJson([
            'error' => 'User not found',
        ]);
    }

    public function test_update_user_validation_error()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'ip' => 'not-an-ip', // Invalid IP
        ];

        $response = $this->putJson("/users/{$user->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ip']); // Check for errors on ip fields
    }

    public function test_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/users/{$user->id}");

        $response->assertStatus(200);

        // Check that the response contains the success message
        $response->assertJson([
            'message' => 'User deleted successfully',
        ]);

        // Verify that the user no longer exists in the database
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_delete_user_not_found()
    {
        $response = $this->deleteJson('/users/99999'); // ID that does not exist in the database

        $response->assertStatus(404);

        // Check that the response contains the error message
        $response->assertJson([
            'error' => 'User not found',
        ]);
    }
}
