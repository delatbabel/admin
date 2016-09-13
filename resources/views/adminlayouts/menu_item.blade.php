@if (is_array($item))
    <li class="treeview">
        <a href="#"><i class='fa'></i>
            <span>{{$key}}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
        </a>
        <ul class="treeview-menu">
            @foreach ($item as $k => $subitem)
                <?php echo view("adminlayouts.menu_item", array(
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
            <a href="{{route('admin_settings', array(substr($key, strlen($settingsPrefix))))}}"><i class='fa fa-link'></i> {{$item}}</a>
        @elseif (strpos($key, $pagePrefix) === 0)
            <a href="{{route('admin_page', array(substr($key, strlen($pagePrefix))))}}"><i class='fa fa-link'></i> {{$item}}</a>
        @else
            <a href="{{route('admin_index', array($key))}}"><i class='fa fa-link'></i> {{$item}}</a>
        @endif
    </li>
@endif
