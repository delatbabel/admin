# Custom Actions

- [Introduction](#introduction)
- [Global Actions](#global-actions)
- [Settings Config](#settings-config)
- [Confirmations](#confirmations)
- [Dynamic Messages](#dynamic-messages)

<a name="introduction"></a>
## Introduction

You can define custom item or global actions in your [model](/docs/model-configuration.md#custom-actions) if you want to provide the administrative user buttons to perform custom code. You can modify an Eloquent model, for perform any other action. A custom item action is part of the `item_actions` array in your config files and it looks like this:

    /**
     * This is where you can define the model's custom actions
     */
    'item_actions' => array(
        'delete' => array(
            'title' => 'Delete',
            'confirmation' => 'Are you sure you want to do this?',
            'messages' => array(
                'active' => 'Deleting...',
                'success' => 'Deleted',
                'error' => 'There was an error while deleting item',
            ),
            'params' => array(
                'action_name' => 'delete'
            ),
            'action' => '\App\Http\Controllers\MyCustomController::itemDelete'
        ),
    ),

The `title` option lets you define the button's label value.

The `confirmation` option text will be displayed in a pop-up to allow the user to confirm or cancel the action.

The `messages` option is an array with three keys: `active`, `success`, and `error`. The `active` key is what is shown to the user as the action is being performed. The `success` key is the success message. The `error` key is the default error message.

The `params` array is passed as part of the request data.  This must include the `action_name` which must match the name of the action.

The `action` item should be a callable function (closure or function name) that gets passed a parameter `$id` containing the ID of the model that the action is to be performed on.

> **Note**: If you want to show a custom error message, return an error string back from the `action` function. If you want to initiate a file download, return a Response::download().

<a name="global-actions"></a>
## Global Actions

You can also create a general action on your model page in the `global_actions` array.

    'global_actions' => array(
        'some_action' => array(
            //action options
        )
    )

These global custom actions are passed an array of ids so that you can do something with multiple objects if you choose to do so. You can also use this to publish all unpublished items, send emails to unnotified users, or really anything you can think of.

<a name="confirmations"></a>
## Confirmations

If you want a confirmation dialog to appear before the action is performed, you can pass in a `confirmation` option for the action:

    'clear_cache' => array(
        'title' => 'Clear Cache',
        'confirmation' => 'Are you sure you want to clear the cache?',
        'action' => function($data)
        {
            //clear the cache
        }
    ),

If the admin user confirms, the action will proceed. If they do not, the action will not.

<a name="dynamic-messages"></a>
## Dynamic Messages

It's possible to pass in anonymous functions to any of the custom action text fields (`title`, `confirmation`, and any of the `messages` keys). These anonymous functions will be passed the relevant Eloquent model. For example:

    'ban_user' => array(
        'title' => function($model)
        {
            return "Are you sure you want to " . ($model->banned ? 'unban ' : 'ban ') . $model->name . '?';
        },
        'messages' => array(
            'active' => function($model)
            {
                return ($model->banned ? 'Unbanning ' : 'Banning ') . $model->name . '...';
            },
            'success' => function($model)
            {
                return $model->name . ($model->banned ? ' unbanned!' : ' banned!');
            },
            'error' => function($model)
            {
                return "There was an error while " . ($model->banned ? 'unbanning ' : 'banning ') . $model->name;
            },
        ),
        'action' => function(&$data)
        {
            //ban the user
        }
    ),
