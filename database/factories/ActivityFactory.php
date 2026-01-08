<?php
namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word,
            'type' => 'run',
            'note' => $this->faker->sentence,
            'photo_path' => null,
            'distance' => $this->faker->numberBetween(1000, 10000),
            'time' => $this->faker->numberBetween(600, 7200),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
