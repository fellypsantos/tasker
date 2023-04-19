<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_log_in_with_correct_credentials()
    {
        $user = User::factory()->create(['email' => 'test@user.com', 'password' => Hash::make('my-password')]);

        $response = $this->postJson('login', ['email' => $user->email, 'password' => 'my-password']);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(2);

        $response->assertJson(function (AssertableJson $json) use ($user) {

            $json->hasAll(['token', 'user']);

            $json
                ->where('user.name', $user->name)
                ->where('user.email', $user->email);
        });
    }

    public function test_user_can_access_restricted_route_when_logged_in()
    {
        $user = User::factory()->create(['email' => 'test@user.com', 'password' => Hash::make('my-password')])->first();

        $response = $this->actingAs($user)->getJson('me');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_cannot_log_in_with_wrong_credentials()
    {
        $user = User::factory()->create(['email' => 'fellyp@user.com', 'password' => Hash::make('my-password')]);

        $response = $this->postJson('login', ['email' => $user->email, 'password' => 'wrong-password']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Invalid credentials.');
        });
    }

    public function test_user_cannot_log_in_when_email_is_invalid()
    {
        $user = User::factory()->create(['email' => 'fellyp.com', 'password' => Hash::make('123456')]);

        $response = $this->postJson('login', ['email' => $user->email, 'password' => '123456']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonCount(2);

        $response->assertJson(function (AssertableJson $json) {

            $json->hasAll(['message', 'errors']);

            $json->whereAll([
                'message' => 'The given data was invalid.',
                'errors.email.0' => 'The email must be a valid email address.'
            ]);
        });
    }

    public function test_user_cannot_log_in_when_password_is_less_than_6_chars()
    {
        $user = User::factory()->create(['email' => 'fellyp@user.com', 'password' => Hash::make('123')]);

        $response = $this->postJson('login', ['email' => $user->email, 'password' => '123']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonCount(2);

        $response->assertJson(function (AssertableJson $json) {

            $json->hasAll(['message', 'errors']);

            $json->whereAll([
                'message' => 'The given data was invalid.',
                'errors.password.0' => 'The password must be at least 6 characters.'
            ]);
        });
    }

    public function test_user_should_get_error_429_too_many_requets_when_fire_many_attemps_to_login()
    {
        $credentials = ['email' => 'flood@user.com', 'password' => 'dummypass123'];

        for ($i = 0; $i < 80; $i++) {
            $this->postJson('login', $credentials);
        }

        $response = $this->postJson('login', $credentials);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }
}
