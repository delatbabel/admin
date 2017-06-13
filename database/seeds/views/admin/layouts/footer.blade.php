<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        {{ config('administrator.footer_message') }}
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ config('administrator.company_url') }}">{{ config('administrator.company_name') }}</a>.</strong> All rights reserved.
</footer>
