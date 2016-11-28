<?php

namespace DDPro\Admin\Config;

/**
 * Interface ModelServiceInterface
 *
 * This interface is used when a model config wants to provide a custom form with a custom
 * load / save handler for a model.
 *
 * The functions contained here include:
 *
 * * getBladeViewIndex -- provide a custom index page.
 * * getBladeViewForm -- provide a custom form.
 * * getFormData -- provide data for the form.
 * * saveFormData -- provide a custom save handler for the form.
 *
 * @package DDPro\Admin\Config
 */
interface ModelServiceInterface
{
    /**
     * Returns the name of the blade view used as the model "index" page.
     *
     * @return string
     */
    public function getBladeViewIndex();

    /**
     * Returns the name of the blade view used as the model create/edit form.
     *
     * @return string
     */
    public function getBladeViewForm();

    /**
     * Get the data to be displayed in the model edit form when editing an existing object.
     *
     * @param integer $id
     * @return array
     */
    public function getFormData($id=null);

    /**
     * Save handler
     *
     * May throw some kind of exception of the data save fails.
     *
     * @param integer $id
     * @param array $data
     * @return boolean
     */
    public function saveFormData($id=null, $data);
}
