# Resources

This directory contains the views that are used by the Admin panel.

Inside views are the base AdminLTE views.  These will need to be expanded to include all of the
functionality of the views in old-views.  Eventually these views will need to be imported into
the database using the service provider (see delatbabel/viewpages).

## views

Inside views are the views that have been created, based on either the AdminLTE templates or some
that have been copied across from FrozenNode.

### How Do The Edit Forms Get Created?

The views are created from Laravel blade templates.  This replaces the knockout.js method used in
earlier versions.

The data sent up to the front end is all communicated in the adminData JS array created starting at
line 17 of views/index.php.  This is the main view for any of the data models.

The adminData array contains an element edit_fields which is populated from the view element
$arrayFields which is created in the view composer (src/Helpers/viewComposers.php) from calling
$fieldFactory->getEditFieldsArrays() (when editing a model, $fieldFactory will be an object of class
Delatbabel\Admin\Fields\Factory).

The forms themselves POST JSON data to, and receive JSON responses from, the endpoints in
src/Http/Controllers/AdminController, meaning that all of the form creation, posting, etc,
functionality is handled on the front end in JS.  The JS code to do this is mostly contained
in public/js/admin.js.  For example, see the function saveItem which handles the POST of data
to the back end when saving an item being edited.
