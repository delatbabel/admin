@extends('adminlayouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Custom Layout
                <small>{{ $config->getOption('title') }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            @if($itemId)
                                {{trans('administrator::administrator.edit')}}
                            @else
                                {{trans('administrator::administrator.createnew')}}
                            @endif
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::model($model, [
                                        'class'   => 'form-horizontal',
                                        'enctype' => 'multipart/form-data',
                                        'route'   => ['admin_save_item',$config->getOption('name'),$itemId],
                                    ]) !!}
                                <div class="row">
                                    @foreach($arrayFields as $key => $arrCol)
                                        @if($arrCol['visible'] && $arrCol['editable'])
                                            <?php $tmpClass = in_array($key,
                                                ['content', 'websites']) ? 'col-md-12' : 'col-md-6'; ?>
                                            <div class="{{$tmpClass}}">
                                                <div class="form-group">
                                                    <?php $tmpClass = in_array($key,
                                                        ['content', 'websites']) ? 'col-md-2' : 'col-md-4'; ?>
                                                    <label class="{{$tmpClass}} control-label"
                                                           for="{{$arrCol['field_name']}}">
                                                        {{$arrCol['title']}}:
                                                    </label>
                                                    <?php $tmpClass = in_array($key,
                                                        ['content', 'websites']) ? 'col-md-10' : 'col-md-8'; ?>
                                                    <div class="{{$tmpClass}}">
                                                        @include('adminmodel.field',[
                                                           'type'         => $arrCol['type'],
                                                           'name'         => $arrCol['field_name'],
                                                           'id'           => $arrCol['field_name'],
                                                           'value'        => $model->{$arrCol['field_name']},
                                                           'arrCol'       => $arrCol,
                                                           'defaultClass' => 'form-control',
                                                           'flagFilter'   => false,
                                                        ])
                                                        @if ($errors->has($arrCol['field_name']))
                                                            <p style="color:red;">
                                                                {!!$errors->first($arrCol['field_name'])!!}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-4 col-sm-offset-2">
                                        <a href="{{route('admin_index', $config->getOption('name'))}}" class="btn btn-default ">
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
