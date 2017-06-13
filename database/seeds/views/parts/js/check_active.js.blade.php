// Set active state on menu element
var current_url = "{{ session('current_url') }}";
$("ul.sidebar-menu li a").each(function() {
if ($(this).attr('href').startsWith(current_url) || current_url.startsWith($(this).attr('href')))
{
    $(this).parents('li').addClass('active');
}
});
