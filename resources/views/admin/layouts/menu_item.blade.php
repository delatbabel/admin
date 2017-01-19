@if (is_array($item))
    <li class="treeview">
        <a href="#"><i class='fa fa-link'></i> <span>{{$key}}</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
            @foreach ($item as $k => $subitem)
                <?php echo view("admin.layouts.menu_item", array(
                        'item' => $subitem,
                        'key' => $k,
                        'settingsPrefix' => $settingsPrefix,
                        'pagePrefix' => $pagePrefix
                ))?>
            @endforeach
        </ul>
    </li>
@else
    <li>
        @if (strpos($key, $settingsPrefix) === 0)
            <a href="{{route('admin_settings', array(substr($key, strlen($settingsPrefix))))}}"><i class='fa fa-link'></i> <span>{{$item}}</span></a>
        @elseif (strpos($key, $pagePrefix) === 0)
            <a href="{{route('admin_page', array(substr($key, strlen($pagePrefix))))}}"><i class='fa fa-link'></i> <span>{{$item}}</span></a>
        @else
            <a href="{{route('admin_index', array($key))}}"><i class='fa fa-link'></i> <span>{{$item}}</span></a>
        @endif
    </li>
@endif
