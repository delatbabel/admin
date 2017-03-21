@extends('admin.layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Users</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active"><strong>Users</strong></li>
            </ol>
        </section>
        <section class="content">
            <div id="admin_page" class="with_sidebar">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"></h3>
                        <div class="box-tools pull-right">
                            <a class="new_item btn btn-primary" href="{{ route('users.create') }}">
                                <i class="glyphicon glyphicon-plus"></i> Create User
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example" id="users">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Country</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $tmpUser)
                                <?php $countryName = $countryList[$tmpUser->country_code] ? $countryList[$tmpUser->country_code] : $tmpUser->country_code ?>
                                <tr>
                                    <td>{{$tmpUser->id}}</td>
                                    <td>{{$tmpUser->first_name}}</td>
                                    <td>{{$tmpUser->last_name}}</td>
                                    <td>{{$tmpUser->email}}</td>
                                    <td>{{$tmpUser->phone}}</td>
                                    <td>{{$countryName}}</td>
                                    <td>
                                        <a href="{{route('users.edit', $tmpUser->id)}}" style="margin: 0px;"
                                           class="btn btn-xs btn-success">
                                            <i class="fa fa-pencil"></i>
                                            Edit
                                        </a>
                                        <a href='#' data-href="{{route('users.destroy', $tmpUser->id)}}"
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
            $('#users').DataTable({
                pageLength: 25,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: []
            });
        });
    </script>
@endsection