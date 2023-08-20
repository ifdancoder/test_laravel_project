<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPostEmail;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function drawPostForm() {
        return view('create_post');
    }
    public function createPost(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $post = Post::create($incomingFields);

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name' => auth()->user()->username,
            'title' => $post->title
        ]));

        return redirect("/post/".$post['id'])->with('success', 'Post created!');
    }

    public function createPostApi(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $post = Post::create($incomingFields);

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name' => auth()->user()->username,
            'title' => $post->title
        ]));

        return $post->id;
    }

    public function showPost(Post $post)
    {
        $post['body'] = Str::markdown($post->body);
        return view('single_post', ['post' => $post]);
    }

    public function deletePost(Post $post) {
        if(auth()->user()->cannot('delete', $post)){
            return redirect('/profile/' . auth() -> user() -> username)->with('error', 'You cannot delete this post');
        }
        else{
            $post -> delete();
            return redirect('/profile/' . auth() -> user() -> username)->with('success', 'Post deleted');
        }
    }
    public function deletePostApi(Post $post) {
        $post -> delete();
        return 'true';
    }
    public function showEditPost(Post $post) {
        return view('edit_post', ['post' => $post]);
    }
    public function updatePost(Request $request, Post $post) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);
        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $post->update($incomingFields);

        return redirect('/post/' . $post['id'])->with('success', 'Post updated');
    }

    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }
}
