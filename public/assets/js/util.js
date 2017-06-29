$(function () {
	if (CKEDITOR) {
		$.each( CKEDITOR.instances, function(instance) {
		    CKEDITOR.instances[instance].on("instanceReady", function() {
		    	this.document.on("keyup", CK_jQ);
		    	this.document.on("paste", CK_jQ);
		    	this.document.on("keypress", CK_jQ);
		    	this.document.on("change", CK_jQ);
		    });

		    CKEDITOR.instances[instance].on("blur", CK_jQ);
		});
	}
	// Make select2 trigger validation on change event
	$(".select2-hidden-accessible").select2().on('change', function() {
	    $(this).valid();
	});
});

function CK_jQ (evt) {
	CKEDITOR.tools.setTimeout(function ()
    {
    	for(var instance in CKEDITOR.instances) {
    		var $textarea = $("#" + instance);
	    	$("#" + instance).val(CKEDITOR.instances[instance].getData());
			if (evt.name === "blur" && $textarea.data("dirty")) {
				$textarea.valid();
			} else {
				$textarea.data("dirty", true);
		    	$textarea.trigger("keyup");
		    }
	    }
    }, 0);
}