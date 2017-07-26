<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome', ['posts' => App\Post::all(), 'tags' => App\Tag::all()]);
});

Route::post('/', function () {
    $post = new App\Post();
    $post->body = request()->body;
    $post->save();
    $post->tags()->attach(request()->tags);
    return redirect('/');
});

