<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Http\Requests\ModuleAssignerRequest;
use App\Tags;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Http\Helpers\CalculateTagsReminderTrait;

class ApiController extends Controller
{

    use CalculateTagsReminderTrait;

    public $request, $infusionSoftHelper;

    protected $coursesCompletedByUser = null;

    // Todo: Module reminder assigner

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
        $contactEmail = $request->contact_email;
        return $this->AddTagsToInfusionSoftForUser($contactEmail, $infusionSoftContactId, $tagsToAdd);
    }

    /**
     * @param $contactEmail
     * @param $infusionSoftContactId
     * @param $tagsToAdd
     * @return \Illuminate\Http\JsonResponse
     */
    protected function AddTagsToInfusionSoftForUser($contactEmail, $infusionSoftContactId, $tagsToAdd)
    {
        if (empty($tagsToAdd)) {
            $response = [
                'status_code' => 200,
                'status'      => 'success',
                'message'     => 'The Customer has not chosen any courses to send reminders!',
            ];

            return Response::json($response);
        }
        $response = [];
        foreach ($tagsToAdd as $key => $tagId) {
            $user = User::where('email', $contactEmail)->first();
            $tagInfo = Tags::find($tagId);
            $addTag = $this->infusionSoftHelper->addTag($infusionSoftContactId, $tagId);
            $response[ $key ] = [
                'status_code' => 200,
                'data'        => [
                    'tag_id'                => $tagId,
                    'tag_name'              => $tagInfo->name,
                    'user_id'               => $user->id,
                    'user_name'             => $user->name,
                    'user_email'            => $user->email,
                    'infusion_soft_user_id' => $infusionSoftContactId,
                ]
            ];
            if ($addTag) {
                $response[ $key ][ 'status' ] = 'success';
                $response[ $key ][ 'message' ] = 'The reminder set successfully for ' . $tagInfo->name;
            } else {
                $response[ $key ][ 'status' ] = 'failed';
                $response[ $key ][ 'message' ] = 'Failed to set reminder ' . $tagInfo->name;
            }
        }

        return Response::json($response);
    }
}
