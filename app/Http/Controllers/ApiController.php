<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Http\Requests\ModuleAssignerRequest;
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
        if(empty($tagsToAdd)) {
            $response = [
                'status_code' => 200,
                'status'      => 'success',
                'message'     => 'The Customer has not chosen any courses to send reminders!',
            ];

            return Response::json($response);
        }
//        foreach($tagsToAdd as $key => $tag) {
//
//            }
//
//            $infusionSoftContactId = $request->extraParams['infusion_customer_id'];
//            return Response::json($infusionsoft->addTag($infusionSoftContactId, $tag_id));
    }
}
