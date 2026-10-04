@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-10">
        <h1 class="font-serif text-4xl font-semibold text-forest-900">Choose a new password</h1>
        @include('layouts.partials.flash')
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="label" for="email">Email</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>
            <div>
                <label class="label" for="password">New password</label>
                <input class="field" id="password" name="password" type="password" required autocomplete="new-password">
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input class="field" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn-primary w-full">Update password</button>
        </form>
    </div>
@endsection
