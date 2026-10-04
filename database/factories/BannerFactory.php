<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Cms\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Own a share of real assets',
            'subtitle' => 'Pool funds with other members for real estate, gold, oil, and other stable holdings.',
            'image_path' => null,
            'link_url' => null,
            'position' => 'home_hero',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
