@extends('layouts.public')

@section('title', 'Log in')
@section('robots', 'noindex, follow')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-10">
        <h1 class="font-serif text-4xl font-semibold text-navy-900">Log in</h1>
        @include('layouts.partials.flash')
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input class="field" id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="flex min-h-11 items-center gap-3 text-sm font-semibold">
                <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-stone-300" @checked(old('remember'))>
                Remember me
            </label>
            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>
        <p class="mt-4 text-sm"><a class="inline-flex min-h-11 items-center font-semibold text-forest-800" href="{{ route('password.request') }}">Forgot your password?</a></p>
        <p class="mt-2 text-sm">New here? <a class="inline-flex min-h-11 items-center font-semibold text-forest-800" href="{{ route('register') }}">Create an account</a></p>
    </div>
@endsection
