var AdminLTEOptions = {
    //Bootstrap.js tooltip
    enableBSToppltip: false
};
angular.module('myApp', [], function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
}).controller("MyController",function(){});

angular.module('myApp').directive('select2', function() {
    return {
        link: function(scope, element, attr) {
            jQuery(element[0]).select2({
                dropdownAutoWidth: true,
                width: '100%'
            }).on('change', function() {
                $(this).valid();
            });
        }
    };
});

angular.module('myApp').directive('imageUpload', function() {
    return {
        link: function(scope, element, attr) {
            jQuery(element[0]).imageupload();
        }
    };
});

angular.module('myApp').directive('datePicker', function () {
    return {
        restrict: "C",
        scope: {
            dateFormat: "@",
            minDate: "@"
        },
        link: function (scope, elem, attr) {
            jQuery(elem[0]).datepicker({
                dateFormat: scope.dateFormat,
                minDate: scope.minDate,
                onSelect: function () {
                    $(this).trigger("keyup");
                }
            });
        }
    };
});

angular.module('myApp').directive('wysiwyg', function() {
    return {
        restrict: "A",
        link: function (scope, elem, attr) {
            CKEDITOR.replace(elem[0], {
                uiColor: '#CCEAEE',
                allowedContent: true,
                enterMode : CKEDITOR.ENTER_BR,
                shiftEnterMode: CKEDITOR.ENTER_P,
                autoParagraph: false,
                filebrowserBrowseUrl: '/sysadmin/files/ckeditor',
                filebrowserImageUploadUrl: '/admin/file_upload',
                extraPlugins: 'uploadimage',
                uploadUrl: '/admin/file_upload'
            });
        }
    };
});
