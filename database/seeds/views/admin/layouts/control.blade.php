{{-- Control Sidebar --}}
@inject('objects', 'Delatbabel\ViewPages\Services\VobjectService')
<?php
$current_url = Route::current()->getUri();
if (strpos($current_url, '{model}') !== false) {
    $current_model = Route::current()->model;
    $current_url   = str_replace('{model}', Route::current()->model, Route::current()->getUri());
}
$help_object = 'help_' . str_replace(['/', '{id}'], ['_', 'edit'], $current_url);
$help_text   = $objects->make($help_object);
?>
<aside class="control-sidebar control-sidebar-dark">
    {{-- Tab panes --}}
    <div class="tab-content">
        {{-- Home tab content --}}
        <div class="tab-pane active" id="control-sidebar-home-tab">
            @if (empty($help_text))
                <h3 class="control-sidebar-heading">No Help Available</h3>
                <p>Create some help text for {{ $help_object }}</p>
            @else
                <!--help for {{ $help_object }} -->
                {!! $objects->make($help_object) !!}
            @endif
        </div>{{-- /.tab-pane --}}
    </div>
</aside>{{-- /.control-sidebar --}}
{{-- Add the sidebar's background. This div must be placed
     immediately after the control sidebar --}}
<div class='control-sidebar-bg'></div>
