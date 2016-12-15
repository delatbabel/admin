@if (is_array($item))
    @if (! is_numeric($key))
    <li>
            <a href="#">
                <i class="fa fa-sitemap"></i>
                <span class="nav-label">{{$key}} </span>
                <span class="fa arrow"></span>
            </a>
        <ul class="nav nav-second-level collapse">
    @endif
    @foreach ($item as $k => $subitem)
        <?php echo view("adminlayouts.menu_item", array(
            'item'           => $subitem,
            'key'            => $k,
            'settingsPrefix' => $settingsPrefix,
            'pagePrefix'     => $pagePrefix,
            'routePrefix'    => $routePrefix,
        ))?>
    @endforeach
    @if (! is_numeric($key))
        </ul>
    </li>
    @endif
@else
    <li>
        @if (strpos($key, $settingsPrefix) === 0)
            <?php $tmpURL = route('admin_settings', array(substr($key, strlen($settingsPrefix)))); ?>
        @elseif (strpos($key, $pagePrefix) === 0)
            <?php $tmpURL = route('admin_page', array(substr($key, strlen($pagePrefix)))); ?>
        @elseif (strpos($key, $routePrefix) === 0)
            <?php $tmpURL = route(substr($key, strlen($routePrefix))); ?>
        @else
            <?php $tmpURL = route('admin_index', array($key)); ?>
        @endif
        <a href="{{$tmpURL}}">
            <i class="fa fa-magic"></i>
            <span class="nav-label">{{$item}}</span>
        </a>
    </li>
@endif
