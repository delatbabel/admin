<?php

namespace DDPro\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate Request for Role form
 *
 * Class RoleFormRequest
 * @package App\Http\Requests
 */
class RoleFormRequest extends FormRequest
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
        $rule = [
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:roles,slug',
        ];
        if ($tmpID = $this->segment(2)) {
            // Update
            $rule['slug'] .= ",{$tmpID}";
        }

        return $rule;
    }
}
