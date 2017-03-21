@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Create User</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active"><strong>Users</strong></li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">

                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::open([
                                    'route'   => 'users.store',
                                    'class'   => 'form-horizontal',
                                ]) !!}
                                <div class="form-group {{ ($errors->has('first_name')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="first_name">First Name</label>
                                    <div class="col-md-10">
                                        {!! Form::text('first_name', null, ['class'=>'form-control', 'id'=>'first_name']) !!}
                                        {!! ($errors->has('first_name') ? $errors->first('first_name', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="form-group {{ ($errors->has('last_name')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="last_name">Last Name</label>
                                    <div class="col-md-10">
                                        {!! Form::text('last_name', null, ['class'=>'form-control', 'id'=>'last_name']) !!}
                                        {!! ($errors->has('last_name') ? $errors->first('last_name', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('email')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="email">
                                        E-mail <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::text('email', null, ['class'=>'form-control', 'id'=>'email']) !!}
                                        {!! ($errors->has('email') ? $errors->first('email', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('phone')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="phone">Phone</label>
                                    <div class="col-md-10">
                                        {!! Form::text('phone', null, ['class'=>'form-control', 'id'=>'phone']) !!}
                                        {!! ($errors->has('phone') ? $errors->first('phone', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('country_code')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label">
                                        Country <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::select('country_code',['' => 'Choose a Country...']+$countryList, null, ['class'=>'chosen-select']) !!}
                                        {!! ($errors->has('country_code') ? $errors->first('country_code', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('timezone')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label">
                                        Time Zone <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::select('timezone',['' => 'Choose a TimeZone...']+$timezoneList, null, ['class'=>'chosen-select']) !!}
                                        {!! ($errors->has('timezone') ? $errors->first('timezone', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Role</label>
                                    <div class="col-md-10">
                                        @foreach($roles as $role)
                                            <div class="checkbox checkbox-inline checkbox-success">
                                                {!! Form::checkbox("roles[{$role->slug}]",$role->id, null, ['id'=>"roles_{$role->slug}"]) !!}
                                                <label for="roles_{{$role->slug}}">{{$role->name}}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('password')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="password">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::password('password', ['class'=>'form-control', 'id'=>'password']) !!}
                                        {!! ($errors->has('password') ? $errors->first('password', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group {{ ($errors->has('password_confirmation')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="password_confirmation">
                                        Password Confirmation <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::password('password_confirmation', ['class'=>'form-control', 'id'=>'password_confirmation']) !!}
                                        {!! ($errors->has('password_confirmation') ? $errors->first('password_confirmation', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <div class="col-md-10 col-md-offset-2">
                                        <div class="checkbox checkbox-inline checkbox-success">
                                            {!! Form::checkbox('active',true, null, ['id'=>"active"]) !!}
                                            <label for='active'>Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <div class="col-sm-4 col-sm-offset-2">
                                        <a href="{{route('users.index')}}" class="btn btn-default ">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">Save & Close</button>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(function () {
            $('.chosen-select').chosen();
        });
    </script>
@endsection