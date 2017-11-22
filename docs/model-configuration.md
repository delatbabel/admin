# Model Configuration

- [Introduction](#introduction)
- [Examples](#examples)
- [Options](#options)
    - [Title](#title) *(required)*
    - [Single](#single) *(required)*
    - [Model](#model) *(required)*
    - [Server Side](#server_side)
    - [Columns](#columns) *(required)*
    - [Edit Fields](#edit-fields) *(required)*
    - [Controller Handler](#controller-handler)
    - [Filters](#filters)
    - [Query Filter](#query-filter)
    - [Permission](#permission)
    - [Action Permissions](#action-permissions)
    - [Custom Actions](#custom-actions)
    - [Global Custom Actions](#global-custom-actions)
    - [Form Request Option](#form-request-option)
    - [Validation Rules](#validation-rules)
    - [Validation Messages](#validation-messages)
    - [Sort](#sort)
    - [Form Width](#form-width)
    - [Link](#link)
    - [Custom Controller](#custom-controller)
    - [Custom Table View](#custom-table-view)
    - [Custom Form View](#custom-form-view)

<a name="introduction"></a>
## Introduction

Any Eloquent model (or any object that ultimately extends from an Eloquent model) can be represented by a model configuration file. These files can be kept anywhere in your application directory structure. All you do is provide the path to their location in the `app/config/packages/ddpro/admin/administrator.php` config file with the [`model_config_path`](/docs/configuration.md#model-config-path) option. The **file names** of these files correspond to the values supplied in the [`menu`](/docs/configuration.md#menu) option, also in the `administrator.php` config.

> **Note**: These are also the **uris** for each model in the admin interface.

There are several required fields that must be supplied in order for a model config file to work. Apart from those, you can also define a number of optional fields that help you customize your admin interface on a per-model basis. For instance, if one of your models needs a WYSIWYG field, you'll probably want the edit form to be wider than the default width. All you would have to do is set the `form_width` option in that model's config.

<a name="examples"></a>
## Examples

For some example config files, check out the `/examples` directory on [Administrator's GitHub repo](https://github.com/ddpro/admin/tree/master/staging/examples).

<a name="options"></a>
## Options

Below is a list of all the available options. Required options are marked as *(required)*:

<a name="title"></a>
### Title *(required)*

    /**
     * Model title
     *
     * @type string
     */
    'title' => 'Collection',

This is the title of the model used in the menu and as the model's primary title.

<a name="single"></a>
### Single *(required)*

    /**
     * The singular name of your model
     *
     * @type string
     */
    'single' => 'collection',

This is used anywhere in Administrator where a singular name must be used. For example, the button that starts the creation of a new item is built using this. In this case it would be "New collection".

<a name="model"></a>
### Model *(required)*

    /**
     * The class name of the Eloquent model that this config represents
     *
     * @type string
     */
    'model' => 'Collection',

This must be the fully-qualified class name of your Eloquent model. In this case I've got a `Collection` model. If you are namespacing your models, you'll want to provide the full namespaced class name.

<a name="server_side"></a>
### Server Side

    /**
     * Whether to do table pagination on the server side or client side (default)
     *
     * @type boolean
     */
    'server_side' => true,

If this is set to *true* then the table display will only show the first *n* rows (where *n* is selected in the **Show n entries** pulldown above the table).  Moving from page to page through the table will fetch an additional *n* rows for each page.  This is useful for large data sets.  If this is set to *false* then all of the rows for the table are fetched at once, and sorting, searching, and pagination are done on the client side.

Setting this to *true* also shows the **Filters** form.  Setting this to *false* hides the filters form and instead shows a single **Search** field above the table which can be used for searching and filtering of the table data.

<a name="columns"></a>
### Columns *(required)*

    /**
     * The columns array
     *
     * @type array
     */
    'columns' => array(
        'ordering' => array(
            'title' => 'Order'
        ),
        'image' => array(
            'title' => 'Image',
            'output' => '<img src="/uploads/homepagesliders/resize/(:value)" height="100" />',
        ),
        'link' => array(
            'title' => 'Link',
            'output' => '<a href="(:value)" target="_blank">(:value)</a>',
        ),
        'product_name' => array(
            'title' => 'Product',
            'relationship' => 'product',
            'select' => '(:table).name',
        )
    ),

These are the columns that will be displayed in your result set. As you can see above, you can customize this fairly extensively by modifying the output, performing custom SQL selects, or by pulling in relational information for this field based on its Eloquent relationships.

> For a detailed description of all the column options, see the **[column docs](/docs/columns.md)**

<a name="edit-fields"></a>
### Edit Fields *(required)*

    /**
     * The edit fields array
     *
     * @type array
     */
    'edit_fields' => array(
        'name' => array(
            'title' => 'Name',
            'type' => 'text'
        ),
        'product' => array(
            'title' => 'Product',
            'type' => 'relationship'
        ),
        'image' => array(
            'title' => 'Image (1200 x 1314)',
            'type' => 'image',
            'naming' => 'random',
            'location' => 'public/uploads/products/originals/',
            'size_limit' => 2,
            'sizes' => array(
                array(1200, 1314, 'crop', 'public/uploads/products/resize/', 100),
                array(452, 495, 'landscape', 'public/uploads/products/detail/', 100),
            )
        )
    ),

The `edit_fields` array lets you define the editable fields for a model. There are many types of fields, including most primitive types and more complex fields like relationships. If you want to represent a field on the model's table, the item's key in the `edit_fields` array should be the attribute name. If the column you want to show is either a custom select or a relationship column, the item's key will be the column's alias.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/edit-form.png" />

> For a detailed description of all the edit field types and options, see the **[field docs](/docs/fields.md)**

<a name="controller-handler"></a>
### Controller Handler
    controller_handler = AdminModelController::class

The Controller Handler option let you custom your controller for template, working process. It must be extend from ` AdminModelController` 
    
<a name="filters"></a>
### Filters

    /**
     * The filter fields
     *
     * @type array
     */
    'filters' => array(
        'id',
        'name' => array(
            'title' => 'Name',
        ),
        'date' => array(
            'title' => 'Date',
            'type' => 'date',
        ),
    ),

The `filters` array lets you define filters for a model. These work just like the `edit_field` items, except there are fewer filterable field types. For example, you can have an `image` field type in the `edit_fields` array, but you can't provide a `'type' => 'image'` in the `filters` array. You can, however, filter by an image's name by setting the field as a `text` field type.

> For a detailed description of all the filter types and options, see the **[filters docs](/docs/fields.md#filters)**

<a name="query-filter"></a>
## Query Filter

    /**
     * The query filter option lets you modify the query parameters before Administrator begins to construct the query. For example, if you want
     * to have one page show only deleted items and another page show all of the items that aren't deleted, you can use the query filter to do
     * that.
     *
     * @type closure
     */
    'query_filter'=> function($query)
    {
        if (!Auth::user()->hasRole('super_admin'))
        {
            $query->whereDeleted(false);
        }
    },

The query filter option lets you define a closure that is run before Administrator constructs the query that will fetch the results for your model. The query builder object is passed to this function, and you can use it to restrict the rows that are visible to the current user. You can use this in conjunction with your auth system as seen above, or you can come up with any reason to use this.

> **Note:** The query builder object passed into the `query_filter` function is unfiltered, but it is already grouped on the current table's primary key field.

<a name="permission"></a>
## Permission

    /**
     * The permission option is the per-model authentication check that lets you define a closure that should return true if the current user
     * is allowed to view this model. Any "falsey" response will result in a 404.
     *
     * @type closure
     */
    'permission'=> function()
    {
        return Auth::user()->hasRole('developer');
    },

The permission option lets you define a closure that determines whether or not the current user can access this model. If this field is provided (it isn't required), the user will only be given access if this resolves to a truthy value. If you return something falsey, it will redirect to your `login_path`. If you return a `Response` or `Redirect` object, it will respect those requests. Returned `Redirect` object will have the login redirect path added to the "with" data.

<a name="action-permissions"></a>
## Action Permissions

    /**
     * The action_permissions option lets you define permissions on the four primary actions: 'create', 'update', 'delete', and 'view'.
     * It also provides a secondary place to define permissions for your custom actions.
     *
     * @type array
     */
    'action_permissions'=> array(
        'delete' => function($model)
        {
            return Auth::user()->has_role('developer');
        }
    ),

Action permissions can be supplied to give you access control over the four primary actions (`create`, `update`, `delete`, and `view`) and any custom actions that you define. None of these options are required and should only be supplied if you want to restrict access. In the above example, only users with role `developer` will be able to delete an item for this model. The keys of the `action_permissions` array should be the names of the actions, and each item should either be an anonymous function that returns either true or false, or simply a boolean value.

<a name="custom-actions"></a>
## Custom Actions

    /**
     * This is where you can define the model's custom actions
     */
    'actions' => array(
        //Ordering an item up
        'order_up' => array(
            'title' => 'Order Up',
            'messages' => array(
                'active' => 'Reordering...',
                'success' => 'Reordered',
                'error' => 'There was an error while reordering',
            ),
            'permission' => function($model)
            {
                return $model->category_id !== 2;
            },
            //the model is passed to the closure
            'action' => function($model)
            {
                //get all the items of this model and reorder them
                $model->orderUp();
            }
        ),

        //Ordering an item down
        'order_down' => array(...),
    ),

You can define custom actions for your model if you want to provide the administrative user buttons to perform custom code. In the above example, there will be two buttons produced that look like this:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/custom-actions.png" />

When the user clicks on either button, the `action` property above is called and passed the relevant Eloquent model.

> For a detailed description of custom actions, see the **[actions docs](/docs/actions.md)**

<a name="global-custom-actions"></a>
### Global Custom Actions

    /**
     * This is where you can define the model's global custom actions
     */
    'global_actions' => array(
        //Create Excel Download
        'download_excel' => array(
            'title' => 'Download XLS',
            'messages' => array(
                'active' => 'Creating the spreadsheet...',
                'success' => 'Spreadsheet created! Downloading now...',
                'error' => 'There was an error while creating the spreadsheet',
            ),
            //the Eloquent query builder is passed to the closure
            'action' => function($query)
            {
                //get all the rows for this query
                $result = $query->get();

                //do something to put it into excel

                //return a download response
                return Response::download($filePath);
            }
        ),
    ),

Global custom actions are buttons that can be pressed at any time on a model's page. In most ways, this works just like regular custom actions. However, instead of the model being passed into the `action` callback function, the query builder is passed in with all the filters already applied (except for the limit/offset).

> For a detailed description of custom actions, see the **[actions docs](/docs/actions.md)**

<a name="form-request-option"></a>
### Form Request Option

    /**
     * The form request to use for validation of the form, based on the Laravel form request class
     *
     * @type string
     */
    'form_request' => 'FormRequestPath',

The `form request` option lets you define a custom [form request](http://laravel.com/docs/validation#form-request-validation) to validate the editable fields on save. The Form Request object must contain a rules array to validate. This field is optional by default.


<a name="validation-rules"></a>
### Validation Rules

    /**
     * The validation rules for the form, based on the Laravel validation class
     *
     * @type array
     */
    'rules' => array(
        'name' => 'required',
        'age' => 'required|integer|min:18',
    ),

The validation rules for your models can be set using the `rules` option. Administrator uses [Laravel's validation](http://laravel.com/docs/validation) to validate your models. If the form is invalid, it will notify the admin user without saving the form.

> For a detailed description of validation, see the **[validation docs](/docs/validation.md)**

<a name="validation-messages"></a>
### Validation Messages

    /**
     * The validation messages for the form, based on the Laravel validation class
     *
     * @type array
     */
    'messages' => array(
        'name.required' => 'The name field is required',
        'age.min' => 'The minimum age is 18 years old',
    ),

The validation messages for your models can be set using the `messages` option. Administrator uses [Laravel's validation](http://laravel.com/docs/validation#custom-error-messages) and the messages should follow the same format as Laravel's custom error messages.

> For a detailed description of validation, see the **[validation docs](/docs/validation.md)**

<a name="sort"></a>
## Sort

    /**
     * The sort options for a model
     *
     * @type array
     */
    'sort' => array(
        'field' => 'name',
        'direction' => 'asc',
    ),

The `sort` option should be an array with two keys: `field` and `direction`. `field` must be a column in the `columns` array. `direction` must be either `asc` or `desc`.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/sorting.png" />

<a name="form-width"></a>
## Form Width

    /**
     * The width of the model's edit form
     *
     * @type int
     */
    'form_width' => 400,

If you set this to any integer value above 285, it will expand the edit field and shrink the columns to fit it. If you set this to `true`, it will default to 500. Playing around with this will let you find the perfect fit for your models.

<a name="link"></a>
## Link

    /**
     * If provided, this is run to construct the front-end link for your model
     *
     * @type function
     */
    'link' => function($model)
    {
        return URL::route('product', array($model->collection()->first()->uri, $model->uri));
    },

If your model has a front-end link, you might want to have a "view item" link at the top of the edit form that pops out to that page. The relevant `$model` is passed into the function so that you can use it to construct the URL. You should return a valid URL string.

<a name="custom-controller"></a>
## Custom Controller

    /**
     * The name of a custom controller for this model
     *
     * @type string
     */
    'controller_handler' => \App\Http\Controllers\MyCustomController::class,

Normally the controller for a model is Delatbabel\Admin\Http\Controllers\AdminModelController however with this
option you can set the controller to any class that extends that class.

The class contains a few functions that are relevant:

    public function item($modelName, $itemId = 0) {}

This function should return the view containing the item data populated into the view ready for
editing.  If $itemId is 0 then this is a new item being created.  This can be used for something
as simple as providing a custom form for the model.

    public function save($modelName, $id = null) {}

This function is the save handler.  It should update or create the model depending on the value of
$id (null to create).  It should return as follows:

    return redirect()->route('admin_index', [$modelName]);

There are other functions in the `AdminModelController` class that can be over-ridden, please read
through the class for further details.

<a name="custom-table-view"></a>
## Custom Table View

    /**
     * The name of a custom table view for this model
     *
     * @type string
     */
    'model_table_view' => 'admin.model.table',

This can be used to provide an alternative view name for the table view.  You can copy the admin.model.table view
and provide customised versions of it for different model classes to suit your needs.

<a name="custom-form-view"></a>
## Custom Form View

    /**
     * The name of a custom form view for this model
     *
     * @type string
     */
    'model_form_view' => 'admin.model.form',

This can be used to provide an alternative view name for the entry/edit form view.  You can copy the admin.model.form view
and provide customised versions of it for different model classes to suit your needs.
