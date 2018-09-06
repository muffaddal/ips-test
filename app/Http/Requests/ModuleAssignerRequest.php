<?php

namespace App\Http\Requests;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Foundation\Http\FormRequest;
use Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

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

    protected function prepareForValidation()
    {
        $request = $this->all();
        if(isset($request[ 'contact_email' ]) && !empty($request[ 'contact_email' ])) {
            $contactEmail = $request[ 'contact_email' ];
            $infusionContact = $this->infusionSoftHelper->getContact($contactEmail);
            if ($infusionContact === false) {
                $data = [
                    'status_code' => 422,
                    'status'      => 'failed',
                    'message'     => 'The email is not a valid Infusion Soft Customer Email',
                ];
                throw new HttpResponseException(response()->json([
                    'errors' => $data
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
            } else {
                $this->extraParams[ 'products' ] = $infusionContact[ '_Products' ];
                $this->extraParams[ 'infusion_customer_id' ] = $infusionContact[ 'Id' ];
            }
        } else {
            $data = [
                'status_code' => 422,
                'status'      => 'failed',
                'message'     => 'Invalid Params',
            ];
            throw new HttpResponseException(response()->json([
                'errors' => $data
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json([
            'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
