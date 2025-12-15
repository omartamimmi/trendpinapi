<?php

namespace Modules\Media\Tests\Feature;

use Tests\RefreshTestDatabaseTrait;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\User\Tests\ContextBuilder\BasicContextBuilder;
use Illuminate\Testing\Fluent\AssertableJson;


class UploadMediaFilesTest extends TestCase
{
    use RefreshTestDatabaseTrait;
    use WithFaker;

    private string $mediaUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileStoreUrl = route('v1.file.store');
    }

    private function mediaUploadUrl()
    {
        return route('v1.file.store');
    }

    public function file()
    {
        return[
            'file' => UploadedFile::fake()->image('avatar.jpg')
        ];
    }

    public function test_media_upload_file()
    {
        $contextBuilder = new BasicContextBuilder();
        $contextBuilder
            ->setupCustomerRole()
            ->createCustomerUser($customer)
            ->createCuratorRequest($customer);

        Storage::fake('avatars');
        
        $response = $this->actingAs($customer)->postJson($this->mediaUploadUrl(),$this->file(), $this->getHeaders());
        $response->assertStatus(200);
    }

    public function test_media_upload_height_width()
    {
        $contextBuilder = new BasicContextBuilder();
        $contextBuilder
            ->setupCustomerRole()
            ->createCustomerUser($customer)
            ->createCuratorRequest($customer);

        Storage::fake('avatars');

        $data = [
            'file' => UploadedFile::fake()->image('avatar.jpg', 5000, 50000)->size(100)
        ];

        $response = $this->actingAs($customer)->postJson($this->mediaUploadUrl(),$data, $this->getHeaders());
        $response->assertStatus(422);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('errors')
                // ->has('errors.dimensions.min_height')
                ->etc();
        });
    }

    public function test_media_upload_empty_file()
    {
        $contextBuilder = new BasicContextBuilder();
        $contextBuilder
            ->setupCustomerRole()
            ->createCustomerUser($customer)
            ->createCuratorRequest($customer);

        Storage::fake('avatars');

        $data = [
            'file' =>''
        ];

        $response = $this->actingAs($customer)->postJson($this->mediaUploadUrl(),$data, $this->getHeaders());
        $response->assertStatus(422);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('errors')
                ->has('errors.file')
                ->etc();
        });
    }


}
