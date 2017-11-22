<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{trans('administrator::administrator.filters')}}</h3>
        <div class="box-tools pull-right">
            <a class="btn-filter btn btn-xs btn-sd-default">Filter</a>
            <a class="btn-filter-reset btn btn-xs btn-xs-default btn-default">Reset</a>
        </div>
    </div>
    <div class="box-body">
        <form role="form">
            <?php $counter = 0; ?>
            @foreach($filters as $key => $arrCol)

                @if (($counter % 3) == 0)
                <?php $divopen = true; ?>
                <div class="row">
                    <div class="col-sm-12">
                @endif

                @if($arrCol['visible'])
                    <?php
                    $tmpID   = "filter_field_" . $arrCol['field_name'];
                    $tmpName = "filters[{$key}][value]";
                    $value   = null;
                    if (! empty(request('filter_' . $key))) {
                        $value = request('filter_' . $key);
                    }
                    ?>
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
                                   'value'        => $value,
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
                                           'value'        => $value,
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
                                           'value'        => $value,
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

                @if (($counter % 3) == 2)
                    </div>
                </div>
                <?php $divopen = false; ?>
                @endif

                <?php $counter++; ?>
            @endforeach

        @if ($divopen)
            </div>
        </div>
        @endif

        </form>
    </div>
</div>
