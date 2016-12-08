{{--
 Done:
    key
    text
    password
    color
    markdown textarea html
    wysiwyg (CKeditor)
    bool
    date
    time
    number
    enum belongs_to
    belongs_to_many
    file, image
 --}}
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
@elseif(in_array($type, ['markdown', 'textarea', 'html']))
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'rows'=>$arrCol['height'], 'id'=>$id]) !!}
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            var customSetting_{{$id}} = {};
            @if(isset($arrCol['markitup_type']))
                customSetting_{{$id}}.nameSpace = '{{$arrCol['markitup_type']}}';
            @elseif($type == 'markdown')
                customSetting_{{$id}}.nameSpace = '{{$type}}';
            @endif
            $("#{{$id}}").markItUp(mySettings, customSetting_{{$id}});
        });
    </script>
@endsection
@elseif($type == 'wysiwyg')
    {!! Form::textarea($name, null, ['class'=> $defaultClass, 'rows'=>$arrCol['height'], 'maxlength'=>$arrCol['limit'], 'id'=>$id]) !!}
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            CKEDITOR.replace('{{$id}}', {
                uiColor: '#CCEAEE'
            });
        });
    </script>
@endsection
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
    <div class="input-group date">
        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
        {!! Form::text($name, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
    </div>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").datepicker({
                dateFormat: "{{$arrCol['date_format']}}"
            });
        });
    </script>
@endsection
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
@elseif($type == 'file'||$type == 'image')
    {!! Form::file($name, ['class'=> $defaultClass, 'id'=>$id]) !!}
@elseif($type == 'enum' || $type == 'belongs_to')
    <?php
    $tmpArr = ['' => $flagFilter ? 'All' : ''];
    foreach ($arrCol['options'] as $tmpSubArr) {
        $tmpArr[$tmpSubArr['id']] = $tmpSubArr['text'];
    }
    ?>
    {!! Form::select($name, $tmpArr, null, ['class'=> $defaultClass, 'id'=>$id]) !!}
@elseif($type == 'belongs_to_many')
    <select class="{{$defaultClass}} select2"
            multiple="multiple"
            name="{{ $name }}"
            id="{{ $id }}">
        @foreach($arrCol['options']  as $item)
            <?php $tmpSelected = is_array($value) && in_array($item['id'], $value) ? 'selected="selected"' : null ?>
            <option value="{{$item['id']}}" {{$tmpSelected}}>
                {{$item['text']}}
            </option>
        @endforeach
    </select>
@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {
            $("#{{$id}}").select2();
        });
    </script>
@endsection
@endif
