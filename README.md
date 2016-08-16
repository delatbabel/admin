
# DDPro Administrator Panel

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

The AdminLTE compatible views have been created and are working.  The forms still use knockout 2.2 and the old CSS
styles from FrozenNode, the forms should be converted to bootstrap compatible styles.

Unit tests are not done yet.

## Requirement

The requirements that we had are for a flexible, pluggable, [AdminLTE](https://almsaeedstudio.com/preview) based administration panel.

Functionality:

* A dashboard of some kind. This will be built in [AdminLTE](https://almsaeedstudio.com/preview) and will allow incorporation of the various widgets available -- tables, graphs, etc.
* Auto draw menus, etc. This has already been coded as [Nested Menus](https://github.com/delatbabel/nestedmenus)
* Modules can be plugged in for CRUD of various database features. Start with a paged list of objects then click on one to edit, click on a + button to add, etc. This should not require coding a lot of extra controllers to handle each object type, there should be one set of resource controllers that can handle the crud endpoints for most object types using the basic resource controller methods, using database / config generated forms. There may be some custom object types that require their own controllers but these should be relatively few.
* Auto generation of forms using a Form Builder. The Form Builder should be a separate component that can be pulled into the Admin Panel using composer.

## Task Plan and Progress

We used [FrozenNode's Laravel-Administrator](https://github.com/FrozenNode/Laravel-Administrator) as a starting point. There are a number of changes that we will need to make so we will not fork the repo, rather we will build a new repo. A summary of the differences and similarities are as follows:

* All views need to be updated to [AdminLTE](https://almsaeedstudio.com/preview) views, use those in omnigate repository as an example.  **DONE**.
* All views need to be stored in the database as per delatbabel/viewpages so that they become editable.  **TODO**
* CSS will need to be changed to [AdminLTE](https://almsaeedstudio.com/preview) compatible CSS.  **TODO**
* Try to keep the back end functionality the same -- same format for the model configuration, etc. That means that many of the back end classes from Laravel-Administrator should be able to be ported directly across. The only change might be that I may want to store the model configuration in the database and make it editable but there should be a migration script that picks up any existing model configuration and imports it.  **TODO**.
* Switch coding standard to PSR-2 and PSR-4. There are numerous non-compliances in FrozenNode.  **DONE**
* Fix bugs. PhpStorm should not mark any of our code as red and should mark as little as possible of it as yellow.  **IN PROGRESS**
* Fix docblocks. Use apigen standard. **IN PROGRESS**
* Need to add some additional field types. In particular HTML (there is an inline HTML editor out there) and JSON blob (array or structure -- there is an inline JSON editor out there).  **TODO**
* Build a new example application repository.  **DONE**

## Documentation

Usage and interfacing documentation is in the [docs](/docs/README.md) directory.

Source code documentation is in the [src](/src/README.md) directory.

Documentation for the resources (views) is in the [resources](/resources/README.md) directory.

Documentation for the assets (public files) is in the [public](/public/README.md) directory.
