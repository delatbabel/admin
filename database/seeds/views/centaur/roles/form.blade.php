@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            @if(Route::getCurrentRoute()->getName() == 'roles.edit')
                <h1>Edit Role</h1>
            @else
                <h1>Create Role</h1>
            @endif
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active"><strong>Roles</strong></li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">

                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                @if(Route::getCurrentRoute()->getName() == 'roles.edit')
                                    {!! Form::model($role, [
                                        'route'   => ['roles.update', $role->id],
                                        'method' => 'put',
                                        'class'   => 'form-horizontal',
                                    ]) !!}
                                @else
                                    {!! Form::open( [
                                        'route'   => 'roles.store',
                                        'class'   => 'form-horizontal',
                                    ]) !!}
                                @endif
                                <div class="form-group {{ ($errors->has('name')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="name">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::text('name', null, ['class'=>'form-control', 'id'=>'name']) !!}
                                        {!! ($errors->has('name') ? $errors->first('name', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="form-group {{ ($errors->has('slug')) ? 'has-error' : null }}">
                                    <label class="col-md-2 control-label" for="slug">
                                        Slug <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-md-10">
                                        {!! Form::text('slug', null, ['class'=>'form-control', 'id'=>'slug']) !!}
                                        {!! ($errors->has('slug') ? $errors->first('slug', '<p class="text-danger">:message</p>') : '') !!}
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Permission</label>
                                    <div class="col-md-10">
                                        {{-- FIXME: Get these from the database --}}
                                        <?php $arrPermissions = \Delatbabel\Keylists\Models\Keyvalue::getKeyValuesByKeyType('permissions') ?>
                                        @foreach($arrPermissions as $permission)
                                            <div class="checkbox checkbox-inline checkbox-success">
                                                {!! Form::checkbox("permissions[{$permission->keyvalue}]", 1, $role->hasAccess($permission->keyvalue), ['id'=>"per_{$permission->id}"]) !!}
                                                <label for="per_{{$permission->id}}">{{$permission->keyname}}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <div class="col-sm-4 col-sm-offset-2">
                                        <a href="{{route('roles.index')}}" class="btn btn-default ">
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