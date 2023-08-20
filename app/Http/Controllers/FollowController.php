<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        if ($user->id == auth()->user()->id) {
            return back()->with('error', 'You cannot follow yourself!');
        }

        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();

        if ($existCheck) {
            return back()->with('error', 'You are already following this user!');
        }

        $newFollow = new Follow;

        $newFollow->user_id = auth()->user()->id;
        $newFollow->followedUser = $user->id;

        $newFollow->save();
        return back()->with('success', 'You are following this user!');
    }
    public function unfollow(User $user)
    {
        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->delete();
        return back()->with('success', 'You are no longer following this user!');
    }
}
