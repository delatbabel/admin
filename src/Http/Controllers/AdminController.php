<?php

namespace DDPro\Admin\Http\Controllers;

use DDPro\Admin\Config\ConfigInterface;
use DDPro\Admin\Config\Factory as ConfigFactory;
use DDPro\Admin\Config\Model\Config;
use DDPro\Admin\DataTable\DataTable;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * DDPro Admin Controller
 *
 * This is the main controller class for all DDPro Admin requests.  It handles
 * all requests related to managing the data models.
 *
 * ### Entry Points
 *
 * The entry points for each function are documented in the function docblocks.
 * See also `php artisan route:list`.
 */
class AdminController extends Controller
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * @var string
     */
    protected $formRequestErrors;

    /**
     * @var View
     */
    protected $view;

    /**
     * Class constructor
     *
     * @param \Illuminate\Http\Request              $request
     * @param \Illuminate\Session\SessionManager    $session
     */
    public function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;

        $this->formRequestErrors = $this->resolveDynamicFormRequestErrors($request);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Dashboard and Utility (file download, page) routes.
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Shows the dashboard page
     *
     * * **route method**: GET
     * * **route name**: admin_dashboard
     * * **route URL**: admin
     *
     * @return Response
     */
    public function dashboard()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'administrator dashboard');
        // if the dev has chosen to use a dashboard
        if (config('administrator.use_dashboard')) {

            // set the view for the dashboard
            $viewname = config('administrator.dashboard_view');
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'administrator dashboard view == ' . $viewname);
            $this->view = view($viewname);

            return $this->view;

            // else we should redirect to the menu item
        } else {
            /** @var ConfigFactory $configFactory */
            $configFactory = app('admin_config_factory');
            $home          = config('administrator.home_page');

            // first try to find it if it's a model config item
            $config = $configFactory->make($home);

            if (! $config) {
                throw new \InvalidArgumentException("Administrator: " .  trans('administrator::administrator.valid_home_page'));
            } elseif ($config->getType() === 'model') {
                return redirect()->route('admin_index', [$config->getOption('name')]);
            } elseif ($config->getType() === 'settings') {
                return redirect()->route('admin_settings', [$config->getOption('name')]);
            }
        }
    }

    /**
     * The GET method that runs when a user needs to download a file
     *
     * * **route method**: GET
     * * **route name**: admin_file_download
     * * **route URL**: admin/file_download
     *
     * @return Response
     */
    public function fileDownload()
    {
        if ($response = $this->session->get('administrator_download_response')) {
            $this->session->forget('administrator_download_response');
            $filename = substr($response['headers']['content-disposition'][0], 22, -1);

            return response()->download($response['file'], $filename, $response['headers']);
        } else {
            return redirect()->back();
        }
    }

    /**
     * The pages view
     *
     * This is where the custom view pages are handled.  These allow the caller to preserve the
     * navigation of Administrator, but have you complete control over the content section. For
     * these you simply need to prefix `page.` to your view path and pass that to the menu array
     * in the configuration (administrator.menu).
     *
     * * **route method**: GET
     * * **route name**: admin_page
     * * **route URL**: admin/page/{page}
     *
     * @return Response
     */
    public function page($page)
    {
        // set the page
        $this->view = view($page);

        return $this->view;
    }

    /**
     * POST method for switching a user's locale
     *
     * * **route method**: POST
     * * **route name**: admin_switch_locale
     * * **route URL**: admin/switch_locale/{locale}
     *
     * @param string	$locale
     *
     * @return RedirectResponse
     */
    public function switchLocale($locale)
    {
        if (in_array($locale, config('administrator.locales'))) {
            $this->session->put('administrator_locale', $locale);
        }

        return redirect()->back();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Model routes
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * The main view for any of the data models
     *
     * * **route method**: GET
     * * **route name**: admin_index
     * * **route URL**: admin/{model}
     *
     * @return Response
     */
    public function index($modelName)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'model index view');

        // set the layout content and title
        $this->view = view(config('administrator.model_index_view'));

        return $this->view;
    }

    /**
     * Gets the item edit page / information
     *
     * * **route method**: GET
     * * **route name**: admin_get_item | admin_new_item
     * * **route URL**: admin/{model}/{id} | admin/{model}/new
     *
     * @param string		$modelName
     * @param mixed			$itemId
     * @return Response
     */
    public function item($modelName, $itemId = 0)
    {
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config        = app('itemconfig');

        /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
        $fieldFactory  = app('admin_field_factory');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $columnFactory     = app('admin_column_factory');
        $actionPermissions = $actionFactory->getActionPermissions();
        $fields            = $fieldFactory->getEditFields();

        // if it's ajax, we just return the item information as json
        if ($this->request->ajax()) {
            // try to get the object
            $model = $config->getModel($itemId, $fields, $columnFactory->getIncludedColumns($fields));

            if ($model->exists) {
                $model = $config->updateModel($model, $fieldFactory, $actionFactory);
            }

            $response = $actionPermissions['view'] ? response()->json($model) : response()->json([
                'success' => false,
                'errors'  => "You do not have permission to view this item",
            ]);

            // set the Vary : Accept header to avoid the browser caching the json response
            return $response->header('Vary', 'Accept');
        } else {
            $this->view = view(config('administrator.model_index_view'), [
                'itemId' => $itemId,
            ]);

            return $this->view;
        }
    }

    /**
     * Accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
     *
     * * **route method**: POST
     * * **route name**: admin_save_item
     * * **route URL**: admin/{model}/{id?}/save
     *
     * @param string		$modelName
     * @param int			$id
     *
     * @return string JSON
     */
    public function save($modelName, $id = null)
    {
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config        = app('itemconfig');

        /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
        $fieldFactory  = app('admin_field_factory');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        if (array_key_exists('form_request', $config->getOptions()) && $this->formRequestErrors !== null) {
            return response()->json([
                'success' => false,
                'errors'  => $this->formRequestErrors,
            ]);
        }

        $save = $config->save($this->request, $fieldFactory->getEditFields(), $actionFactory->getActionPermissions(), $id);

        if (is_string($save)) {
            return response()->json([
                'success' => false,
                'errors'  => $save,
            ]);
        } else {
            // override the config options so that we can get the latest
            app('admin_config_factory')->updateConfigOptions();

            // grab the latest model data
            $columnFactory = app('admin_column_factory');
            $fields        = $fieldFactory->getEditFields();
            $model         = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

            if ($model->exists) {
                $model = $config->updateModel($model, $fieldFactory, $actionFactory);
            }

            return response()->json([
                'success' => true,
                'data'    => $model->toArray(),
            ]);
        }
    }

    /**
     * POST delete method that accepts data via JSON POST and deletes an item
     *
     * * **route method**: POST
     * * **route name**: admin_delete_item
     * * **route URL**: admin/{model}/{id}/delete
     *
     * @param string		$modelName
     * @param int			$id
     *
     * @return string JSON
     */
    public function delete($modelName, $id)
    {
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config        = app('itemconfig');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $baseModel     = $config->getDataModel();
        $model         = $baseModel::find($id);
        $errorResponse = [
            'success' => false,
            'error'   => "There was an error deleting this item. Please reload the page and try again.",
        ];

        // if the model or the id don't exist, send back an error
        $permissions = $actionFactory->getActionPermissions();

        if (! $model->exists || ! $permissions['delete']) {
            return response()->json($errorResponse);
        }

        // delete the model
        if ($model->delete()) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json($errorResponse);
        }
    }

    /**
     * POST method for handling custom model actions
     *
     * @param string		$modelName
     *
     * @return string JSON
     */
    public function customModelAction($modelName)
    {
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $actionName    = $this->request->input('action_name', false);
        $dataTable     = app('admin_datatable');

        // get the sort options and filters
        $page        = $this->request->input('page', 1);
        $sortOptions = $this->request->input('sortOptions', []);
        $filters     = $this->request->input('filters', []);

        // get the prepared query options
        $prepared = $dataTable->prepareQuery(app('db'), $page, $sortOptions, $filters);

        // get the action and perform the custom action
        $action = $actionFactory->getByName($actionName, true);
        $result = $action->perform($prepared['query']);

        // if the result is a string, return that as an error.
        if (is_string($result)) {
            return response()->json(['success' => false, 'error' => $result]);
        }
        // if it's falsy, return the standard error message
        elseif (! $result) {
            $messages = $action->getOption('messages');

            return response()->json(['success' => false, 'error' => $messages['error']]);
        } else {
            $response = ['success' => true];

            // if it's a download response, flash the response to the session and return the download link
            if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse')) {
                $file    = $result->getFile()->getRealPath();
                $headers = $result->headers->all();
                $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

                $response['download'] = route('admin_file_download');
            }
            // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }

    /**
     * POST method for handling custom model item actions
     *
     * @param string		$modelName
     * @param int			$id
     *
     * @return string JSON
     */
    public function customModelItemAction($modelName, $id = null)
    {
        /** @var Config $config */
        $config        = app('itemconfig');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $model         = $config->getDataModel();
        $model         = $model::find($id);
        $actionName    = $this->request->input('action_name', false);

        // get the action and perform the custom action
        $action = $actionFactory->getByName($actionName);
        $result = $action->perform($model);

        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();

        // if the result is a string, return that as an error.
        if (is_string($result)) {
            return response()->json(['success' => false, 'error' => $result]);

        // if it's falsy, return the standard error message
        } elseif (! $result) {
            $messages = $action->getOption('messages');
            return response()->json(['success' => false, 'error' => $messages['error']]);
        } else {
            $fieldFactory  = app('admin_field_factory');
            $columnFactory = app('admin_column_factory');
            $fields        = $fieldFactory->getEditFields();
            $model         = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

            if ($model->exists) {
                $model = $config->updateModel($model, $fieldFactory, $actionFactory);
            }

            $response = ['success' => true, 'data' => $model->toArray()];

            // if it's a download response, flash the response to the session and return the download link
            if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse')) {
                $file    = $result->getFile()->getRealPath();
                $headers = $result->headers->all();
                $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

                $response['download'] = route('admin_file_download');

            // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            } elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }

    /**
     * Gets the database results for the current model
     *
     * @param string		$modelName
     *
     * @return string JSON containing an array of rows
     */
    public function results($modelName)
    {
        /** @var DataTable $dataTable */
        $dataTable = app('admin_datatable');

        // get the sort options and filters
        $page        = $this->request->input('page', 1);
        $sortOptions = $this->request->input('sortOptions', []);
        $filters     = $this->request->input('filters', []);

        // return the rows
        return response()->json($dataTable->getRows(app('db'), $filters, $page, $sortOptions));
    }

    /**
     * Gets a list of related items given constraints
     *
     * @param string	$modelName
     *
     * @return string JSON containing an array of objects [{id: string} ... {1: 'name'}, ...]
     */
    public function updateOptions($modelName)
    {
        $fieldFactory = app('admin_field_factory');
        $response     = [];

        // iterate over the supplied constrained fields
        foreach ($this->request->input('fields', []) as $field) {
            // get the constraints, the search term, and the currently-selected items
            $constraints   = array_get($field, 'constraints', []);
            $term          = array_get($field, 'term', []);
            $type          = array_get($field, 'type', false);
            $fieldName     = array_get($field, 'field', false);
            $selectedItems = array_get($field, 'selectedItems', false);

            $response[$fieldName] = $fieldFactory->updateRelationshipOptions($fieldName, $type, $constraints, $selectedItems, $term);
        }

        return response()->json($response);
    }

    /**
     * The GET method that displays a file field's file
     *
     * @return Response
     */
    public function displayFile()
    {
        // get the stored path of the original
        $path = $this->request->input('path');
        $data = File::get($path);
        $file = new SymfonyFile($path);

        $headers = [
            'Content-Type'        => $file->getMimeType(),
            'Content-Length'      => $file->getSize(),
            'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"'
        ];

        return response()->make($data, 200, $headers);
    }

    /**
     * The POST method that runs when a user uploads a file on a file field
     *
     * @param string	$modelName
     * @param string	$fieldName
     *
     * @return string JSON
     */
    public function fileUpload($modelName, $fieldName)
    {
        $fieldFactory = app('admin_field_factory');

        // get the model and the field object
        $field = $fieldFactory->findField($fieldName);

        return response()->json($field->doUpload());
    }

    /**
     * The POST method for setting a user's rows per page
     *
     * @param string	$modelName
     *
     * @return string JSON
     */
    public function rowsPerPage($modelName)
    {
        $dataTable = app('admin_datatable');

        // get the inputted rows and the model rows
        $rows = (int) $this->request->input('rows', 20);
        $dataTable->setRowsPerPage(app('session.store'), 0, $rows);

        return response()->JSON(['success' => true]);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Settings routes
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * The main view for any of the settings pages
     *
     * @param string	$settingsName
     *
     * @return Response
     */
    public function settings($settingsName)
    {
        // set the layout content and title
        $this->layout->content = view("administrator::settings");

        return $this->layout;
    }

    /**
     * POST save settings method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
     *
     * @return string JSON
     */
    public function settingsSave()
    {
        $config = app('itemconfig');
        $save   = $config->save($this->request, app('admin_field_factory')->getEditFields());

        if (is_string($save)) {
            return response()->json([
                'success' => false,
                'errors'  => $save,
            ]);
        } else {
            // override the config options so that we can get the latest
            app('admin_config_factory')->updateConfigOptions();

            return response()->json([
                'success' => true,
                'data'    => $config->getDataModel(),
                'actions' => app('admin_action_factory')->getActionsOptions(),
            ]);
        }
    }

    /**
     * POST method for handling custom actions on the settings page
     *
     * @param string	$settingsName
     *
     * @return string JSON
     */
    public function settingsCustomAction($settingsName)
    {
        $config        = app('itemconfig');
        $actionFactory = app('admin_action_factory');
        $actionName    = $this->request->input('action_name', false);

        // get the action and perform the custom action
        $action = $actionFactory->getByName($actionName);
        $data   = $config->getDataModel();
        $result = $action->perform($data);

        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();

        // if the result is a string, return that as an error.
        if (is_string($result)) {
            return response()->json(['success' => false, 'error' => $result]);

        // if it's falsy, return the standard error message
        } elseif (! $result) {
            $messages = $action->getOption('messages');

            return response()->json(['success' => false, 'error' => $messages['error']]);
        } else {
            $response = ['success' => true, 'actions' => $actionFactory->getActionsOptions(true)];

            // if it's a download response, flash the response to the session and return the download link
            if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse')) {
                $file    = $result->getFile()->getRealPath();
                $headers = $result->headers->all();
                $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

                $response['download'] = route('admin_file_download');

            // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            } elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }

    /**
     * POST method to capture any form request errors
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveDynamicFormRequestErrors(Request $request)
    {
        try {
            $config       = app('itemconfig');
            $fieldFactory = app('admin_field_factory');
        } catch (\ReflectionException $e) {
            return null;
        }
        if (array_key_exists('form_request', $config->getOptions())) {
            try {
                $model = $config->getFilledDataModel($request, $fieldFactory->getEditFields(), $request->id);

                $request->merge($model->toArray());
                $formRequestClass = $config->getOption('form_request');
                app($formRequestClass);
            } catch (HttpResponseException $e) {
                // Parses the exceptions thrown by Illuminate\Foundation\Http\FormRequest
                $errorMessages = $e->getResponse()->getContent();
                $errorsArray   = json_decode($errorMessages);
                if (! $errorsArray && is_string($errorMessages)) {
                    return $errorMessages;
                }
                if ($errorsArray) {
                    return implode(".", array_dot($errorsArray));
                }
            }
        }
        return null;
    }
}
