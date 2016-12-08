@if (is_array($item))
    <li>
        <a href="#">
            <i class="fa fa-sitemap"></i>
            <span class="nav-label">{{$key}} </span>
            <span class="fa arrow"></span>
        </a>
        <ul class="nav nav-second-level collapse">
            @foreach ($item as $k => $subitem)
                <?php echo view("adminlayouts.menu_item", array(
                    'item'           => $subitem,
                    'key'            => $k,
                    'settingsPrefix' => $settingsPrefix,
                    'pagePrefix'     => $pagePrefix
                ))?>
            @endforeach
        </ul>
    </li>
@else
    <li>
        @if (strpos($key, $settingsPrefix) === 0)
            <?php $tmpURL = route('admin_settings', array(substr($key, strlen($settingsPrefix)))); ?>
        @elseif (strpos($key, $pagePrefix) === 0)
            <?php $tmpURL = route('admin_page', array(substr($key, strlen($pagePrefix)))); ?>
        @else
            <?php $tmpURL = route('admin_index', array($key)); ?>
        @endif
        <a href="{{$tmpURL}}">
            <i class="fa fa-magic"></i>
            <span class="nav-label">{{$item}}</span>
        </a>
    </li>
@endif
