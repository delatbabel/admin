<?php

// Centaur Auth routes
// Authorization
\Illuminate\Support\Facades\Route::get('/login', ['as' => 'auth.login.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@getLogin']);
\Illuminate\Support\Facades\Route::post('/login', ['as' => 'auth.login.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@postLogin']);
\Illuminate\Support\Facades\Route::get('/logout', ['as' => 'auth.logout', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@getLogout']);

// Dashboard
\Illuminate\Support\Facades\Route::get('dashboard', ['as' => 'dashboard', 'uses' => '\DDPro\Admin\Http\Controllers\DashboardController@index']);

// Registration
\Illuminate\Support\Facades\Route::get('register', ['as' => 'auth.register.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getRegister']);
\Illuminate\Support\Facades\Route::post('register', ['as' => 'auth.register.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@postRegister']);

// Activation
\Illuminate\Support\Facades\Route::get('activate/{code}', ['as' => 'auth.activation.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getActivate']);
\Illuminate\Support\Facades\Route::get('resend', ['as' => 'auth.activation.request', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getResend']);
\Illuminate\Support\Facades\Route::post('resend', ['as' => 'auth.activation.resend', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@postResend']);

// Password Reset
\Illuminate\Support\Facades\Route::get('password/reset/{code}', ['as' => 'auth.password.reset.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@getReset']);
\Illuminate\Support\Facades\Route::post('password/reset/{code}',
    ['as' => 'auth.password.reset.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@postReset']);
\Illuminate\Support\Facades\Route::get('password/reset', ['as' => 'auth.password.request.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@getRequest']);
\Illuminate\Support\Facades\Route::post('password/reset',
    ['as' => 'auth.password.request.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@postRequest']);

// Users
\Illuminate\Support\Facades\Route::resource('users', '\DDPro\Admin\Http\Controllers\UserController');

// Roles
\Illuminate\Support\Facades\Route::resource('roles', '\DDPro\Admin\Http\Controllers\RoleController');
