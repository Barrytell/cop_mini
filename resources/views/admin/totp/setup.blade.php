@extends('layouts.admin')
@section('title', 'Two-factor authentication')
@section('content')
    <h1 class="font-serif text-3xl font-semibold">Admin 2FA (TOTP)</h1>
    <p class="mt-2 text-stone-600">{{ $confirmed ? 'Two-factor authentication is enabled.' : 'Scan the QR code, then confirm with a code from your authenticator app.' }}</p>
    <div class="mt-6 max-w-lg rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        <div class="mx-auto w-48">{!! $qr !!}</div>
        <p class="mt-4 break-all text-sm text-stone-600">Secret: {{ $secret }}</p>
        @unless($confirmed)
            <form method="POST" action="{{ route('admin.totp.confirm') }}" class="mt-4 space-y-3">
                @csrf
                <div><label class="label" for="code">Authenticator code</label><input class="field" id="code" name="code" required autocomplete="one-time-code"></div>
                <button class="btn-primary" type="submit">Confirm and enable</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.totp.disable') }}" class="mt-4 space-y-3">
                @csrf
                <div><label class="label" for="code">Code to disable</label><input class="field" id="code" name="code" required></div>
                <button class="btn-secondary" type="submit">Disable 2FA</button>
            </form>
        @endunless
    </div>
@endsection