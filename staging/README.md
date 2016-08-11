# Staging

This is where things go while we are migrating them into the main admin framework.

## config

The config/administrator.php file contains the base config for the admin panel.  Eventually
this will move to be inside the database (see delatbabel/site-config).

## views

Inside views are the base AdminLTE views.  These will need to be expanded to include all of the
functionality of the views in old-views.  Eventually these views will need to be imported into
the database using the service provider (see delatbabel/viewpages).

Inside old-views are the views that have been copied across from FrozenNode.

TODO: Write a migration script that imports these views from staging into the database.

## lang

These are the translation files for the old views.  Ideally it would be good to keep these but
in the long term we may migrate all of this to gettext and .po files.
