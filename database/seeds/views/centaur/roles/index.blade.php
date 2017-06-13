@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Roles</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active"><strong>Roles</strong></li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"></h3>
                        <div class="box-tools pull-right">
                            <a class="new_item btn btn-primary" href="{{ route('roles.create') }}">
                                <i class="glyphicon glyphicon-plus"></i> Create Role
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example" id="roles">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($roles as $tmpRole)
                                <tr>
                                    <td>{{$tmpRole->id}}</td>
                                    <td>{{$tmpRole->name}}</td>
                                    <td>{{$tmpRole->slug}}</td>
                                    <td>
                                        @if($tmpRole->permissions)
                                            <span class="badge">
                                                {!! implode("</span> <span class='badge'>", array_keys($tmpRole->permissions)) !!}
                                                </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{route('roles.edit', $tmpRole->id)}}" style="margin: 0px;"
                                           class="btn btn-xs btn-success">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <a href='#' data-href="{{route('roles.destroy', $tmpRole->id)}}"
                                           style="margin: 0px;"
                                           class="btn btn-xs btn-danger btn-remove">
                                            <i class="fa fa-trash"></i>
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    {!! Form::open(['class'=>'frm-delete','method' => 'DELETE']) !!}
    {!! Form::close() !!}
@endsection
@section('javascript')
    <script type="text/javascript">
        $(function () {
            $('a.btn-remove').click(function () {
                if (!window.confirm('Are you sure you want to delete this item? This cannot be reversed.')) {
                    return;
                }
                $('form.frm-delete').attr('action', $(this).data('href')).submit();
            });
            $('#roles').DataTable({
                pageLength: 25,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: []
            });
        });
    </script>
@endsection