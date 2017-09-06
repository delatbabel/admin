@if (is_array($item))
    @if (! is_numeric($key))
        <li class="treeview">
            <a href="#"><i class='fa fa-link'></i> <span>{{$key}}</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
    @endif
    @foreach ($item as $k => $subitem)
        <?php echo view("admin.layouts.menu_item", [
                'item'           => $subitem,
                'key'            => $k,
                'settingsPrefix' => $settingsPrefix,
                'pagePrefix'     => $pagePrefix,
                'routePrefix'    => $routePrefix,
        ])?>
    @endforeach
    @if (! is_numeric($key))
            </ul>
        </li>
    @endif
@else
    <li>
        <?php $tmpURL = '' ?>
        @if (strpos($key, $settingsPrefix) === 0)
            <?php $tmpURL = route('admin_settings', [substr($key, strlen($settingsPrefix))]); ?>
        @elseif (strpos($key, $pagePrefix) === 0)
            <?php $tmpURL = route('admin_page', [substr($key, strlen($pagePrefix))]); ?>
        @elseif (strpos($key, $routePrefix) === 0)
            <?php $tmpURL = route(substr($key, strlen($routePrefix))); ?>
        @else
            <?php $tmpURL = route('admin_index', [$key]); ?>
        @endif
        <a href="{{$tmpURL}}">
            <i class='fa fa-link'></i> <span>{{$item}}</span>
        </a>
    </li>
@endif
