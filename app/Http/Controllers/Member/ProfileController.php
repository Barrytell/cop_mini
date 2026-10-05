<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdatePasswordRequest;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Modules\Members\Actions\StoreAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('member.profile.edit', [
            'member' => $request->user(),
            'countries' => config('countries'),
        ]);
    }

    public function update(UpdateProfileRequest $request, StoreAvatar $storeAvatar): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->except(['avatar']))->save();

        if ($request->hasFile('avatar')) {
            $storeAvatar->handle($user, $request->file('avatar'));
        }

        return redirect()->route('member.profile.edit')->with('status', 'Profile saved.');
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->string('password')->toString(),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('member.profile.edit')->with('status', 'Password updated.');
    }
}
