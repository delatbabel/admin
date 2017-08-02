<?php

namespace DDPro\Admin\Http\Controllers;

use DDPro\Admin\Actions\Action;
use DDPro\Admin\Config\Model\Config;
use DDPro\Admin\DataTable\DataTable;
use DDPro\Admin\Includes\UploadedImage;
use Delatbabel\Contacts\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * DDPro Admin Model Controller
 *
 * This controller manage everything relate to the Model (List, Edit, Create, Download...)
 * If there are any Model, which working with a special template or process, it should be declared a controller on
 * `controller_handler` option and which controller should extend from this controller
 *
 * ### Entry Points
 *
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

    /**
     * Get Address Groups
     *
     * This can be included in a controller class where one or more addresses are being
     * handled.
     *
     * This function defines the real model to apparent model data mapping for
     * addresses.  In the example shown, the model address_id field is mapped
     * to and from the data accessor's street, suburb, postal_code, state_code, and country_code
     * fields.
     *
     * @return array
     */
    protected function getAddressGroups()
    {
        return [];
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
     * @return \Illuminate\View\View
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

            // Display address fields according to the address relationship
            foreach ($this->getAddressGroups() as $groupName => $groupFields) {
                if ($address = $model->{$groupName}) {
                    $model->{$groupFields[0]} = $address->street;
                    $model->{$groupFields[1]} = $address->suburb;
                    $model->{$groupFields[2]} = $address->postal_code;
                    $model->{$groupFields[3]} = $address->state_code;
                    $model->{$groupFields[4]} = $address->country_code;
                }
            }
        }
        if (! $actionPermissions['view']) {
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
     * @return \Illuminate\Http\RedirectResponse
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

        // Get all fields in config
        $fields = $fieldFactory->getEditFields();

        // Process to save/remove addresses
        $savedAddresses   = [];
        $removedAddresses = [];
        $isNew            = false;

        // Loop though each address group
        foreach ($this->getAddressGroups() as $groupName => $groupFields) {

            // Get address inputs
            $addressInputs = $this->request->only($groupFields);

            // Set default process flag to false
            $process = false;
            foreach ($addressInputs as $key => $value) {

                // We don't store address fields in current model
                unset($fields[$key]);

                // If any address input is not empty, then set the process flag to true
                if (! empty($value)) {
                    $process = true;
                }
            }

            // If the flag is true, then process to save current address group
            if ($process) {

                // Get values only
                $addressInputs = array_values($addressInputs);

                // In edit mode, load the old address
                if ($id && $model = $config->getDataModel()->find($id)) {
                    $address = $model->{$groupName};
                }

                // If old address doesn't exist at this point, create a new one
                if (! isset($address) || ! $address) {
                    $isNew   = true;
                    $address = new Address();
                }

                // Store address fields in address table
                $address->street        = $addressInputs[0];
                $address->suburb        = $addressInputs[1];
                $address->postal_code   = $addressInputs[2];
                $address->state_code    = $addressInputs[3];
                $address->country_code  = $addressInputs[4];
                $address->save();

                // Add new address id to an array for later use
                if ($isNew) {
                    $savedAddresses[$groupName] = $address->id;
                }
            } else { // Add this group to and array for later use
                $removedAddresses[] = $groupName;
            }
        }

        // Save current model
        $save = $config->save($this->request, $fields, $actionFactory->getActionPermissions(),
            $id);
        if ($save !== true) {
            return redirect()->back()->withInput()->withErrors($config->getCustomValidator());
        }

        // Get the saved model back from the config, which will contain
        // an updated ID
        $model = $config->getDataModel();
        $id    = $model->getKey();
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Saved model with ID = ' . $id);

        // Store reference to new addresses in address table
        if (! empty($savedAddresses)) {
            foreach ($savedAddresses as $key => $value) {
                $model->{$key . '_id'} = $value;
            }
            $model->save();
        }

        // Delete old addresses and remove reference to them
        if (! empty($removedAddresses)) {
            foreach ($removedAddresses as $removedAddress) {
                /** @var Address $addressToRemove */
                $addressToRemove = $model->{$removedAddress};
                if (! empty($addressToRemove)) {
                    $addressToRemove->forceDelete();
                }
            }
        }

        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();

        // grab the latest model data
        $columnFactory = app('admin_column_factory');
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
     * POST Batch Destroy items
     *
     * This is called on the admin/model/destroy URL.
     *
     * see customDataTable.js
     *
     * @param $modelName
     * @return Response JSON
     */
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
        if (! $permissions['delete']) {
            return response()->json($errorResponse);
        }

        $baseModel  = $config->getDataModel();
        $primaryKey = $baseModel->getKeyName();
        $models     = $baseModel::whereIn($primaryKey, $ids);

        // delete the models
        if ($models && $models->delete()) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json($errorResponse);
        }
    }

    /**
     * POST Batch Activate / Inactivate toggle
     *
     * This is a batch action function which toggles the status of the selected items from active
     * to inactive or vice-versa.  It is called by the admin/model/toggle_activate URL
     *
     * see customDataTable.js
     *
     * @param $modelName
     * @return Response JSON
     */
    public function toggleActivate($modelName)
    {
        $ids    = $this->request->get('ids');
        $status = $this->request->get('status');

        // The itemconfig singleton is built in the ValidateModel middleware and
        // will be an instance of \DDPro\Admin\Config\Model\Config
        /** @var Config $config */
        $config = app('itemconfig');

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');

        if (! in_array($status, ['active', 'inactive'])) {
            $errorResponse = [
                'success' => false,
                'error'   => "Unsupported action. Please reload the page and try again.",
            ];
            return response()->json($errorResponse);
        }

        $mapping = [
            'active'   => 'activating',
            'inactive' => 'deactivating'
        ];

        $mapped_action = $mapping[$status];

        $errorResponse = [
            'success' => false,
            'error'   => "There was an error $mapped_action selected item(s). Please reload the page and try again.",
        ];

        // checking permission
        $permissions = $actionFactory->getActionPermissions();
        if (($status == 'active' && ! $permissions['active']) || ($status == 'inactive' && ! $permissions['inactive'])) {
            return response()->json($errorResponse);
        }

        $baseModel  = $config->getDataModel();
        $primaryKey = $baseModel->getKeyName();
        $models     = $baseModel::whereIn($primaryKey, $ids);

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
     * @return Response JSON
     */
    public function customModelAction($modelName)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'custom model action input', $this->request->all());
        // customModelAction:custom model action input {"action_name":"batchSendInvoice","ids":["2","1"]}
        // customModelAction:custom model action input {"action_name":"test","id":"2"}

        $response = [
            'success' => true,
        ];

        // If this is an action for one ID then it's a custom model item action
        if (! empty($this->request->get('id'))) {
            $ids = $this->request->get('id');
        } else {
            // If an empty array of IDs has been sent in, then return success but do nothing.
            $ids = $this->request->ids;
            if (empty($ids) || ! is_array($ids) || count($ids) == 0) {
                return response()->json($response);
            }
        }

        /** @var \DDPro\Admin\Actions\Factory $actionFactory */
        $actionFactory = app('admin_action_factory');
        $actionName    = $this->request->input('action_name', false);

        // get the action and perform the custom action
        /** @var Action $action */
        $action = $actionFactory->getByName($actionName, true);

        // Debug in case something has gone missing
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'custom model action options', $action->getOptions());

        // Find the action, check if it's a local function
        /*
        $actionCall = $action->getOption('action');
        if (is_string($actionCall) && is_callable([$this, $actionCall])) {
            $action->setCallableAction([$this, $actionCall]);
        }
        */

        // Call the action itself
        $result = $action->perform($ids);

        // if the result is a string, return that as an error.
        if (is_string($result)) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'error result from custom action == ' . $result);
            return response()->json(['success' => false, 'error' => $result]);
        }

        // if it's falsy, return the standard error message
        if (! $result) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'false result from custom action');
            $messages = $action->getOption('messages');
            return response()->json(['success' => false, 'error' => $messages['error']]);
        }

        // Binary file responses create a file download action
        if ($result instanceof BinaryFileResponse) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'file result from custom action');

            // if it's a download response, flash the response to the session and return the download link
            $file    = $result->getFile()->getRealPath();
            $headers = $result->headers->all();
            $this->session->put('administrator_download_response', ['file' => $file, 'headers' => $headers]);
            $response['download'] = route('admin_file_download');

        // Redirect responses store a redirect entry in the response JSON
        } elseif ($result instanceof RedirectResponse) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'redirect result from custom action');

            // if it's a redirect, put the url into the redirect key so that javascript can transfer the user
            /** @var RedirectResponse $result */
            $response['redirect'] = $result->getTargetUrl();
        }

        // Every other true result is assumed to be a simple success
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'success result from custom action');
        return response()->json($response);
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
        // The request data comes from the DataTable JS plugin and looks like this:
        // {"input":{"draw":"1",
        //   "columns":[
        //     {"data":"0","name":"batch_select","searchable":"true","orderable":"false","search":{"value":"","regex":"false"}},
        //     {"data":"1","name":"id","searchable":"true","orderable":"true","search":{"value":"","regex":"false"}},
        //     ... etc
        //   "start":"0","length":"10","search":{"value":"","regex":"false"},
        //   "filters":{
        //      "company":{"field_name":"company","value":""},
        //      ... etc
        // }}}

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
     * This gets triggered by the admin/{model}/{field}/file_upload route.
     *
     * In fact this never gets called.  Instead the File or Image field classes
     * handle the upload.
     *
     * @param string $modelName
     * @param string $fieldName
     *
     * @return string JSON
     */
    public function fileUpload($modelName, $fieldName)
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'File upload triggered for ' . $fieldName);

        $fieldFactory = app('admin_field_factory');

        // get the model and the field object
        $field = $fieldFactory->findField($fieldName);

        // Do the upload
        /** @var UploadedImage $upload */
        $upload = $field->doUpload();

        return response()->json($upload->path);
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
        $data         = $this->request->get('data');
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
        if (! $permissions['reorder']) {
            return response()->json($errorResponse);
        }

        $baseModel = $config->getDataModel();
        if ($model = $baseModel::find($id)) {
            if ($this->request->get('prev_sibling_id')) {
                $pre = $baseModel::find($this->request->get('prev_sibling_id'));
                if (! $pre || ! $model->makePreviousSiblingOf($pre)) {
                    return response()->json($errorResponse);
                }
            } elseif ($this->request->get('next_sibling_id')) {
                $next = $baseModel::find($this->request->get('next_sibling_id'));
                if (! $next || ! $model->makeNextSiblingOf($next)) {
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

    public function export($modelName)
    {
        $config        = app('itemconfig');
        $actionFactory = app('admin_action_factory');
        $permissions   = $actionFactory->getActionPermissions();
        if ($config->getOption('export') && isset($permissions['export'])) {
            // field names
            $arrColumns = [];
            foreach ($config->getOption('export')['columns'] as $key => $column) {
                $arrColumns[$key] = $column['title'];
            }

            // parse data
            $arrResults = [];
            $query      = $this->prepareExportQuery($config->getDataModel());
            foreach ($query->get() as $index => $model) {
                foreach ($config->getOption('export')['columns'] as $key => $column) {
                    $attributeValue = $model->getAttribute($key);
                    if (isset($column['type']) && $column['type'] == 'enum') {
                        $options = [];
                        if (isset($column['options'])) {
                            $options = $column['options'];
                        } elseif (isset($column['callback'])) {
                            $options = call_user_func_array([$column['callback']['class'], $column['callback']['method']], isset($column['callback']['params']) ? $column['callback']['params'] : []);
                        }
                        if (isset($options[$attributeValue])) {
                            $attributeValue = $options[$attributeValue];
                        }
                    }
                    $arrResults[$index][] = $attributeValue;
                }
            }
            return Excel::create($modelName, function ($excel) use ($arrResults, $arrColumns) {
                $excel->sheet('mySheet', function ($sheet) use ($arrResults, $arrColumns) {
                    $sheet->fromArray($arrResults, null, 'A1', false, false)->prependRow(array_values($arrColumns));
                });
            })->download($config->getOption('export')['type']);
        } else {
            return abort('403');
        }
    }

    private function prepareExportQuery($model)
    {
        // get things going by grouping the set
        $table   = $model->getTable();
        $keyName = $model->getKeyName();
        /** @var EloquentBuilder $query */
        $query   = $model->groupBy($table . '.' . $keyName);
        // set up initial array states for the selects
        $selects = [$table . '.*'];
        // column factory
        $columnFactory = app('admin_column_factory');
        $columns       = $columnFactory->getExportColumns();
        if ($columns) {
            foreach ($columns as $column) {
                // if this is a related column, we'll need to add some selects
                $column->filterQuery($selects);
            }
        }
        $query->getQuery()->select($selects);
        return $query;
    }
}
