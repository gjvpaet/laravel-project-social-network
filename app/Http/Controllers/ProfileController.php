<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function index($slug) {
        $user = User::where('slug', $slug)->first();
        return view('profiles.profile')->withUser($user);
    }

    public function edit() {
        return view('profiles.edit')->withInfo(Auth::user()->profile);
    }

    public function update(Request $request) {
        $this->validate($request, [
            'location' => 'required',
            'about' => 'required|max:255'
        ]);

        Auth::user()->profile()->update([
            'location' => $request->location,
            'about' => $request->about
        ]);

        if ($request->hasFile('avatar')) {
            Auth::user()->update([
                'avatar' => $request->avatar->store('public/avatars')
            ]);
        }

        Session::flash('success', 'Profile updated.');

        return redirect()->back();
    }
}
