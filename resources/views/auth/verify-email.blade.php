@extends('layouts.member')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900">Confirm your email</h1>
    <p class="mt-2 max-w-xl text-stone-700">Open the link we sent to {{ auth()->user()->email }}. You can keep going to payment, and you can request another message if it did not arrive.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button type="submit" class="btn-primary">Resend verification email</button>
    </form>
@endsection
