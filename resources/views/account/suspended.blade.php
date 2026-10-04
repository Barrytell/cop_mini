@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-16">
        <h1 class="font-serif text-4xl font-semibold text-forest-900">Account suspended</h1>
        <p class="mt-3 text-stone-700">This membership cannot sign in or move money. Write to <a class="font-semibold text-forest-800" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> if you think this is a mistake.</p>
        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="btn-primary">Log out</button>
            </form>
        @endauth
    </div>
@endsection
