@extends('admin.layouts.noauth')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="ibox-content">
                <h2 class="font-bold">Reset Your Password</h2>
                <div class="row">
                    <div class="col-lg-12">
                        {!! Form::open(['route' => ['auth.password.reset.attempt',$code]]) !!}
                        <div class="form-group  {{ ($errors->has('password')) ? 'has-error' : '' }}">
                            {!! Form::password('password', ['class'=>'form-control','placeholder'=>'Password',]) !!}
                            {!! ($errors->has('password') ? $errors->first('password', '<p class="text-danger">:message</p>') : '') !!}
                        </div>
                        <div class="form-group  {{ ($errors->has('password_confirmation')) ? 'has-error' : '' }}">
                            {!! Form::password('password_confirmation', ['class'=>'form-control','placeholder'=>'Confirm Password',]) !!}
                            {!! ($errors->has('password_confirmation') ? $errors->first('password_confirmation', '<p class="text-danger">:message</p>') : '') !!}
                        </div>
                        <button type="submit" class="btn btn-primary block full-width m-b">Save</button>
                        {!! Form::close() !!}
						@if (Session::has('error'))
							<p class="text-danger">{!! session('error') !!}</p>
						@elseif (Session::has('success'))
							<p class="text-danger">{!! session('success') !!}</p>
						@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
