<?php

namespace DDPro\Admin\Http\Requests;

use App\Http\Requests\Request;
use Delatbabel\Keylists\Models\Keytype;

/**
 * Validate Request for User form
 *
 * Class UserFormRequest
 * @package App\Http\Requests
 */
class UserFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $keyTypeCTID = Keytype::where('name', 'countries')->firstOrFail()->id;
        $keyTypeTZID = Keytype::where('name', 'timezones')->firstOrFail()->id;
        $rule        = [
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'numeric',
            'country_code' => "required|exists:keyvalues,keyvalue,keytype_id,{$keyTypeCTID}",
            'timezone'     => "required|exists:keyvalues,keyvalue,keytype_id,{$keyTypeTZID}",
        ];
        if ($tmpID = $this->segment(2)) {
            // Update
            $rule['email'] .= ",{$tmpID}";
        } else {
            // Create
            $rule['password'] = 'required|confirmed';
        }

        return $rule;
    }
}
