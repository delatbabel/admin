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
                    <form action="{{ route('users.index') }}" role="form" class="ng-pristine ng-valid" method="get">
                        <div class="box-header with-border">
                            <h3 class="box-title">Filters</h3>
                            <div class="box-tools pull-right">
                                <button class="btn-filter btn btn-xs btn-sd-default">Filter</button>
                                <a href="{{ route('users.index') }}" class="btn-filter-reset btn btn-xs btn-xs-default btn-default">Reset</a>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-12">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input class="form-control input-sm form-filter" id="first_name" name="first_name" type="text" autocomplete="off" value="{{ isset($queryParams['first_name']) ? $queryParams['first_name'] : null }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input class="form-control input-sm form-filter" id="last_name" name="last_name" type="text" autocomplete="off" value="{{ isset($queryParams['last_name']) ? $queryParams['last_name'] : null }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input class="form-control input-sm form-filter" id="email" name="email" type="text" autocomplete="off" value="{{ isset($queryParams['email']) ? $queryParams['email'] : null }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="show_deleted">Show Deleted</label>
                                            <select class="form-control input-sm form-filter ng-isolate-scope select2-hidden-accessible" select2="" name="show_deleted" id="show_deleted" tabindex="-1" aria-hidden="true">
                                                <option value="all" @if(isset($queryParams['show_deleted']) && $queryParams['show_deleted'] == 'all') selected="selected" @endif>All</option>
                                                <option value="no" @if((isset($queryParams['show_deleted']) && $queryParams['show_deleted'] == 'no') || !isset($queryParams['show_deleted']))) selected="selected" @endif>Not Deleted</option>
                                                <option value="yes" @if(isset($queryParams['show_deleted']) && $queryParams['show_deleted'] == 'yes') selected="selected" @endif>Deleted</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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
                                <?php
                                if (! empty($tmpUser->country_code) && ! empty($countryList[$tmpUser->country_code])) {
                                    $countryName = $countryList[$tmpUser->country_code];
                                } else {
                                    $countryName = $tmpUser->country_code;
                                }
                                ?>
                                <tr>
                                    <td>{{$tmpUser->id}}</td>
                                    <td>{{$tmpUser->first_name}}</td>
                                    <td>{{$tmpUser->last_name}}</td>
                                    <td>{{$tmpUser->email}}</td>
                                    <td>{{$tmpUser->phone}}</td>
                                    <td>{{$countryName}}</td>
                                    <td>
                                        <a href="{{route('users.edit', $tmpUser->id)}}" style="margin: 0px;"
                                           class="btn btn-xs btn-sd-default">
                                            Edit
                                        </a>
                                        <a href='#' data-href="{{route('users.destroy', $tmpUser->id)}}"
                                           style="margin: 0px;"
                                           class="btn btn-xs btn-sd-default btn-remove">
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
                "searching": false,
                pageLength: 25,
                responsive: true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: []
            });

        });
    </script>
@endsection
