@extends('admin.layouts.noauth')
@section('class-wrapper-contents', 'middle-box text-center loginscreen animated fadeInDown')
@section('content')
    <div class="row">
        <div>
            <div>
                <h1 class="logo-name">IN+</h1>
            </div>
            <h3>Register to IN+</h3>
            <p>Create account to see it in action.</p>
            {!! Form::open(['route' => 'auth.register.attempt']) !!}
            <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                {!! Form::text('email', null, ['class'=>'form-control','placeholder'=>'E-mail',]) !!}
                {!! ($errors->has('email') ? $errors->first('email', '<p class="text-danger">:message</p>') : '') !!}
            </div>
            <div class="form-group  {{ ($errors->has('password')) ? 'has-error' : '' }}">
                {!! Form::password('password', ['class'=>'form-control','placeholder'=>'Password',]) !!}
                {!! ($errors->has('password') ? $errors->first('password', '<p class="text-danger">:message</p>') : '') !!}
            </div>
            <div class="form-group  {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                {!! Form::password('password_confirmation', ['class'=>'form-control','placeholder'=>'Confirm Password',]) !!}
                {!! ($errors->has('password_confirmation') ? $errors->first('password_confirmation', '<p class="text-danger">:message</p>') : '') !!}
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b">Register</button>
            <p class="text-muted text-center">
                <small>Already have an account?</small>
            </p>
            <a class="btn btn-sm btn-white btn-block" href="{{route('auth.login.form')}}">Login</a>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
