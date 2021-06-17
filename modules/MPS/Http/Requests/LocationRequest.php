<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'phone'            => 'required',
            'color'            => 'nullable',
            'header'           => 'nullable',
            'footer'           => 'nullable',
            'address'          => 'required',
            'email'            => 'required|email',
            'name'             => 'required|string',
            'registers'        => 'array|min:1|required',
            'registers.*.id'   => 'nullable',
            'registers.*.code' => 'required',
            'registers.*.name' => 'required',
            'extra_attributes' => 'nullable',
            'account_id'       => 'bail|required|exists:accounts,id',
            'accounts'         => 'nullable|array',
            'logo'             => 'nullable|mimes:jpg,jpeg,png,svg|max:300',
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ($this->has('logo')) {
            $path         = $this->logo->store('/images', 'public');
            $data['logo'] = Storage::disk('public')->url($path);
        }
        return $data;
    }
}
