<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'displayName' => ['sometimes', 'required', 'string'],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email'],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
        ];
    }

    public function withValidator($validator) {
      $validator->after(function ($validator) {
        if ($validator->errors()->count() === 0) {
          if ($this->password) $this->merge(['password' => bcrypt($this->password)]);
          if ($this->displayName) $this->merge(['display_name' => $this->displayName]);
        }
      });
  }
}
