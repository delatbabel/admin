<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{{$config->getOption('title')}} Form</h3>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form class="form-horizontal">
        <div class="box-body">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">{{$arrayFields['first_name']['title']}}</label>

                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail3" placeholder="{{$arrayFields['first_name']['title']}}">
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">{{$arrayFields['last_name']['title']}}</label>

                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail3" placeholder="{{$arrayFields['last_name']['title']}}">
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-default">Cancel</button>
                <button type="submit" class="btn btn-info pull-right">Sign in</button>
            </div>
            <!-- /.box-footer -->
    </form>
</div>
