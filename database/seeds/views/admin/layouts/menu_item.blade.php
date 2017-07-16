@inject('user', 'DDPro\Admin\Services\User')
<?php $sentinelUser = $user->getUser() ?>
{{-- draw menu item key = {{ print_r($key, true) }}, item = {{ print_r($item, true) }} --}}
@if (is_array($item))
    @if (is_numeric($key))
        @foreach ($item as $subkey => $subitem)
            @include("admin.layouts.menu_item", [
                    'key'            => $subkey,
                    'item'           => $subitem,
                    'settingsPrefix' => $settingsPrefix,
                    'pagePrefix'     => $pagePrefix,
                    'routePrefix'    => $routePrefix,
            ])
        @endforeach
    @else
        @if ($sentinelUser->hasAnyAccess(['menu.' . str_slug($key), 'all.all']))
            <li class="treeview">
                <a href="#"><span>{{$key}}</span> <i class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        @foreach ($item as $subkey => $subitem)
                            @include("admin.layouts.menu_item", [
                                    'key'            => $subkey,
                                    'item'           => $subitem,
                                    'settingsPrefix' => $settingsPrefix,
                                    'pagePrefix'     => $pagePrefix,
                                    'routePrefix'    => $routePrefix,
                            ])
                        @endforeach
                    </ul>
            </li>
        @endif
    @endif
@else
    {{-- test for access to menuitem.{{ $key }} --}}
    @if ($sentinelUser->hasAnyAccess(['menuitem.' . $key, 'all.all']))
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
@endif
{{--

This view is used to draw the sidebar menu.

The use and data for this view is quite complex so I will explain it here.

The menu structure looks like this:

Array
(
    [Members] => Array
        (
            [companies] => Companies
            [contacts] => Contacts
            [address] => Addresses
        )
    [Admin Access] => Array
        (
            [0] => Array
                (
                    [route.users.index] => Admins
                )

            [1] => Array
                (
                    [route.roles.index] => Roles
                )

        )
... etc

The first time this is called, the call is with the following parameters:

$key:  Members
$item:  Array( ... )

This passes the @if (is_array($item)) check which then triggers the @foreach loop on $item as $subkey => $subitem
which will cause the view to be loaded again, this time with each of the following parameter pairs:

$key: companies
$item Companies
... etc

Each one of these calls will fail the @if (is_array($item)) check which means that the lower half of the view
is executed, the one where access to menu.$key is checked, and then the actual menu item is drawn between
the <li> ... </li> block.

The tricky part is when the Admin Access array is hit.  This will essentially cause each array item to draw
its own submenu, but because there's a check for @if (! is_numeric($key)) immediately after that, the submenu
<ul> ... </ul> border is not drawn and therefore each item appears to be a menu item of the parent menu.

Permissions checking is done by the check @if ($sentinelUser->hasAnyAccess(['menuitem.' . $key, 'all.all']))
The permission all.all grants access to everything, but if the user doesn't have that then they only see
the menu item if they have the permission menuitem.$key

Permission checking on the parent menu is done by checking the permission for 'menu.' + the slug of the menu
key.  Again the all.all permission grants permission to everything.

--}}
