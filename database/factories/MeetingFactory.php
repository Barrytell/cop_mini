<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $starts = now()->addDays(7)->startOfHour();

        return [
            'created_by' => User::factory()->admin(),
            'title' => 'Members\' circle',
            'description' => fake()->paragraph(),
            'location' => 'Online',
            'meeting_url' => 'https://meet.minimini.org/circle',
            'starts_at' => $starts,
            'ends_at' => $starts->copy()->addHour(),
            'status' => MeetingStatus::Scheduled,
        ];
    }
}
