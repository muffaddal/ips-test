<?php

namespace App\Http\Requests;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Foundation\Http\FormRequest;

class ModuleAssignerRequest extends FormRequest
{

    public $infusionSoftHelper;
    public $extraParams = null;

    public function __construct(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null,
        InfusionsoftHelper $infusionSoftHelper
    ) {
        $this->infusionSoftHelper = $infusionSoftHelper;
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

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
        return [
            'contact_email' => 'required|email',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $request = $this->all();
            $contactEmail = $request[ 'contact_email' ];
            $infusionContact = $this->infusionSoftHelper->getContact($contactEmail);
            if ($infusionContact == false) {
                $validator->errors()->add('access', "The email is not a valid Infusion Soft Email");
            } else {
                $this->extraParams[ 'products' ] = $infusionContact[ '_Products' ];
                $this->extraParams[ 'infusion_customer_id' ] = $infusionContact[ 'Id' ];
            }
        });
    }
}
