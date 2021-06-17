<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'name'      => 'required',
            'code'      => 'bail|required|alpha_dash|unique:categories,code,' . $this->id,
            'parent_id' => 'bail|nullable|exists:categories,id',
        ];
    }
}
