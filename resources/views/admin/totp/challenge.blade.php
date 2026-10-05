@extends('layouts.admin')
@section('title', 'Verify 2FA')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Enter your authenticator code</h1>
    <form method="POST" action="{{ route('admin.totp.verify') }}" class="mt-6 max-w-md space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        <div><label class="label" for="code">Code</label><input class="field" id="code" name="code" required autocomplete="one-time-code" autofocus></div>
        <button class="btn-primary" type="submit">Continue</button>
    </form>
@endsection