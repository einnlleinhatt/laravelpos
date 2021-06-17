<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'details' => 'nullable',
            'order'   => 'nullable|numeric',
            'name'    => 'bail|required|unique:units,name,' . $this->id,
            'code'    => 'bail|nullable|alpha_num|max:20|unique:units,code,' . $this->id,
        ];
    }
}
