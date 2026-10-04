@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-10">
        <h1 class="font-serif text-4xl font-semibold text-forest-900">Reset your password</h1>
        <p class="mt-2 text-stone-700">We will email a reset link if the address belongs to an account.</p>
        @include('layouts.partials.flash')
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <button type="submit" class="btn-primary w-full">Email reset link</button>
        </form>
    </div>
@endsection
