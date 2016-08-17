# Staging

This is where things go while we are migrating them into the main admin framework.

## config

The config/administrator.php file contains the base config for the admin panel.  Eventually
this will move to be inside the database (see delatbabel/site-config).

TODO: Import this as database driven config and have a config editor.

## old-views

Inside old-views are the views that have been copied across from FrozenNode.  Updated versions
of these are now in the [resources](/resources/README.md) directory.

## lang

These are the translation files for the old views.  Ideally it would be good to keep these but
in the long term we may migrate all of this to gettext and .po files.

## examples

These are image files remaining from the examples copied from FrozenNode.

## tests

These are tests copied from FrozenNode.  They need to be changed for the new namespaces.

TODO: Move these up to the main area and fix them so they work.
