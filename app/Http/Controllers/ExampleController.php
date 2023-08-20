<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function home_guest() {
        return view("home_guest");
    }
    public function post() {
        return view("post");
    }
}
