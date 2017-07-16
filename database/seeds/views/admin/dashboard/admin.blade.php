@extends('admin.layouts.main')

@section('content')
    <div class="content-wrapper">
        {{-- Content Header (Page header) --}}
        <section class="content-header">
            <h1>
                {{ Config::get('administrator.title') }}
                <small>Dashboard</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('admin_dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        {{-- Main content --}}
        <section class="content">
            <div class='row'>
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Dashboard</h3>
                        </div>{{-- /.box-header --}}
                        <div class="box-body">
                            Dashboard goes here.
                        </div>{{-- /.box-body --}}
                    </div>{{-- /.box --}}
                </div>{{-- /.col-xs-12 --}}
            </div>{{-- /.row --}}
        </section>{{-- /.content --}}
    </div>{{-- /.content-wrapper --}}
@endsection
