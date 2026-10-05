@extends('layouts.admin')
@section('title', 'Edit member')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Edit {{ $member->name }}</h1>
    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="mt-6 max-w-xl space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <div>
            <label class="label" for="name">Name</label>
            <input class="field" id="name" name="name" value="{{ old('name', $member->name) }}" required>
        </div>
        <div>
            <label class="label" for="email">Email</label>
            <input class="field" id="email" name="email" type="email" value="{{ old('email', $member->email) }}" required>
        </div>
        <div>
            <label class="label" for="phone">Phone</label>
            <input class="field" id="phone" name="phone" value="{{ old('phone', $member->phone) }}" required>
        </div>
        <div>
            <label class="label" for="country">Country</label>
            <select class="field" id="country" name="country" required>
                @foreach ($countries as $country)
                    <option value="{{ $country }}" @selected(old('country', $member->country) === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="status">Status</label>
            <select class="field" id="status" name="status" required>
                @foreach (['pending','active','suspended'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $member->status->value) === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary" type="submit">Save</button>
    </form>
@endsection