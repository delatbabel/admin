jQuery(document).ready(function(){

    $("<?php echo $validator['selector']; ?>").validate({
        errorElement: 'span',
        errorClass: 'help-block error-help-block',
        errorPlacement: function(error, element) {
            if( element.is('select') && element.hasClass('select2-hidden-accessible')) {
                element.parent().append(error).addClass("selectContainer");
                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if ( !$( element ).next( "i" )[0]) {
                    $(element).closest('.form-group').addClass('has-feedback');
                    $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element );
                }
            } else {
                if (element.attr("type") == "radio") {
                    error.insertAfter(element.parents('div').find('.radio-list'));
                    // Add the span element, if doesn't exists, and apply the icon classes to it.
                    if ( !$( element ).next( "i" )[0]) {
                        $(element).closest('.form-group').addClass('has-feedback');
                        $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element );
                    }
                }
                else if (element.attr("type") == "checkbox") {
                    // error.insertAfter(element.parents('label'));
                    element.parents(".form-group").append(error.addClass('col-md-10'));
                    // Add the span element, if doesn't exists, and apply the icon classes to it.
                    if ( !$( element ).find( "i" )[0]) {
                        $(element).closest('.form-group').addClass('has-feedback');
                        $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertBefore( element.parent("label") );
                    }
                }
                else if (element.attr("type") == "file") { // Image upload
                    if (element.parents('div[class*="_imageupload"]').length) {
                        error.insertAfter(element.parents('div[class*="_imageupload"]'));
                        // Add the span element, if doesn't exists, and apply the icon classes to it.
                        if ( !$( element ).next( "i" )[0]) {
                            $(element).closest('.form-group').addClass('has-feedback');
                            $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element.parent() );
                        }
                    } else {
                        var $imageControlGroup = $(element).closest(".image-control-group"),
                            hasErrorBlock = $imageControlGroup.find(".error-help-block").length > 0;
                        if ($imageControlGroup.length > 0 && hasErrorBlock) {
                            return;
                        }
                        error.insertAfter(element);
                    }
                }
                else if (element.is("textarea")) {
                    element.parent().append(error);
                     if ( !$( element ).next( "i" )[0]) {
                        $(element).closest('.form-group').addClass('has-feedback');
                        $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element );
                    }
                }
                else {
                    if(element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                        // Add the span element, if doesn't exists, and apply the icon classes to it.
                        if ( !$( element ).next( "i" )[0]) {
                            $(element).closest('.form-group').addClass('has-feedback');
                            $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element );
                        }
                    } else {
                        error.insertAfter(element);
                        // Add the span element, if doesn't exists, and apply the icon classes to it.
                        if ( !$( element ).next( "i" )[0]) {
                            $(element).closest('.form-group').addClass('has-feedback');
                            $( "<i class='glyphicon glyphicon-remove form-control-feedback'></i>" ).insertAfter( element );
                        }
                    }
                }
            }
        },
        highlight: function(element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error'); // add the Bootstrap error class to the control group

            $(element).closest('.form-group').find( "i" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
        },

        ignore: [],



         // Uncomment this to mark as validated non required fields
         unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            $(element).closest('.form-group').find( "i" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
         },

        success: function(element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            // remove the Boostrap error class from the control group
            // Add the span element, if doesn't exists, and apply the icon classes to it.
            if ( !$( element ).find( "i" ) && $(element).attr("type") !== "file") {
                $( "<i class='glyphicon glyphicon-ok form-control-feedback'></i>" ).insertAfter( $( element ) );
            }
        },

        focusInvalid: false, // do not focus the last invalid input
        <?php if (Config::get('jsvalidation.focus_on_error')): ?>
        invalidHandler: function(form, validator) {

            if (!validator.numberOfInvalids())
                return;

            $('html, body').animate({
                scrollTop: $(validator.errorList[0].element).offset().top
            }, <?php echo Config::get('jsvalidation.duration_animate') ?>);
            $(validator.errorList[0].element).focus();

        },
        <?php endif; ?>

        rules: <?php echo json_encode($validator['rules']); ?>
    });
    var $wysiwyg = $("[wysiwyg]");
    if ($wysiwyg && $wysiwyg.length > 0) {
        $wysiwyg.rules("add", {
            required: function (ele) {
                if (CKEDITOR) {
                    CKEDITOR.instances[$(ele).attr("name")].updateElement();
                }
            }
        });
    }
});
// Make select2 trigger validation on change event
$(".select2-hidden-accessible").select2().on('change', function() {
    $(this).valid();
});
