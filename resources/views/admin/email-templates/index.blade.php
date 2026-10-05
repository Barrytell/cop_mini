@extends('layouts.admin')
@section('title', 'Email templates')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Email templates</h1>
    <div class="mt-6 space-y-3">
        @forelse ($templates as $template)
            <a href="{{ route('admin.email-templates.edit', $template) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $template->name }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $template->key }} · {{ $template->is_active ? 'active' : 'inactive' }}</p>
            </a>
        @empty
            <p class="text-stone-600">No templates. Seed the database to add defaults.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $templates->links() }}</div>
@endsection