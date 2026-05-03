<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployerRequest extends FormRequest
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
            'department_id'=>'required|integer',
            'nom'=>'required|string',
            'prenom'=>'required|string',
            'email'=>'required|unique:employers,email',
            'contact'=>'required|unique:employers,contact',
            'montant_journalier'=>'required'
        ];
    }

    public function messages(){
        return [
            'email.required'=> 'L\'email est requis',
            'email.unique'=>'L\'email existe deja',
            'contact.required'=>'Le numero de telephone est requis',
            'contact.unique'=>'Le numero de telephone existe deja'
        ];
    }
}
