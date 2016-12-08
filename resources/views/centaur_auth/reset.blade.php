@extends('adminlayouts.noauth')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="ibox-content">
                <h2 class="font-bold">Forgot password</h2>
                <p>
                    Enter your email address and your password will be reset and emailed to you.
                </p>
                <div class="row">
                    <div class="col-lg-12">
                        {!! Form::open(['route' => 'auth.password.request.attempt']) !!}
                        <div class="form-group {{ ($errors->has('email')) ? 'has-error' : '' }}">
                            {!! Form::email('email', null, ['class'=>'form-control','placeholder'=>'E-mail',]) !!}
                            {!! ($errors->has('email') ? $errors->first('email', '<p class="text-danger">:message</p>') : '') !!}
                        </div>
                        <button type="submit" class="btn btn-primary block full-width m-b">Send new password</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
