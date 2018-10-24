<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{

    public $fileCount = 0;

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

        $rules = array(
            'json_files' => 'required',
            'json_files.*' => 'file|max:1024',
        );

        return $rules;
    }

    /**
     * Ghost error message output
     *
     * @return array
     */
    public function messages()
    {
        $messages = array();
        
        $messages['json_files'] = 'A file is required';
        $messages['json_files.*.max'] = 'can\'t be larger than 1MB';
        $messages['json_files.*.mimetypes'] = 'must be in JSON format';
        $messages['json_files.*.mimes'] = 'must be in JSON format';
        $messages['json_files.*.file'] = 'must represent a file type';

        return $messages;
    }
}
