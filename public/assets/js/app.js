var AdminLTEOptions = {
    //Bootstrap.js tooltip
    enableBSToppltip: false
};
angular.module('myApp', ['ng.jsoneditor'], function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
}).controller("MyController", function(){
    var vm = this;
    vm.onCategoryChange = function () {
        vm.subCategory = "";
    }
    vm.isShowSubmitButton = function (selectedCatId) {
        if (selectedCatId === vm.CategoryIdHasSubCat && vm.subCategory) {
            return true;
        } else if (selectedCatId && selectedCatId !== vm.CategoryIdHasSubCat) {
            return true;
        }
        return false;
    }
    vm.init = function (catId) {
        vm.CategoryIdHasSubCat = catId;
    }
});

angular.module('myApp').directive('select2', function() {
    return {
        scope: {
            theme: "@",
            multiple: "@",
            maxSelectionLength: "@",
            appendToHiddenField: "@",
            onChange: "&"
        },
        link: function(scope, element, attr) {
            var configObj;
            if (scope.multiple) {
                configObj = {
                    theme: scope.theme || "bootstrap",
                    maximumSelectionLength: scope.maxSelectionLength
                }
            } else {
                configObj = {
                    theme: scope.theme || "default single",
                    dropdownAutoWidth: true,
                    width: '100%'
                };
            }
            jQuery(element[0])
            .removeData()
            .select2(configObj)
            .on('change', function(evt) {
                $(this).valid();
                var selectedOption = $(this).find("option:selected"),
                    companyInfo = selectedOption.data("info");
                if (scope.onChange) {
                    scope.onChange({
                        companyInfo: companyInfo
                    });
                    scope.$apply();
                }
                if (scope.appendToHiddenField) {
                    fillValueToHiddenField(companyInfo);
                }
            });

            var initSelectedOption = jQuery(element[0]).find("option:selected"),
                companyInfo = initSelectedOption.data("info");
            if (companyInfo) {
                if (scope.onChange) {
                    scope.onChange({
                        companyInfo: companyInfo
                    });
                }
            }

            function fillValueToHiddenField(selectedData) {
                if (selectedData) {
                    $("input[data-fieldname]").each(function (index, ele) {
                        var $ele = $(ele);
                            fieldName = $ele.data("fieldname").split(".");
                        if (fieldName.length === 2) {
                            if (selectedData[fieldName[0]]) {
                                $ele.val(selectedData[fieldName[0]][fieldName[1]]);
                            } else {
                                $ele.val("");
                            }
                        } else {
                            $ele.val(selectedData[fieldName[0]] || "");
                        }
                    });
                }
            }
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
            minDate: "@",
            showOn: "@",
            calendarType: "@"
        },
        link: function (scope, elem, attr) {
            if (scope.calendarType === "bootstrap") {
                $(elem[0]).closest(".date").datetimepicker({
                    allowInputToggle: true,
                    showClose: true,
                    format: scope.dateFormat.toUpperCase() || "DD/MM/YYYY"
                });
            } else {
                jQuery(elem[0]).datepicker({
                    showOn: scope.showOn,
                    dateFormat: scope.dateFormat,
                    minDate: scope.minDate,
                    onSelect: function () {
                        $(this).trigger("keyup");
                    }
                });
            }
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

angular.module('myApp').directive('selectize', function() {
    return {
        link: function(scope, element, attr) {
            jQuery(element[0]).selectize({
                create: true,
                sortField: 'text'
            }).on('change', function() {
                $(this).valid();
            });
        }
    };
});

angular.module('myApp').directive('tableSortable', function() {
    return {
        restrict: 'AC',
        scope: {
            ngUpdate: "&"
        },
        link: function(scope, element, attr) {
            jQuery(element[0]).find("tbody").sortable({
                placeholder: "ui-state-highlight",
                update: function (event, ui) {
                    var itemId = ui.item.find("td").eq(0).text(),
                        prev_sibling_id = ui.item.prev().find("td").eq(0).text(),
                        next_sibling_id = ui.item.next().find("td").eq(0).text(),
                        params = {
                            item_id: itemId
                        };
                    if (prev_sibling_id) {
                        params.prev_sibling_id = prev_sibling_id;
                    }
                    if(next_sibling_id) {
                        params.next_sibling_id = next_sibling_id;
                    }
                    scope.ngUpdate({
                        params: params
                    });
                }
            });
        }
    };
});