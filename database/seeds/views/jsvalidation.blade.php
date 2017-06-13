<script>
    jQuery(document).ready(function(){

        $("<?php echo $validator['selector']; ?>").validate({
            errorElement: 'span',
            errorClass: 'help-block error-help-block',

            errorPlacement: function(error, element) {
                if( element.is('select') && element.hasClass('select2-hidden-accessible')) {
                    element.parent().append(error);
                } else {
                    if (element.attr("type") == "radio") {
                        error.insertAfter(element.parents('div').find('.radio-list'));
                    }
                    else if (element.attr("type") == "checkbox") {
                        error.insertAfter(element.parents('label'));
                    }
                    else if (element.attr("type") == "file" && element.parents('div[class*="_imageupload"]').length) { // Image upload
                        error.insertAfter(element.parents('div[class*="_imageupload"]'));
                    }
                    else {
                        if(element.parent('.input-group').length) {
                            error.insertAfter(element.parent());
                        } else {
                            error.insertAfter(element);
                        }
                    }
                }
            },
            highlight: function(element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error'); // add the Bootstrap error class to the control group
            },

            ignore: [],


            /*
             // Uncomment this to mark as validated non required fields
             unhighlight: function(element) {
             $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
             },
             */
            success: function(element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // remove the Boostrap error class from the control group
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
        })
    })
</script>
