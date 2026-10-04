<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by' => User::factory()->admin(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'is_published' => true,
            'published_at' => now(),
        ];
    }
}
