<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\Post;
use App\Modules\Cms\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = null;
        $slug = $request->query('category');

        $posts = Post::query()->published()->with('category')->latest('published_at');

        if (is_string($slug) && $slug !== '') {
            $category = PostCategory::query()->where('slug', $slug)->firstOrFail();
            $posts->where('post_category_id', $category->id);
        }

        return view('news.index', [
            'posts' => $posts->paginate(9)->withQueryString(),
            'categories' => PostCategory::query()->orderBy('name')->get(),
            'current' => $category,
        ]);
    }

    public function show(Post $post): View
    {
        $post->load('category');

        return view('news.show', [
            'post' => $post,
            'related' => Post::query()->published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
