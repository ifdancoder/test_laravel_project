<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private function GetSharedData($user) {
        $existingFollow = 0;
        if (auth()->check()) {
            $existingFollow = Follow::where([['user_id', '=', auth()->user()->id], ['followedUser', '=', $user->id]])->count();
        }
        View::share("sharedData", ["existingFollow" => $existingFollow,
        "avatar" => $user->avatar,
        "name" => $user->username,
        'postCount' => $user->posts()->count(),
        'followingCount' => $user->following()->count(),
        'followerCount' => $user->followers()->count()]);
    }
    public function profilePosts(User $user) {
        $this->GetSharedData($user);
        return view("profile_posts", ["posts" => $user->posts()->latest()->get()]);
    }
    public function profileFollowers(User $user) {
        $this->GetSharedData($user);
        return view("profile_followers", ["followers" => $user->followers()->latest()->get()]);
    }
    public function profileFollowing(User $user) {
        $this->GetSharedData($user);
        return view("profile_following", ["following" => $user->following()->latest()->get()]);
    }
    public function profilePostsRaw(User $user) {
        return response()->json(['theHTML' => view('profile_posts_only', ["posts" => $user->posts()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Posts"]);
    }
    public function profileFollowersRaw(User $user) {
        return response()->json(['theHTML' => view("profile_followers_only", ["followers" => $user->followers()->latest()->get()])->render(), 'docTitle' => $user->username . "'s Followers"]);
    }
    public function profileFollowingRaw(User $user) {
        return response()->json(['theHTML' => view("profile_following_only", ["following" => $user->following()->latest()->get()])->render(), 'docTitle' => "Who " . $user->username . " Follows"]);
    }
    public function logout() {
        event(new OurExampleEvent(['username' => auth()->user()->username, 'action' => 'logged out']));
        auth()->logout();
        return redirect("/")->with('success', 'You are now logged out!');
    }
    public function showCorrectHomepage() {
        if (auth()->check()) {
            return view("home_feed", ['posts' => auth()->user()->feedPosts()->latest()->paginate(2)]);
        }
        else {
            $postCount = Cache::remember("postCount", 60, function () {
                return Post::count();
            });
            return view("home_guest", ['postCount' => $postCount]);
        }
    }
    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect("/")->with('success', 'You are now registered!');
    }

    public function loginApi(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);
        if (auth()->attempt($incomingFields)) {
            $user = User::where('username', $incomingFields['username'])->first();
            $token = $user->createToken('authToken')->plainTextToken;
            return $token;
        }
        return '';
    }
    public function login(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);
        if (auth()->attempt($incomingFields)) {
            $request->session()->regenerate();
            event(new OurExampleEvent(['username' => $incomingFields['username'], 'action' => 'logged in']));
            return redirect("/")->with('success', 'You are now logged in!');
        }
        else {
            return redirect("/")->with('error', 'You are now not logged in!');
        }
    }

    public function adminOnly() {
        return "You are admin!";
    }

    public function manageAvatar() {
        return view('manage_avatar');
    }

    public function uploadAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:16384'
        ]);
        $user = auth()->user();
        $filename = $user->username . uniqid() . '.jpg';
        $imageData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $imageData);

        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != 'fallback-avatar.jpg') {
            Storage::delete(str_replace('/storage/', 'public/', $oldAvatar));
        }
        return redirect("/")->with('success', 'Avatar uploaded!');
    }
}
