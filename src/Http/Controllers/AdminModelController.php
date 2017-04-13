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
use Maatwebsite\Excel\Facades\Excel;

/**
 * DDPro Admin Model Controller
 *
 * This controller manage everything relate to the Model (List, Edit, Create, Download...)
 * If there are any Model, which working with a special template or process, it should be declared a controller on
 * `controller_handler` option and which controller should extend from this controller
 *
 * ### Entry Points
 * The entry points for each function are documented in the function docblocks.
 * See also `php artisan route:list | grep {model}`.
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
        // Validate from form_request
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

    public function destroy($modelName)
    {
        $ids = $this->request->get('ids');
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $errorResponse = [
            'success' => false,
            'error'   => "There was an error deleting selected item(s). Please reload the page and try again.",
        ];
        // checking permission
        $permissions = $actionFactory->getActionPermissions();
        if (!$permissions['delete']) {
            return response()->json($errorResponse);
        }

        $baseModel = $config->getDataModel();
        $primaryKey = $baseModel->getKeyName();
        $models = $baseModel::whereIn($primaryKey, $ids);
        // delete the models
        if ($models && $models->delete()) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json($errorResponse);
        }
    }

    public function toggleActivate($modelName)
    {
        $ids = $this->request->get('ids');
        $status = $this->request->get('status');
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        if (!in_array($status, ['active', 'inactive'])) {
            $errorResponse = [
                'success' => false,
                'error'   => "Unsupported action. Please reload the page and try again.",
            ];
            return response()->json($errorResponse);
        }

        $mapping = [
            'active' => 'activating',
            'inactive' => 'deactivating'
        ];

        $mapped_action = $mapping[$status];

        $errorResponse = [
            'success' => false,
            'error'   => "There was an error $mapped_action selected item(s). Please reload the page and try again.",
        ];

        // checking permission
        $permissions = $actionFactory->getActionPermissions();
        if (($status == 'active' && !$permissions['active']) || ($status == 'inactive' && !$permissions['inactive'])) {
            return response()->json($errorResponse);
        }

        $baseModel = $config->getDataModel();
        $primaryKey = $baseModel->getKeyName();
        $models = $baseModel::whereIn($primaryKey, $ids);
        // update status of the models
        if ($models && $models->update(['status' => $status])) {
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

    public function customModelItemData($modelName, $itemId)
    {
        $data = $this->request->get('data');
        $functionName = "get" . ucfirst($data);
        return $this->$functionName($modelName, $itemId);
    }

    public function reorderItem($modelName)
    {
        $id = $this->request->get('id');
        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');
        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        $errorResponse = [
            'success' => false,
            'error'   => "There was an error reordering selected item. Please reload the page and try again.",
        ];
        // checking permission
        $permissions = $actionFactory->getActionPermissions();
        if (!$permissions['reorder']) {
            return response()->json($errorResponse);
        }

        $baseModel = $config->getDataModel();
        if ($model = $baseModel::find($id)) {
            if ($this->request->get('prev_sibling_id')) {
                $pre = $baseModel::find($this->request->get('prev_sibling_id'));
                if (!$pre || !$model->makePreviousSiblingOf($pre)) {
                    return response()->json($errorResponse);
                }
            } elseif ($this->request->get('next_sibling_id')) {
                $next = $baseModel::find($this->request->get('next_sibling_id'));
                if (!$next || !$model->makeNextSiblingOf($next)) {
                    return response()->json($errorResponse);
                }
            }
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json($errorResponse);
        }
    }

    public function exportCSV($modelName)
    {
        $config = app('itemconfig');
        $actionFactory = app('admin_action_factory');
        $permissions = $actionFactory->getActionPermissions();
        if ($config->getOption('export_csv') && isset($permissions['export_csv'])) {
            $baseModel = $config->getDataModel();
            $data = $baseModel::get()->toArray();
            return Excel::create($modelName, function($excel) use ($data) {
                $excel->sheet('mySheet', function($sheet) use ($data)
                {
                    $sheet->fromArray($data);
                });
            })->download('csv');
        } else {
            return abort('403');
        }

    }
}
