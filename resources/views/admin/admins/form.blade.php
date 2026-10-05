@extends('layouts.admin')
@section('title', $admin->exists ? 'Edit admin' : 'Create admin')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">{{ $admin->exists ? 'Edit' : 'Create' }} admin</h1>
    <form method="POST" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}" class="mt-6 max-w-2xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @if($admin->exists) @method('PUT') @endif
        <div><label class="label" for="name">Name</label><input class="field" id="name" name="name" value="{{ old('name', $admin->name) }}" required></div>
        <div><label class="label" for="email">Email</label><input class="field" id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required></div>
        <div><label class="label" for="phone">Phone</label><input class="field" id="phone" name="phone" value="{{ old('phone', $admin->phone) }}" required></div>
        <div><label class="label" for="country">Country</label><input class="field" id="country" name="country" value="{{ old('country', $admin->country ?: 'Nigeria') }}" required></div>
        <div>
            <label class="label" for="role">Role</label>
            <select class="field" id="role" name="role" required>
                <option value="admin" @selected(old('role', $admin->role?->value) === 'admin')>admin</option>
                <option value="super_admin" @selected(old('role', $admin->role?->value) === 'super_admin')>super_admin</option>
            </select>
        </div>
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status" required>
                @foreach (['active','pending','suspended'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $admin->status?->value ?? 'active') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <fieldset>
            <legend class="font-semibold">Permissions</legend>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($labels as $key => $label)
                    <label class="inline-flex min-h-11 items-center gap-2"><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, old('permissions', $admin->permissions ?? []), true))> {{ $label }}</label>
                @endforeach
            </div>
        </fieldset>
        <div><label class="label" for="password">Password{{ $admin->exists ? ' (optional)' : '' }}</label><input class="field" id="password" name="password" type="password" @unless($admin->exists) required @endunless></div>
        <div><label class="label" for="password_confirmation">Confirm password</label><input class="field" id="password_confirmation" name="password_confirmation" type="password" @unless($admin->exists) required @endunless></div>
        <button class="btn-primary" type="submit">Save</button>
    </form>
@endsection