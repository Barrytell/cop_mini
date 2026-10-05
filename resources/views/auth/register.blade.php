@extends('layouts.public')

@section('title', 'Create an account')
@section('robots', 'noindex, follow')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-10">
        <h1 class="font-serif text-4xl font-semibold text-navy-900">Create your account</h1>
        <p class="mt-2 text-stone-700">You will be pending until your first payment is confirmed.</p>
        @if (! empty($referrerName))
            <p class="mt-4 rounded-2xl bg-gold-100 px-4 py-3 text-sm font-semibold text-navy-900">Referred by {{ $referrerName }}</p>
        @endif
        @include('layouts.partials.flash')
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
            @csrf
            <div aria-hidden="true" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">
                <label for="website">Website</label>
                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off" style="width:1px;height:1px">
            </div>
            <div>
                <label class="label" for="name">Full name</label>
                <input class="field" id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
            </div>
            <div>
                <label class="label" for="phone">Phone</label>
                <input class="field" id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel" placeholder="+15551234567">
            </div>
            <div>
                <label class="label" for="country">Country</label>
                <select class="field" id="country" name="country" required>
                    <option value="">Select a country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected(old('country') === $country)>{{ $country }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input class="field" id="password" name="password" type="password" required autocomplete="new-password" minlength="8">
                <p class="mt-1 text-sm text-stone-600">At least 8 characters, with upper and lower case letters and a number.</p>
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input class="field" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>
            <div>
                <label class="label" for="referral_code">Referral code <span class="font-normal text-stone-500">(optional)</span></label>
                <input class="field uppercase" id="referral_code" name="referral_code" type="text" value="{{ old('referral_code', $referralCode) }}" maxlength="16" autocomplete="off">
            </div>
            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
        <p class="mt-4 text-sm">Already registered? <a class="inline-flex min-h-11 items-center font-semibold text-forest-800" href="{{ route('login') }}">Log in</a></p>
    </div>
@endsection
