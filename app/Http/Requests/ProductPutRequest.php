<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductPutRequest extends Request
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
            'id_producto' => ['required', 'integer'],
            'documento' => ['required'],  // Primero, validar que 'documentos' es un arreglo
            'documento.*' => ['file', 'mimes:pdf,doc,docx,png,jpeg,jpg'],  // Luego, validar que cada archivo en el arreglo es un archivo válido
            'nombre_archivo' => ['required', 'string'],
        ];;
    }
}
