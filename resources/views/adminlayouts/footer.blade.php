<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        {{ Config::get('site.footer') }}
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2016 <a href="{{ Config::get('site.company_url') }}">{{ Config::get('site.company_name') }}</a>.</strong> All rights reserved.
</footer>
