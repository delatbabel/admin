@extends('adminlayouts.noauth')
@section('class-wrapper-contents', 'loginColumns animated fadeInDown')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <h2 class="font-bold">Welcome to IN+</h2>
            <p>
                Perfectly designed and precisely prepared admin theme with over 50 pages with extra new web app
                views.
            </p>
            <p>
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the
                industry's standard dummy text ever since the 1500s.
            </p>
            <p>
                When an unknown printer took a galley of type and scrambled it to make a type specimen book.
            </p>
            <p>
                <small>It has survived not only five centuries, but also the leap into electronic typesetting,
                    remaining
                    essentially unchanged.
                </small>
            </p>
        </div>
        <div class="col-md-6">
            <div class="ibox-content">
                {!! Form::open(['route' => 'auth.login.attempt']) !!}
                <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                    {!! Form::text('email', null, ['class'=>'form-control','placeholder'=>'E-mail',]) !!}
                    {!! ($errors->has('email') ? $errors->first('email', '<p class="text-danger">:message</p>') : '') !!}
                </div>
                <div class="form-group  {{ ($errors->has('password')) ? 'has-error' : '' }}">
                    {!! Form::password('password', ['class'=>'form-control','placeholder'=>'Password',]) !!}
                    {!! ($errors->has('password') ? $errors->first('password', '<p class="text-danger">:message</p>') : '') !!}
                </div>
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('remember', true, null) !!} Remember Me
                    </label>
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
                <a href="{{route('auth.password.request.form')}}">
                    <small>Forgot password?</small>
                </a>
                <p class="text-muted text-center">
                    <small>Do not have an account?</small>
                </p>
                <a class="btn btn-sm btn-white btn-block" href="{{route('auth.register.form')}}">Create an account</a>
                {!! Form::close() !!}
                <p class="m-t">
                    <small>Inspinia we app framework base on Bootstrap 3 &copy; 2014</small>
                </p>
            </div>
        </div>
    </div>
@endsection
