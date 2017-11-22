<?php

//
// Centaur Auth routes
//

// Authentication
Route::get('/login', [
    'as'   => 'auth.login.form',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\SessionController@getLogin'
]);
Route::post('/login', [
    'as'   => 'auth.login.attempt',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\SessionController@postLogin'
]);
Route::get('/logout', [
    'as'   => 'auth.logout',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\SessionController@getLogout'
]);

// Dashboard
Route::get('dashboard', [
    'as'   => 'dashboard',
    'uses' => '\Delatbabel\Admin\Http\Controllers\DashboardController@index'
]);

// Registration
Route::get('register', [
    'as'   => 'auth.register.form',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\RegistrationController@getRegister'
]);
Route::post('register', [
    'as'   => 'auth.register.attempt',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\RegistrationController@postRegister'
]);

// Activation
Route::get('activate/{code}', [
    'as'   => 'auth.activation.attempt',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\RegistrationController@getActivate'
]);
Route::get('resend', [
    'as'   => 'auth.activation.request',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\RegistrationController@getResend'
]);
Route::post('resend', [
    'as'   => 'auth.activation.resend',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\RegistrationController@postResend'
]);

// Password Reset
Route::get('password/reset/{code}', [
    'as'   => 'auth.password.reset.form',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\PasswordController@getReset'
]);
Route::post('password/reset/{code}', [
    'as'   => 'auth.password.reset.attempt',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\PasswordController@postReset'
]);
Route::get('password/reset', [
    'as'   => 'auth.password.request.form',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\PasswordController@getRequest'
]);
Route::post('password/reset', [
    'as'   => 'auth.password.request.attempt',
    'uses' => '\Delatbabel\Admin\Http\Controllers\Auth\PasswordController@postRequest'
]);

// Users
Route::resource('users', '\Delatbabel\Admin\Http\Controllers\UserController');

// Roles
Route::resource('roles', '\Delatbabel\Admin\Http\Controllers\RoleController');

Route::post('users/destroy_batch', [
    'as'    => 'users.destroy_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@destroyBatch'
]);

Route::post('users/undelete_batch', [
    'as'    => 'users.undelete_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@unDeleteBatch'
]);

Route::post('users/purge_batch', [
    'as'    => 'users.purge_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@purgeBatch'
]);

Route::post('users/destroy_batch', [
    'as'    => 'users.destroy_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@destroyBatch'
]);

Route::post('users/undelete_batch', [
    'as'    => 'users.undelete_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@unDeleteBatch'
]);

Route::post('users/purge_batch', [
    'as'    => 'users.purge_batch',
    'uses'  => '\Delatbabel\Admin\Http\Controllers\UserController@purgeBatch'
]);
