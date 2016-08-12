# Staging

This is where things go while we are migrating them into the main admin framework.

## config

The config/administrator.php file contains the base config for the admin panel.  Eventually
this will move to be inside the database (see delatbabel/site-config).

TODO: Import this as database driven config and have a config editor.

## views

Inside views are the base AdminLTE views.  These will need to be expanded to include all of the
functionality of the views in old-views.  Eventually these views will need to be imported into
the database using the service provider (see delatbabel/viewpages).

TODO: Write a migration script that imports these views from staging into the database.

### old-views

Inside old-views are the views that have been copied across from FrozenNode.  These use
knockout.js of which version 2.2.0 (quite old) is included in public/js/knockout.  It would
probably be best to update the version of knockout.js

The views in here use knockout.js' containerless binding syntax.  It's hard to find documentation
on this because all of the knockout.js examples use the native binding.  Examples of each are
as follows.

See this post for some useful information:
http://stackoverflow.com/questions/17068094/comment-foreach-binding-vs-foreach-binding-in-knockoutjs

#### Containerless

```js
<!-- ko foreach: customer -->
   <div data-bind="text: id" />
<!-- /ko -->
```

#### Native

```js
<div data-bind="foreach: customer">
    <div data-bind="text: id" />
</div>
```

## lang

These are the translation files for the old views.  Ideally it would be good to keep these but
in the long term we may migrate all of this to gettext and .po files.

## examples

These are examples copied from FrozenNode.  They need to be checked over.

## tests

These are tests copied from FrozenNode.  They need to be changed for the new namespaces.
