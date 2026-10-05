@extends('layouts.member')

@section('title', 'Profile')

@section('content')
    <h1 class="font-serif text-3xl font-semibold text-forest-900 sm:text-4xl">Profile</h1>
    <p class="mt-2 text-stone-700">{{ $member->email }} · {{ str_starts_with((string) $member->member_no, 'TMP-') ? 'Member number issued after your first payment' : $member->member_no }}</p>

    <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        @if ($member->avatar_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($member->avatar_path) }}" alt="" class="h-16 w-16 rounded-full object-cover" width="64" height="64">
        @endif
        <div>
            <label class="label" for="avatar">Photo</label>
            <input class="field" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
            @error('avatar')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="name">Name</label>
            <input class="field" id="name" name="name" value="{{ old('name', $member->name) }}" required>
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
        <fieldset class="space-y-4">
            <legend class="font-serif text-2xl">Next of kin</legend>
            <div>
                <label class="label" for="kin_name">Name</label>
                <input class="field" id="kin_name" name="kin_name" value="{{ old('kin_name', $member->kin_name) }}" autocomplete="off">
            </div>
            <div>
                <label class="label" for="kin_relationship">Relationship</label>
                <input class="field" id="kin_relationship" name="kin_relationship" value="{{ old('kin_relationship', $member->kin_relationship) }}">
            </div>
            <div>
                <label class="label" for="kin_phone">Phone</label>
                <input class="field" id="kin_phone" name="kin_phone" value="{{ old('kin_phone', $member->kin_phone) }}" autocomplete="off">
            </div>
        </fieldset>
        <fieldset class="space-y-4">
            <legend class="font-serif text-2xl">Payout details</legend>
            <p class="text-sm text-stone-600">Stored encrypted. Used only if the cooperative pays a member distribution.</p>
            <div>
                <label class="label" for="payout_bank_name">Bank</label>
                <input class="field" id="payout_bank_name" name="payout_bank_name" value="{{ old('payout_bank_name', $member->payout_bank_name) }}" autocomplete="off">
            </div>
            <div>
                <label class="label" for="payout_account_name">Account name</label>
                <input class="field" id="payout_account_name" name="payout_account_name" value="{{ old('payout_account_name', $member->payout_account_name) }}" autocomplete="off">
            </div>
            <div>
                <label class="label" for="payout_account_number">Account number</label>
                <input class="field" id="payout_account_number" name="payout_account_number" value="{{ old('payout_account_number', $member->payout_account_number) }}" autocomplete="off" inputmode="numeric">
            </div>
            <div>
                <label class="label" for="payout_routing_code">Routing or sort code</label>
                <input class="field" id="payout_routing_code" name="payout_routing_code" value="{{ old('payout_routing_code', $member->payout_routing_code) }}" autocomplete="off">
            </div>
        </fieldset>
        <fieldset class="space-y-2">
            <legend class="font-serif text-2xl">Email notifications</legend>
            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="notify_payments" value="1" @checked(old('notify_payments', $member->notify_payments))> Payment receipts and referral bonuses</label>
            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="notify_announcements" value="1" @checked(old('notify_announcements', $member->notify_announcements))> Announcements</label>
            <label class="flex min-h-11 items-center gap-3"><input type="checkbox" name="notify_meetings" value="1" @checked(old('notify_meetings', $member->notify_meetings))> Meetings</label>
        </fieldset>
        <button type="submit" class="btn-primary">Save profile</button>
    </form>

    <form method="POST" action="{{ route('member.profile.password') }}" class="mt-6 space-y-4 rounded-3xl bg-white p-5 ring-1 ring-stone-200">
        @csrf
        @method('PUT')
        <h2 class="font-serif text-2xl">Change password</h2>
        <div>
            <label class="label" for="current_password">Current password</label>
            <input class="field" id="current_password" name="current_password" type="password" required autocomplete="current-password">
        </div>
        <div>
            <label class="label" for="password">New password</label>
            <input class="field" id="password" name="password" type="password" required autocomplete="new-password">
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirm new password</label>
            <input class="field" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn-primary">Update password</button>
    </form>
@endsection
