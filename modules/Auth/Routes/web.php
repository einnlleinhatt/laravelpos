<?php

use Illuminate\Support\Facades\Route;

// Auth
// Auth::routes();
Route::post('/login', 'LoginController@login');
Route::get('/logout', 'LoginController@logout');
Route::get('/login', 'LoginController@login')->name('login');
Route::post('/password/reset', 'ResetPasswordController@reset');
Route::delete('/logout', 'LoginController@logout')->name('logout');
Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail');
Route::get('/password/email', function () {
    return redirect()->to('/auth/forgot');
})->middleware(['guest']);

// Reset password handle by module
Route::get('/password/reset/{token}', function ($token) {
    return redirect()->to('/#/login?reset=' . $token);
})->name('password.reset');

// Uncomment lien below to handle reset password by auth module
// Route::view('/password/reset/{token}', 'auth::reset')->middleware(['guest'])->name('password.reset');

Route::prefix('auth')->group(function () {
    Route::view('login', 'auth::login')->middleware(['guest']);
    Route::view('forgot', 'auth::forgot')->middleware(['guest']);

    Route::view('/', 'auth::index');
    Route::view('{any}', 'auth::index')->where('any', '.*');
});
