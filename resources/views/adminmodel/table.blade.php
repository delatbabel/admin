<div class="table_container">
    <div class="box">
        <div class="results_header box-header">
            <h2 class="box-title">{{$config->getOption('title')}}</h2>
            <div class="actions" style="padding-right: 7px;">
                @foreach($globalActions as $arr)
                    @if($arr['has_permission'])
                        <input type="button" class="btn btn-info" value="{{$arr['title']}}"/>
                    @endif
                @endforeach
                @if(isset($actionPermissions['update']) === true)
                    <a class="edit_item btn btn-primary" style="display: none">
                        {{trans('administrator::administrator.edit')}} {{$config->getOption('single')}}
                    </a>
                @endif
                @if(isset($actionPermissions['create']) === true)
                    <a class="new_item btn btn-primary" href="{{"$baseUrl/{$config->getOption('name')}/new"}}">
                        {{trans('administrator::administrator.new')}} {{$config->getOption('single')}}
                    </a>
                @endif
            </div>
            <div class="action_message" data-bind="css: { error: globalStatusMessageType() == 'error',
                                                    success: globalStatusMessageType() == 'success' },
                                                    notification: globalStatusMessage "></div>
        </div>
        <table class="table table-bordered table-striped" border="0" cellspacing="0" id="customers" cellpadding="0">
            <thead>
            <tr>
                @foreach($columnModel as $tmpArr)
                    <th>{{$tmpArr['title']}}</th>
                @endforeach
            </tr>
            </thead>
        </table>
    </div>
</div>
<div class="item_edit_container"
     data-bind="itemTransition: activeItem() !== null || loadingItem(), style: {width: expandWidth() + 'px'}">
    <div class="item_edit box box-primary" style="width: 100% !important;">
        @include('adminmodel.edit')
    </div>
</div>
