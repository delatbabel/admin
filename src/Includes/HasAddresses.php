<?php
/**
 * Class HasAddresses
 *
 * @author del
 */

namespace DDPro\Admin\Includes;

use Illuminate\Support\Facades\Log;
use DDPro\Admin\Config\Model\Config;
use Delatbabel\Contacts\Models\Address;

/**
 * Trait HasAddresses
 *
 * This trait can be included in a controller class where one or more addresses are being
 * handled.
 *
 * This trait over-rides the `item()` and `save()` functions from AdminModelController to
 * support addresses being displayed and edited as street/suburb/state/postcode sets but
 * stored in the Address model and referenced as address_id from the current model.
 *
 * ### Example
 *
 * <code>
 * // Example code goes here
 * </code>
 *
 */
trait HasAddresses
{
    /**
     * Get Address Groups
     *
     * This function defines the real model to apparent model data mapping for
     * addresses.  In the example shown, the model address_id field is mapped
     * to and from the data accessor's street, suburb, state_code, and postal_code
     * fields.
     *
     * Override this function in classes where there are more than one address.
     *
     * @return array
     */
    protected function getAddressGroups()
    {
        return [
            'address' => ['street', 'suburb', 'state_code', 'postal_code'],
        ];
    }

    /**
     * Gets the item edit page / information
     *
     * * **route method**: GET
     * * **route name**: admin_get_item | admin_new_item
     * * **route URL**: admin/celebrities/{id} | admin/celebrities/new
     *
     * @param string $modelName
     * @param mixed  $itemId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
                    $model->{$groupFields[2]} = $address->state_code;
                    $model->{$groupFields[3]} = $address->postal_code;
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
     * Save Handle
     *
     * * **route method**: POST
     * * **route name**: admin_save_item
     * * **route URL**: admin/celebrities/{id?}/save
     *
     * @param string $modelName
     * @param int    $id
     *
     * @return $this|\Illuminate\Http\RedirectResponse
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

        // Process to save addresses
        $savedAddresses = [];
        $isNew = false;

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
                if (!empty($value)) {
                    $process = true;
                }
            }
            // If the flag is true, then process to save current address group
            if ($process) {
                // Get values only
                $addressInputs = array_values($addressInputs);
                if ($id && $model = $config->getDataModel()->find($id)) {
                    // In edit mode, load the old address
                    $address = $model->{$groupName};
                }
                if (!isset($address) || !$address) {
                    // If old address doesn't exist at this point, create a new one
                    $isNew = true;
                    $address = new Address();
                }
                // Store address fields in address table
                $address->street = $addressInputs[0];
                $address->suburb = $addressInputs[1];
                $address->state_code = $addressInputs[2];
                $address->postal_code = $addressInputs[3];
                $address->save();

                // Add new address id to an array for later use
                if ($isNew) {
                    $savedAddresses[$groupName] = $address->id;
                }
            }
        }

        // Save current model
        $save = $config->save($this->request, $fields, $actionFactory->getActionPermissions(),
            $id);
        if ($save !== true) {
            return redirect()->back()->withInput()->withErrors($config->getCustomValidator());
        }

        // override the config options so that we can get the latest
        app('admin_config_factory')->updateConfigOptions();

        // grab the latest model data
        $columnFactory = app('admin_column_factory');
        $model         = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));
        if ($model->exists) {
            $model = $config->updateModel($model, $fieldFactory, $actionFactory);
        }

        // Store reference to new address in address table
        if (!empty($savedAddresses)) {

            // Not sure why I cannot use $model->save() with the above $model object, so I have to load it here
            $model = $config->getDataModel()->find($id);
            foreach ($savedAddresses as $key => $value) {
                $model->{$key . '_id'} = $value;
            }
            $model->save();
        }

        return redirect()->route('admin_index', [$modelName]);
    }

}
