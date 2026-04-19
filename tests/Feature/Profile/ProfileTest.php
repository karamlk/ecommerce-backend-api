<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_it_returns_authenticated_user_profile()
    {
        $user = $this->authenticate();

        $response = $this->getJson('/api/profile');
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'email' => $user->email,
                ]
            ]);
    }

    public function test_it_returns_profile_photos_without_excluded_id()
    {
        $this->authenticate();

        UserPhoto::factory()->create(['id' => 10]);
        UserPhoto::factory()->create(['id' => 11]); // excluded
        UserPhoto::factory()->create(['id' => 12]);

        $response = $this->getJson('/api/profile/photos');

        $response->assertStatus(200);

        $photos = $response->json('photos');

        $this->assertCount(2, $photos);

        $ids = collect($photos)->pluck('id');
        $this->assertFalse($ids->contains(11));
    }

    public function test_it_updates_profile_photo_successfully()
    {
        $user = $this->authenticate();

        $photo = UserPhoto::factory()->create([
            'photo_url' => 'storage/photos/test.png'
        ]);

        $response = $this->putJson('/api/profile/photo', [
            'photo_id' => $photo->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'profile_photo' => $photo->photo_url
        ]);
    }

    public function test_it_validates_photo_id_on_update_profile_photo()
    {
        $this->authenticate();

        $response = $this->putJson('/api/profile/photo', [
            'photo_id' => 999 // doesn't exist
        ]);

        $response->assertStatus(422);
    }

    public function test_it_updates_profile_basic_fields()
    {
        $user = $this->authenticate();

        $response = $this->putJson('/api/profile', [
            'first_name' => 'New',
            'last_name' => 'Name',
            'location' => 'Amsterdam',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New',
            'last_name' => 'Name',
            'location' => 'Amsterdam',
        ]);
    }

    public function test_it_updates_password_successfully()
    {
        $user = $this->authenticate();

        $user->update([
            'password' => Hash::make('oldpassword')
        ]);

        $response = $this->putJson('/api/profile', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(
            Hash::check('newpassword', $user->fresh()->password)
        );
    }

    public function test_it_fails_when_current_password_is_wrong()
    {
        $user = $this->authenticate();

        $user->update([
            'password' => Hash::make('correct-password')
        ]);

        $response = $this->putJson('/api/profile', [
            'current_password' => 'wrong-password',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'The current password is incorrect.'
            ]);
    }

    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
