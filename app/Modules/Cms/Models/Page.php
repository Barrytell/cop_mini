<?php

declare(strict_types=1);

namespace App\Modules\Cms\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'template',
        'excerpt',
        'body',
        'is_published',
        'show_in_nav',
        'nav_group',
        'sort_order',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_nav' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeInNav(Builder $query): Builder
    {
        return $query->published()->where('show_in_nav', true)->orderBy('sort_order')->orderBy('title');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->published()
            ->first();
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
