<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{trans('administrator::administrator.filters')}}</h3>
        <div class="box-tools pull-right">
            <a class="btn-filter btn btn-primary">Filter</a>
            <a class="btn-filter-reset btn btn-default">Reset</a>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form role="form">
                    @foreach($filters as $key => $arrCol)
                        @if($arrCol['visible'])
                            <?php $tmpID   = "filter_field_" . $arrCol['field_name']; ?>
                            <?php $tmpName = "filters[{$key}][value]"; ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    @if ($arrCol['type'] != 'hidden')
                                        <label for="{{$tmpID}}">{{$arrCol['title']}}</label>
                                    @endif
                                    {!! Form::hidden("filters[{$key}][field_name]", $arrCol['field_name'], ['class'=>"form-filter"]) !!}
                                    @if(!in_array($arrCol['type'],['number','date','datetime','time'] ))
                                        @include('admin.model.field',[
                                           'type'         => $arrCol['type'],
                                           'name'         => "filters[{$key}][value]",
                                           'id'           => $tmpID,
                                           'value'        => null,
                                           'arrCol'       => $arrCol,
                                           'defaultClass' => 'form-control input-sm form-filter',
                                           'flagFilter'   => true,
                                        ])
                                    @else
                                        <div class="row">
                                            <div class="col-xs-5">
                                                @include('admin.model.field',[
                                                   'type'         => $arrCol['type'],
                                                   'name'         => "filters[{$key}][min_value]",
                                                   'id'           => $tmpID.'_min',
                                                   'value'        => null,
                                                   'arrCol'       => $arrCol,
                                                   'defaultClass' => 'form-control input-sm form-filter',
                                                   'flagFilter'   => true,
                                                ])
                                            </div>
                                            <span class="col-xs-2 text-center">-</span>
                                            <div class="col-xs-5">
                                                @include('admin.model.field',[
                                                   'type'         => $arrCol['type'],
                                                   'name'         => "filters[{$key}][max_value]",
                                                   'id'           => $tmpID.'_max',
                                                   'value'        => null,
                                                   'arrCol'       => $arrCol,
                                                   'defaultClass' => 'form-control input-sm form-filter',
                                                   'flagFilter'   => true,
                                                ])
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</div>
