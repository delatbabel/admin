# Source Code Documentation

This directory contains the main source code for Delatbabel Admin.  There is documentation in the source code (as docblocks) and also linked from here.

# Source Code Overview

- [Introduction](/src/introduction.md)
- [Contributing](/src/contributing.md)
- [Style Guide](/src/style-guide.md)
- [License](/src/license.md)

# Classes and Namespaces -- API Documentation

The API documentation (documentation built from the docblocks of all classes and functions) can be built from the parent directory by running this script:

    ./makedoc.sh

This will make a documents/main directory in your source tree. Point your web browser at that.  That is where the documentation for all of the classes and namespaces is to be found.

The folllowing is an overview of what is in each namespace and the top level classes

## Classes (in the Delatbabel\Admin Namespace)

These classes are outside the namespaces listed below.

### AdminServiceProvider

Service providers are the central place of all Laravel application bootstrapping. This bootstraps the Delatbabel Admin panel.

This also creates the necessary routes in Laravel's route table, which can be seen in your application using:

    php artisan route:list

### Menu

This class produces the site menu from the site configuration.  The menu is in turn rendered by the sidebar layout view.

### Validator

This class extends the [Laravel Validator](https://laravel.com/docs/5.1/validation) and provides some additional validation rules.

## Namespaces and Directories

### Delatbabel\Admin\Actions

These classes manage the CRUD actions taken on a database record.

### Delatbabel\Admin\Config

These classes manage the configuration that drives Delatbabel.  See [configuration documentation](/docs/configuration.md).

### Delatbabel\Admin\DataTable

These classes manage interfacing with tables through Laravel model classes.

### Delatbabel\Admin\Fields

These classes manage the different field types -- defining defaults and validation rules for each, etc.

### Delatbabel\Admin\Helpers

This contains the viewComposers helper which associates the internally generated view data with the views, using Laravel's View::composer facade.  See [View Composers](https://laravel.com/docs/5.1/views#view-composers)

### Delatbabel\Admin\Http\Controllers

This contains the controller class AdminController which is the main controller class for all Delatbabel admin requests.  It handles all of the actions for managing the data models.

### Delatbabel\Admin\Http\Middleware

There are 4 middleware classes here that perform validation and permission checking for the controller requests.
