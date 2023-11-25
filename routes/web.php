<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

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

Route::get('/', function () { return view('welcome'); });
Route::post('/register', function (Request $request) {
    $name = $request->name;
    $email = $request->email;

    User::create([
        'name' => $name,
        'email' => $email,
    ]);
});

// Login route
Route::get('login', function (Request $request) {

    dd($request->all());  //to check all the datas dumped from the form
    //if your want to get single element,someName in this case
    $someName = $request->someName; 

    echo $someName; //to check the value of someName

    $viewData = [
        'title' => 'Login',
        'description' => 'Login page',
        'keywords' => 'login, page',
    ];
    return view('welcome', $viewData);
});
