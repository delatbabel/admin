@if($type == 'key')
    @if($flagFilter)
        {!! Form::text($name, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
    @else
        <p class="form-control-static"> {{ $value }} </p>
    @endif
@elseif($type == 'text')
    {!! Form::text($name, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
@elseif($type == 'password')
    {!! Form::password($name, ['class'=> $defaultClass, 'id'=>$id]) !!}
@elseif($type == 'color')
    {!! Form::text($name, '#5367ce', ['class'=> $defaultClass,'id'=>$id]) !!}
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").colorpicker();
        });
    </script>
@endsection
@elseif($type == 'textarea')
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'maxlength'=>$arrCol['limit'], 'rows'=>$arrCol['height'], 'id'=>$id]) !!}
@elseif($type == 'html')
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'maxlength'=>$arrCol['limit'], 'rows'=>$arrCol['height'], 'id'=>$id]) !!}
@elseif($type == 'static')
    {!! $arrCol['content'] !!}
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").markItUp(myHtmlSettings);
        });
    </script>
@endsection
@elseif($type == 'markdown')
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'maxlength'=>$arrCol['limit'], 'rows'=>$arrCol['height'], 'id'=>$id]) !!}
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").markItUp(myMarkdownSettings);
        });
    </script>
@endsection
@elseif($type == 'json')
<?php
if (is_array($value)) {
    $tmpValue = json_encode($value);
} elseif ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
    $tmpValue = json_encode($value->toArray());
} else {
    $tmpValue = json_encode([]);
}
$tmpValue = old($name, $tmpValue);
?>
    {!! Form::hidden($name, $tmpValue, ['class'=> $defaultClass, 'id'=>$id]) !!}
    <div id="jsoneditor{{$id}}" style="height: {{$arrCol['height']}}px;"></div>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            var jsonString{{$id}} = {!! $tmpValue ?: 'null'!!};
            var editorJSON{{$id}} = new JSONEditor(document.getElementById("jsoneditor{{$id}}"), {
                mode: 'tree',
                modes: ['code', 'form', 'text', 'tree', 'view'],
                onChange: function () {
                    var tmpJSON = editorJSON{{$id}}.get();
                    $("#{{$id}}").val(JSON.stringify(tmpJSON));
                }
            }, jsonString{{$id}});
        });
    </script>
@endsection
@elseif($type == 'wysiwyg')
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'maxlength'=>$arrCol['limit'], 'rows'=>$arrCol['height'], 'id'=>$id, 'wysiwyg'=>'']) !!}
@elseif($type == 'number')
    <div class="input-group">
        <span class="input-group-addon">{{$arrCol['symbol']}}</span>
        {!! Form::text($name, null, ['class'=> "$defaultClass", 'id'=>$id]) !!}
    </div>
@elseif($type == 'bool')
    @if($flagFilter)
        <select name="{{$name}}" class="{{$defaultClass}}" id="{{ $id }}">
            <option value="">All</option>
            <option value="true">true</option>
            <option value="false">false</option>
        </select>
    @else
        <label class="col-md-3 control-label">
            {!! Form::checkbox($name, true) !!}
        </label>
    @endif
@elseif($type == 'date')
    <?php
    $minDate = isset($arrCol['min_date']) ? $arrCol['min_date'] : null;
    ?>
    <div class="input-group date">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        {!! Form::text($name, null, ['class'=> $defaultClass.' date-picker', 'id'=>$id, 'date-format'=>$arrCol['date_format'], 'min-date'=>$minDate]) !!}
    </div>
@elseif($type == 'datetime')
    <div class="input-group date">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        {!! Form::text($name, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
    </div>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").datetimepicker({
                dateFormat: "{{$arrCol['date_format']}}",
                timeFormat: "{{$arrCol['time_format']}}"
            });
        });
    </script>
@endsection
@elseif($type == 'time')
    <div class="input-group clockpicker" data-autoclose="true">
        {!! Form::text($name, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
        <span class="input-group-addon">
            <span class="fa fa-clock-o"></span>
        </span>
    </div>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").timepicker({
                timeFormat: "{{$arrCol['time_format']}}"
            });
        });
    </script>
@endsection
@elseif($type == 'file')
    {!! Form::file($name, ['class'=> $defaultClass, 'id'=>$id]) !!}
@elseif($type == 'image')
    {{-- bootstrap-imageupload. --}}
    <div image-upload class="{{ $id }}_imageupload panel panel-default">
        <div class="file-tab panel-body">
            @if ($model->{$id . '_preview'})
                <img src="{{ $model->{$id . '_preview'} }}" alt="Image preview" class="thumbnail">
            @endif
            <div class="btn btn-default btn-file">
                <span>Browse</span>
                <input type="file" name="{{ $id }}">
            </div>
            <button type="button" class="btn btn-default">Remove</button>
        </div>
        <input type="hidden" name="{{ $id }}_original" class="original" value="{{isset($model) ? $model->{$id} : ''}}">
    </div>
@elseif($type == 'enum' || $type == 'enum_multiple' || $type == 'belongs_to')
    <?php
    $tmpArr = [];
    if ($type != 'enum_multiple') {
        $tmpArr = ['' => $flagFilter ? 'All' : 'Select'];
    }
    foreach ($arrCol['options'] as $tmpSubArr) {
        $tmpArr[$tmpSubArr['id']] = $tmpSubArr['text'];
    }
    $tmpDefault = null;
    if ((!old($name) && (!isset($model) || !isset($model->{$name}))) && isset($arrCol['default'])) {
        $tmpDefault = $arrCol['default'];
    }

    // Check for option "persist"
    if ((!old($name) && (!isset($model) || !isset($model->{$name}))) && isset($arrCol['persist']) && $arrCol['persist']) {
        $tmpDefault = session('persist__' . $name);
    }
    // Attributes
    $attributes = ['class'=> $defaultClass, 'id'=>$id, 'select2' => ''];
    if ($type == 'enum_multiple') {
        $attributes = $attributes + ['multiple' => 'multiple'];
    }
    ?>
    {!! Form::select($name, $tmpArr, $tmpDefault, $attributes) !!}
@elseif($type == 'selectize')
    <?php
    $tmpArr = ['' => $flagFilter ? 'All' : 'Select'];
    foreach ($arrCol['options'] as $tmpSubArr) {
        $tmpArr[$tmpSubArr['id']] = $tmpSubArr['text'];
    }
    if (isset($model->{$name}) && !in_array($model->{$name}, $tmpArr)) {
        $tmpArr[$model->{$name}] = $model->{$name};
    }
    $tmpDefault = null;
    if ((!old($name) && (!isset($model) || !isset($model->{$name}))) && isset($arrCol['default'])) {
        $tmpDefault = $arrCol['default'];
    }

    // Check for option "persist"
    if ((!old($name) && (!isset($model) || !isset($model->{$name}))) && isset($arrCol['persist']) && $arrCol['persist']) {
        $tmpDefault = session('persist__' . $name);
    }
    ?>
    {!! Form::select($name, $tmpArr, $tmpDefault, ['class'=> $defaultClass, 'id'=>$id, 'selectize' => '']) !!}
@elseif($type == 'belongs_to_many')
    <?php
    if (old($id)) {
        $value = old($id);
    }
    ?>
    <select select2 class="{{$defaultClass}}"
            multiple="multiple"
            name="{{ $name }}[]"
            id="{{ $id }}">
        @foreach($arrCol['options']  as $item)
            <?php $tmpSelected = is_array($value) && in_array($item['id'], $value) ? 'selected="selected"' : null ?>
            <option value="{{$item['id']}}" {{$tmpSelected}}>
                {{$item['text']}}
            </option>
        @endforeach
    </select>
@endif
