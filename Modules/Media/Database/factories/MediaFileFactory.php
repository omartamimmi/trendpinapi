<?php

namespace Modules\Media\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Media\Models\MediaFile;

class MediaFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'file_name'=>fake()->name().'.'.fake()->fileExtension(),
            'file_path'=>fake()->filePath(),
            'file_size'=>fake()->numberBetween(20000,50000),
            'file_type'=>fake()->mimeType(),
            'file_extension'=>fake()->fileExtension(),
            'file_width'=>fake()->numberBetween(20000,50000),
            'file_height'=>fake()->numberBetween(20000,50000),
            'create_user' => 1
        ];
    }
}

