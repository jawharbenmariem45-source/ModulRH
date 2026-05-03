<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    return [
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role'  => 'required|in:rh,manager',
    ];
    }

    public function messages(){
        return [
            'name.required'=>'le nom de l\'administrateur est requis',
            'email.required'=>'l\'email est requis',
            'email.email'=>'Cette adresse mail est deja liee a un compte',
            'password.required'=>'Le mot de passe est requis'
        ];
    }
}
