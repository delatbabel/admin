<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?php echo trans('administrator::administrator.filters') ?></h3>
        <div class="actions" style="padding-right: 7px;">
            <a class="btn-filter btn btn-primary">Filter</a>
            <a class="btn-filter-reset btn btn-default">Reset</a>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            @foreach($filters as $key => $arrCol)
                @if($arrCol['visible'])
                    <?php $tmpID = "filter_field_" . $arrCol['field_name']; ?>
                    <?php $tmpName = "filters[{$key}][value]"; ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="{{$tmpID}}">{{$arrCol['title']}}</label>
                            <input type="hidden" class="form-filter" name="filters[{{$key}}][field_name]"
                                   value="{{$arrCol['field_name']}}">
                            @if(in_array($arrCol['type'], ['key', 'text', 'color' ]))
                                <input type="text" class="form-control input-sm form-filter" name="{{$tmpName}}"/>
                            @elseif($arrCol['type'] == 'number')
                                <div class="row">
                                    <div class="col-xs-5">
                                        <input type="text" name="filters[{{$key}}][min_value]"
                                               class="form-control input-sm form-filter"/>
                                    </div>
                                    <span class="col-xs-2 text-center">-</span>
                                    <div class="col-xs-5">
                                        <input type="text" name="filters[{{$key}}][max_value]"
                                               class="form-control input-sm form-filter"/>
                                    </div>
                                </div>
                            @elseif($arrCol['type'] == 'bool')
                                <select name="{{$tmpName}}" class="form-control input-sm form-filter">
                                    <option value="">All</option>
                                    <option value="true">true</option>
                                    <option value="false">false</option>
                                </select>
                            @elseif($arrCol['type'] == 'enum')
                                <select name="{{$tmpName}}" class="form-control input-sm form-filter" id="{{$tmpID}}">
                                    <option value="">All</option>
                                    @foreach($arrCol['options']  as $item)
                                        <option value="{{$item['id']}}">{{$item['text']}}</option>
                                    @endforeach
                                </select>
                            @elseif($arrCol['type'] == 'date')
                                <div class="row">
                                    <div class="col-xs-5">
                                        <input type="text" name="filters[{{$key}}][min_value]"
                                               class="form-control input-sm form-filter date-picker"
                                               data-date-format="{{$arrCol['date_format']}}"/>
                                    </div>
                                    <span class="col-xs-2 text-center">-</span>
                                    <div class="col-xs-5">
                                        <input type="text" name="filters[{{$key}}][max_value]"
                                               class="form-control input-sm form-filter date-picker"
                                               data-date-format="{{$arrCol['date_format']}}"/>
                                    </div>
                                </div>
                            @elseif($arrCol['type'] == 'time')
                                <div class="row">
                                    <div class="col-xs-5">
                                        <div class="bootstrap-timepicker">
                                            <input type="text" id="{{$tmpID}}_min"
                                                   name="{{$tmpName}}"
                                                   value="{{$arrCol['min_value']}}"
                                                   class="form-control input-sm form-filter timepicker"/>
                                        </div>
                                    </div>
                                    <span class="col-xs-2 text-center">-</span>
                                    <div class="col-xs-5">
                                        <div class="bootstrap-timepicker">
                                            <input type="text" id="{{$tmpID}}_max"
                                                   name="{{$tmpName}}"
                                                   value="{{$arrCol['max_value']}}"
                                                   class="form-control input-sm form-filter timepicker"/>
                                        </div>
                                    </div>
                                </div>
                            @elseif($arrCol['type'] == 'belongs_to')
                                <select name="{{$tmpName}}" class="form-control input-sm form-filter" id="{{$tmpID}}">
                                    <option value="">All</option>
                                    @foreach($arrCol['options']  as $item)
                                        <option value="{{$item['id']}}">{{$item['text']}}</option>
                                    @endforeach
                                </select>
                            @elseif($arrCol['type'] == 'belongs_to_many')
                                <select name="{{$tmpName}}" class="form-control input-sm form-filter select2"
                                        multiple="multiple"
                                        id="{{$tmpID}}">
                                    @foreach($arrCol['options']  as $item)
                                        <option value="{{$item['id']}}">{{$item['text']}}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('.date-picker').datepicker();
        $('.timepicker').timepicker({
            showInputs: false
        });
        $(".select2").select2();
    });
</script>
