<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [UserController::class, "showCorrectHomepage"]);
Route::post('/register', [UserController::class, "register"])->middleware("guest");
Route::post('/login', [UserController::class, "login"])->middleware("guest");
Route::post('/logout', [UserController::class, "logout"])->middleware("mustBeLoggedIn");

Route::get('/create-post', [PostController::class, "drawPostForm"])->middleware("mustBeLoggedIn");
Route::post('/create-post', [PostController::class, "createPost"])->middleware("mustBeLoggedIn");
Route::get('/post/{post}', [PostController::class, "showPost"]);
Route::delete('/post/{post}', [PostController::class, "deletePost"])->middleware('can:delete,post');

Route::get('/post/{post}/edit', [PostController::class, "showEditPost"])->middleware('can:update,post');
Route::put('/post/{post}/edit', [PostController::class, "updatePost"])->middleware('can:update,post');

Route::get('/profile/{user:username}', [UserController::class, "profilePosts"]);
Route::get('/profile/{user:username}/followers', [UserController::class, "profileFollowers"]);
Route::get('/profile/{user:username}/following', [UserController::class, "profileFollowing"]);

Route::middleware('cache.headers:public;max_age=20;etag')->group(function(){
    Route::get('/profile/{user:username}/raw', [UserController::class, "profilePostsRaw"]);
    Route::get('/profile/{user:username}/followers/raw', [UserController::class, "profileFollowersRaw"]);
    Route::get('/profile/{user:username}/following/raw', [UserController::class, "profileFollowingRaw"]);
});

Route::get('/admin-only', [UserController::class, "adminOnly"])->middleware("can:isAdmin");

Route::get('/manage-avatar', [UserController::class, "manageAvatar"])->middleware("mustBeLoggedIn");
Route::post('/manage-avatar', [UserController::class, "uploadAvatar"])->middleware("mustBeLoggedIn");

Route::post('/follow/{user:username}', [FollowController::class, "follow"])->middleware("mustBeLoggedIn");
Route::post('/unfollow/{user:username}', [FollowController::class, "unfollow"])->middleware("mustBeLoggedIn");
Route::get('/search/{term}', [PostController::class, "search"]);

Route::post('/send-chat-message', function (Request $request) {
    $incomingFields = $request->validate([
        'textvalue' => ['required']
    ]);

    if (!trim(strip_tags($incomingFields['textvalue']))) {
        return response()->noContent();
    }

    broadcast(new ChatMessage([
        'username' => auth()->user()->username,
        'textvalue' => strip_tags($incomingFields['textvalue']),
        'avatar' => auth()->user()->avatar]))->toOthers();
    return response()->noContent();
})->middleware('mustBeLoggedIn');