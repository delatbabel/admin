<?php
/**
 * Class HasAddresses
 *
 * @author del
 */

namespace Delatbabel\Admin\Includes;

/**
 * Trait HasAddresses
 *
 * This trait can be included in a controller class where one or more addresses are being
 * handled.
 *
 * This trait over-rides getAddressGroups function which triggers the `item()` and `save()`
 * functions in AdminModelController to support addresses being displayed and edited as
 * street/suburb/state/postcode sets but stored in the Address model and referenced as
 * address_id from the current model.
 */
trait HasAddresses
{
    /**
     * Get Address Groups
     *
     * This can be included in a controller class where one or more addresses are being
     * handled.
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
            'address' => ['street', 'suburb', 'postal_code', 'state_code', 'country_code'],
        ];
    }
}
