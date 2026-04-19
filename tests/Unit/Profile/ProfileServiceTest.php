<?php

namespace Tests\Unit\Profile;

use App\Models\User;
use App\Models\UserPhoto;
use App\Services\Profile\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfileService();
    }

    public function test_it_returns_user_profile()
    {
        $user = User::factory()->create();

        $result = $this->service->getProfile($user);

        $this->assertEquals($user->id, $result->id);
    }

    public function test_it_returns_profile_photos_without_excluded_id()
    {
        UserPhoto::factory()->create(['id' => 10]);
        UserPhoto::factory()->create(['id' => 11]); // should be excluded
        UserPhoto::factory()->create(['id' => 12]);

        $photos = $this->service->getProfilePhotos();

        $this->assertCount(2, $photos);

        $ids = collect($photos)->pluck('id');

        $this->assertFalse($ids->contains(11));
    }

    public function test_it_updates_profile_photo_successfully()
    {
        $user = User::factory()->create();

        $photo = UserPhoto::factory()->create([
            'photo_url' => 'storage/photos/test.png'
        ]);

        $updatedUser = $this->service->updateProfilePhoto($user, $photo->id);

        $this->assertEquals($photo->photo_url, $updatedUser->profile_photo);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'profile_photo' => $photo->photo_url
        ]);
    }

    public function test_it_updates_profile_basic_fields()
    {
        $user = User::factory()->create();

        $data = [
            'first_name' => 'New',
            'last_name' => 'Name',
            'location' => 'Amsterdam',
        ];

        $this->service->updateProfile($user, $data);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New',
            'last_name' => 'Name',
            'location' => 'Amsterdam',
        ]);
    }

    public function test_it_updates_password_when_correct_current_password_provided()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $data = [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
        ];

        $this->service->updateProfile($user, $data);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_it_throws_exception_when_current_password_is_wrong()
    {
        $this->expectException(\Exception::class);

        $user = User::factory()->create([
            'password' => Hash::make('correct-password')
        ]);

        $data = [
            'current_password' => 'wrong-password',
            'new_password' => 'newpassword',
        ];

        $this->service->updateProfile($user, $data);
    }
}