<?php

// Centaur Auth routes
// Authorization
Route::get('/login', ['as' => 'auth.login.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@getLogin']);
Route::post('/login', ['as' => 'auth.login.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@postLogin']);
Route::get('/logout', ['as' => 'auth.logout', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\SessionController@getLogout']);

// Dashboard
Route::get('dashboard', ['as' => 'dashboard', 'uses' => '\DDPro\Admin\Http\Controllers\DashboardController@index']);

// Registration
Route::get('register', ['as' => 'auth.register.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getRegister']);
Route::post('register', ['as' => 'auth.register.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@postRegister']);

// Activation
Route::get('activate/{code}', ['as' => 'auth.activation.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getActivate']);
Route::get('resend', ['as' => 'auth.activation.request', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@getResend']);
Route::post('resend', ['as' => 'auth.activation.resend', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\RegistrationController@postResend']);

// Password Reset
Route::get('password/reset/{code}', ['as' => 'auth.password.reset.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@getReset']);
Route::post('password/reset/{code}',
    ['as' => 'auth.password.reset.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@postReset']);
Route::get('password/reset', ['as' => 'auth.password.request.form', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@getRequest']);
Route::post('password/reset',
    ['as' => 'auth.password.request.attempt', 'uses' => '\DDPro\Admin\Http\Controllers\Auth\PasswordController@postRequest']);

// Users
Route::resource('users', '\DDPro\Admin\Http\Controllers\UserController');

// Roles
Route::resource('roles', '\DDPro\Admin\Http\Controllers\RoleController');
