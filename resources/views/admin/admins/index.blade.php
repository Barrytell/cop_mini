@extends('layouts.admin')
@section('title', 'Admins')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-serif text-3xl font-semibold">Admin users</h1>
        @if(auth()->user()?->role->value === 'super_admin')
            <a class="btn-primary" href="{{ route('admin.admins.create') }}">Create admin</a>
        @endif
    </div>
    <div class="mt-6 space-y-3">
        @foreach ($admins as $admin)
            <a href="{{ route('admin.admins.edit', $admin) }}" class="block rounded-2xl bg-white p-4 ring-1 ring-stone-200">
                <p class="font-semibold">{{ $admin->name }}</p>
                <p class="mt-1 break-all text-sm text-stone-600">{{ $admin->email }} · {{ $admin->role->value }} · {{ $admin->status->value }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $admins->links() }}</div>
@endsection