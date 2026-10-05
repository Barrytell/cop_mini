@extends('layouts.admin')
@section('title', $title)
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-serif text-3xl font-semibold">{{ $title }}</h1>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                @foreach (['posts','categories','faqs','testimonials','team','gallery','downloads','menus'] as $cmsType)
                    <a class="rounded-full px-3 py-2 ring-1 ring-stone-300 {{ $type === $cmsType ? 'bg-forest-800 text-white' : 'bg-white' }}" href="{{ route('admin.cms.content.index', $cmsType) }}">{{ $cmsType }}</a>
                @endforeach
            </div>
        </div>
        <a class="btn-primary" href="{{ route('admin.cms.content.create', $type) }}">Add</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-3xl bg-white ring-1 ring-stone-200">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-stone-200 text-stone-600">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-4 py-3">{{ $column }}</th>
                    @endforeach
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-b border-stone-100">
                        @foreach ($columns as $column)
                            <td class="px-4 py-3">{{ is_bool($item->{$column} ?? null) ? (($item->{$column} ?? false) ? 'yes' : 'no') : ($item->{$column} ?? '') }}</td>
                        @endforeach
                        <td class="px-4 py-3"><a class="font-semibold text-forest-800" href="{{ route('admin.cms.content.edit', [$type, $item->id]) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 1 }}" class="px-4 py-8 text-stone-600">No items.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
@endsection