# Resources

This directory contains the views that are used by the Admin panel.

Inside views are the base AdminLTE views.  These will need to be expanded to include all of the
functionality of the views in old-views.  Eventually these views will need to be imported into
the database using the service provider (see delatbabel/viewpages).

## views

Inside views are the views that have been created, based on either the AdminLTE templates or some
that have been copied across from FrozenNode.  These use knockout.js of which version 2.2.0
(quite old) is included in public/js/knockout.  It would be best to either:

* update the version of knockout.js,
* switch to angular.js, or something like schemaform.io, or
* convert to PHP code driven views instead of the current JS driven views

TODO: Write a migration script that imports these views from staging into the database.

TODO: The forms, etc, still use the old styles used by FrozenNode.  These need to be converted
to bootstrap compatible styles.

### How Do The Edit Forms Get Created?

The views use knockout.js to dynamically create the edit forms on the front end, and the PHP code
builds some JS some arrays to communicate to the front end.

The data sent up to the front end is all communicated in the adminData JS array created starting at
line 17 of views/index.php.  This is the main view for any of the data models.

The adminData array contains an element edit_fields which is populated from the view element
$arrayFields which is created in the view composer (src/Helpers/viewComposers.php) from calling
$fieldFactory->getEditFieldsArrays() (when editing a model, $fieldFactory will be an object of class
DDPro\Admin\Fields\Factory).

In turn this edit_fields element is used by the prepareEditFields function in public/js/admin.js starting
around line 1069.

The forms themselves POST JSON data to, and receive JSON responses from, the endpoints in
src/Http/Controllers/AdminController, meaning that all of the form creation, posting, etc,
functionality is handled on the front end in JS.  The JS code to do this is mostly contained
in public/js/admin.js.  For example, see the function saveItem which handles the POST of data
to the back end when saving an item being edited.

The views in here use knockout.js' containerless binding syntax.  It's hard to find documentation
on this because all of the knockout.js examples use the native binding.  Examples of each are
as follows.

See this post for some useful information:
http://stackoverflow.com/questions/17068094/comment-foreach-binding-vs-foreach-binding-in-knockoutjs

### Containerless

```js
<!-- ko foreach: customer -->
   <div data-bind="text: id" />
<!-- /ko -->
```

### Native

```js
<div data-bind="foreach: customer">
    <div data-bind="text: id" />
</div>
```
