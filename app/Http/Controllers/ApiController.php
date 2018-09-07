<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Http\Requests\ModuleAssignerRequest;
use App\Tags;
use App\User;
use App\Traits\ModuleAssignerTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{

    use ModuleAssignerTrait;

    public $request, $infusionSoftHelper;

    protected $coursesCompletedByUser = null;

    public function __construct(InfusionsoftHelper $infusionSoftHelper)
    {
        $this->infusionSoftHelper = $infusionSoftHelper;
    }

    /**
     * @return mixed
     */
    public function exampleCustomer()
    {

        $uniqid = uniqid();

        $this->infusionSoftHelper->createContact([
            'Email'     => $uniqid . '@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name'     => 'Test ' . $uniqid,
            'email'    => $uniqid . '@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    /**
     * @param ModuleAssignerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reminderAssigner(ModuleAssignerRequest $request)
    {
        $tagsToAdd = $this->getReminderTagsByUser($request);
        $infusionSoftContactId = $request->extraParams[ 'infusion_customer_id' ];

        return $this->AddTagsReminderToInfusionSoftForUser($infusionSoftContactId, $tagsToAdd);
    }

    /***
     * @param $infusionSoftContactId
     * @param $tagsToAdd
     */
    protected function AddTagsReminderToInfusionSoftForUser($infusionSoftContactId, $tagsToAdd)
    {
        if (empty($tagsToAdd)) {
            $response = [
                'message' => 'The Customer has not chosen any courses to send reminders!',
            ];

            throw new HttpResponseException(response()->json([
                'errors' => $response
            ], JsonResponse::HTTP_OK));
        }
        $response = [];
        foreach ($tagsToAdd as $key => $tagId) {
            $addTag = $this->infusionSoftHelper->addTag($infusionSoftContactId, $tagId);
            $tagInfo = Tags::find($tagId);
            if ($addTag) {
                $response[ $key ][ 'status' ] = 'success';
            } else {
                $response[ $key ][ 'status' ] = 'failed';
            }

            $response[ $key ][ 'message' ] = 'Reminder Tag Name : ' . $tagInfo->name;
        }

        throw new HttpResponseException(response()->json([
            'data' => $response
        ], JsonResponse::HTTP_OK));
    }
}
