<?php
namespace DDPro\Admin\Http\Controllers;

use DDPro\Admin\Config\Model\Config;
use DDPro\Admin\DataTable\DataTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * DDPro Admin Model Controller
 *
 * Manage model from admin page
 */
class AdminModelController extends Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var View
     */
    protected $view;

    /**
     * Class constructor
     *
     * @param Request        $request
     * @param SessionManager $session
     */
    public function __construct(Request $request, SessionManager $session)
    {
        $this->request = $request;
        $this->session = $session;
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

        return $this->view = view(config('administrator.model_index_view'));
    }

    /**
     * Gets the item edit page / information
     *
     * * **route method**: GET
     * * **route name**: admin_get_item | admin_new_item
     * * **route URL**: admin/{model}/{id} | admin/{model}/new
     *
     * @param string $modelName
     * @param mixed  $itemId
     * @return Response
     */
    public function item($modelName, $itemId = 0)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'model item fetch, modelName = ' . $modelName . ', itemId = ' . $itemId);
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
        $fieldFactory = app('admin_field_factory');
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory     = app('admin_action_factory');
        $columnFactory     = app('admin_column_factory');
        $actionPermissions = $actionFactory->getActionPermissions();
        $fields            = $fieldFactory->getEditFields();
        // try to get the object
        $model = $config->getModel($itemId, $fields, $columnFactory->getIncludedColumns($fields));
        if ($model->exists) {
            $model = $config->updateModel($model, $fieldFactory, $actionFactory);
        }
        if (!$actionPermissions['view']) {
            return redirect()->route('admin_index');
        }
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'view model', $model->toArray());

        return $this->view = view(config('administrator.model_index_view'), [
            'itemId' => $itemId,
            'model'  => $model,
        ]);
    }

    /**
     * Accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
     *
     * * **route method**: POST
     * * **route name**: admin_save_item
     * * **route URL**: admin/{model}/{id?}/save
     *
     * @param string $modelName
     * @param int    $id
     *
     * @return string JSON
     */
    public function save($modelName, $id = null)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'model item save, modelName = ' . $modelName . ', id = ' . $id, [
            'input' => $this->request->all(),
        ]);
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Fields\Factory $fieldFactory */
        $fieldFactory = app('admin_field_factory');
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');
        /* Validate from form_request */
        if ($formRequestClass = $config->getOption('form_request')) {
            $this->request = app($formRequestClass);
        }
        $save = $config->save($this->request, $fieldFactory->getEditFields(), $actionFactory->getActionPermissions(),
            $id);
        if ($save !== true) {
            return redirect()->back()->withInput()->withErrors($config->getCustomValidator());
        }
        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();
        // grab the latest model data
        $columnFactory = app('admin_column_factory');
        $fields        = $fieldFactory->getEditFields();
        $model         = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));
        if ($model->exists) {
            $model = $config->updateModel($model, $fieldFactory, $actionFactory);
        }

        return redirect()->route('admin_index', [$modelName]);
    }

    /**
     * POST delete method that accepts data via JSON POST and deletes an item
     *
     * * **route method**: POST
     * * **route name**: admin_delete_item
     * * **route URL**: admin/{model}/{id}/delete
     *
     * @param string $modelName
     * @param int    $id
     *
     * @return string JSON
     */
    public function delete($modelName, $id)
    {
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
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
        if (!$model->exists || !$permissions['delete']) {
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
     * @param string $modelName
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
        } // if it's falsy, return the standard error message
        elseif (!$result) {
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
            } // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }

    /**
     * POST method for handling custom model item actions
     *
     * @param string $modelName
     * @param int    $id
     *
     * @return string JSON
     */
    public function customModelItemAction($modelName, $id = null)
    {
        /** @var Config $config */
        $config = app('itemconfig');
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
        } elseif (!$result) {
            // if it's falsy, return the standard error message
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
            if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse')) {
                // if it's a download response, flash the response to the session and return the download link
                $file    = $result->getFile()->getRealPath();
                $headers = $result->headers->all();
                $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);
                $response['download'] = route('admin_file_download');
            } elseif (is_a($result, '\Illuminate\Http\RedirectResponse')) {
                // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
                $response['redirect'] = $result->getTargetUrl();
            }

            return response()->json($response);
        }
    }

    /**
     * Gets the database results for the current model
     *
     * Called by DataTable
     *
     * @param string $modelName
     * @return string JSON
     * @link https://www.datatables.net/manual/server-side
     */
    public function dataTableResults($modelName)
    {
        /** @var DataTable $dataTable */
        $dataTable = app('admin_datatable');
        $input     = $this->request->all();
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'fetch dataTable results for model = ' . $modelName, [
            'input' => $input,
        ]);
        $result = $dataTable->getDataTableRows(app('db'), $input);
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'fetch dataTable results for model = ' . $modelName, [
            'result' => $result,
        ]);

        return response()->json($result);
    }

    /**
     * Gets a list of related items given constraints
     *
     * @param string $modelName
     *
     * @return string JSON containing an array of objects [{id: string} ... {1: 'name'}, ...]
     */
    public function updateOptions($modelName)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'get update options, model = ' . $modelName);
        $fieldFactory = app('admin_field_factory');
        $response     = [];
        // iterate over the supplied constrained fields
        foreach ($this->request->input('fields', []) as $field) {
            // get the constraints, the search term, and the currently-selected items
            $constraints          = array_get($field, 'constraints', []);
            $term                 = array_get($field, 'term', []);
            $type                 = array_get($field, 'type', false);
            $fieldName            = array_get($field, 'field', false);
            $selectedItems        = array_get($field, 'selectedItems', false);
            $response[$fieldName] = $fieldFactory->updateRelationshipOptions($fieldName, $type, $constraints,
                $selectedItems, $term);
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
        $path    = $this->request->input('path');
        $data    = File::get($path);
        $file    = new SymfonyFile($path);
        $headers = [
            'Content-Type'        => $file->getMimeType(),
            'Content-Length'      => $file->getSize(),
            'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"',
        ];

        return response()->make($data, 200, $headers);
    }

    /**
     * The POST method that runs when a user uploads a file on a file field
     *
     * @param string $modelName
     * @param string $fieldName
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
     * @param string $modelName
     *
     * @return string JSON
     */
    public function rowsPerPage($modelName)
    {
        $dataTable = app('admin_datatable');
        // get the inputted rows and the model rows
        $rows = (int)$this->request->input('rows', 20);
        $dataTable->setRowsPerPage(app('session.store'), 0, $rows);

        return response()->JSON(['success' => true]);
    }
}
