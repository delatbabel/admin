
# DDPro Administrator Panel

[![Build Status](https://travis-ci.org/ddpro/admin.png?branch=master)](https://travis-ci.org/ddpro/admin)
[![StyleCI](https://styleci.io/repos/64189152/shield)](https://styleci.io/repos/64189152)
[![Latest Stable Version](https://poser.pugx.org/ddpro/admin/version.png)](https://packagist.org/packages/ddpro/admin)
[![Total Downloads](https://poser.pugx.org/ddpro/admin/d/total.png)](https://packagist.org/packages/ddpro/admin)

This is currently a work in progress to build a new Laravel 5 compatible administration panel based on [AdminLTE](https://almsaeedstudio.com/preview)

First some history. Sitepro 3 (which was a private framework built on top of Laravel 3) has an administration panel. We considered porting this across to Laravel 5 and then decided that this was a bad idea for a bunch of reasons.

Instead what we have decided to do is to build a new panel with most of the same ideas as the Sitepro admin panel, but in Laravel 5.

Sitepro 3 is a bundle in Laravel 3. Since Laravel 4 and later did away with the concept of bundles in favour of packages, everything works somewhat differently. It's not really possible to port a Laravel 3 bundle to a Laravel 5 package because Laravel 3 bundles were very much more invasive -- they could take over the routing system, for example.

Instead of this what I have decided to do is to focus on building a new panel. However since there are already existing candidate starting starting points for building an administration panel, we don't plan to build everything from scratch. Instead we will adapt one of the existing admin panels and code it to suit our applications.

Currently the leading candidate for this is [Laravel-Administrator by FrozenNode](https://github.com/FrozenNode/Laravel-Administrator) -- the main limitation being that it does not support [AdminLTE](https://almsaeedstudio.com/preview).

Instead of forking the existing package I decided to create a new package because the amount of changes that would
be required to convert the code and views to use AdminLTE are too great for a set of git pull requests.

## Status

This is working, basically.  There are a lot of things still to do.  See the **TODO** and **FIXME** notes throughout the
code.

The controller endpoints for managing models are all OK.  The endpoints for working on settings haven't been done yet.

The code has been brought across from AdminLTE, mostly unchanged except for minor refactoring.  The code has been
converted to PSR-2 standards.

There is a [working example application](https://github.com/ddpro/example).

The AdminLTE compatible views have been created and are working.  The forms still use knockout which has been updated from version 2.2 to version 3.4, and also use some of the old CSS styles from FrozenNode, the forms should be converted to bootstrap compatible styles.

## Requirement

The requirements that we had are for a flexible, pluggable, [AdminLTE](https://almsaeedstudio.com/preview) based administration panel.

Functionality:

* A dashboard of some kind. This will be built in [AdminLTE](https://almsaeedstudio.com/preview) and will allow incorporation of the various widgets available -- tables, graphs, etc.
* Auto draw menus, etc. This has already been coded as [Nested Menus](https://github.com/delatbabel/nestedmenus)
* Modules can be plugged in for CRUD of various database features. Start with a paged list of objects then click on one to edit, click on a + button to add, etc. This should not require coding a lot of extra controllers to handle each object type, there should be one set of resource controllers that can handle the crud endpoints for most object types using the basic resource controller methods, using database / config generated forms. There may be some custom object types that require their own controllers but these should be relatively few.
* Auto generation of forms using a Form Builder. The Form Builder should be a separate component that can be pulled into the Admin Panel using composer.

## Task Plan and Progress

We used [FrozenNode's Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator) as a starting point. There are a number of changes that we will need to make so we will not fork the repo, rather we will build a new repo. A summary of the differences and similarities are as follows:

* Views changed to match [AdminLTE](https://almsaeedstudio.com/preview) **DONE**
* Views will be stored in the database as per delatbabel/viewpages so that they become editable.  **TODO**
* CSS will need to be changed to [AdminLTE](https://almsaeedstudio.com/preview) compatible CSS.  **IN PROGRESS**
* The class structure and the back end functionality is the same.  e.g. the model configuration files are the same.
* The only change might be that I may want to store the model configuration in the database and make it editable but there should be a migration script that picks up any existing model configuration and imports it.  **TODO**.
* Coding standard has been switched to PSR-2 and PSR-4. **DONE**
* Fixed bugs. Especially go through the existing issues on the FrozenNode github issues tracker and see what still needs doing. **IN PROGRESS**.
* Fixed docblocks using the apigen standard. **DONE**
* There is a [new example application](https://github.com/ddpro/example). **DONE**

### TODO List -- Front End

This is the list of front end changes that need doing:

* Conversion of the views from FrozenNode to AdminLTE is not complete. See the files in resources/views
    * adminmodel/* templates have been converted to blade format but still use the original FrozenNode CSS classes.  Ideally the CSS classes would be converted to AdminLTE compatible classes and the classes used in the dynamically generated forms would be converted to bootstrap compatible form field classes.  This requires rework of some of the JS code that manipulates the form DOM objects.
* Check over all other CSS classes to ensure that they are compatible with AdminLTE.
* The front end form generation uses knockout.js version 3.4.0.  Switch form generation to being done at the back end using the blade template with form population done using jquery or angular.js.  Ideally this would allow for more form widgets including multi-tabbed forms.  At least we need an HTML editor, and a JSON editor (for editing arrays and structures stored as JSON blobs in the database).
* Document the bower configuration and requirements.

### TODO List -- Back End

This is the list of back end changes that need doing:

* Get rid of the closures in the config so that the config can be serialised and stored in the database.
* Store views in the database as per delatbabel/viewpages.
* Store configuration in the database as per delatbabel/site-config.
* Store the model configuration in the database and have a model configuration editor for the admin.
* Go through the remaining TODOs and FIXMEs in the code.
* Unit tests have about 60% coverage, with no coverage on the controller.  There are also some commented out tests that fail.

## Documentation

Usage and interfacing documentation is in the [docs](/docs/README.md) directory.

Source code documentation is in the [src](/src/README.md) directory.

Documentation for the resources (views) is in the [resources](/resources/README.md) directory.

Documentation for the assets (public files) is in the [public](/public/README.md) directory.
