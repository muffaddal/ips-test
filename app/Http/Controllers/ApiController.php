<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Http\Requests\ModuleAssignerRequest;
use App\Tags;
use App\User;
use App\Module;
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
            "_Products" => 'ipa,iea,iaa'
        ]);

        $user = User::create([
            'name'     => 'Test ' . $uniqid,
            'email'    => $uniqid . '@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
//        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(7)->get());
//        $user->completed_modules()->attach(Module::where('course_key', 'iea')->limit(7)->get());
//        $user->completed_modules()->attach(Module::where('course_key', 'iaa')->limit(7)->get());
//        $user->completed_modules()->attach(Module::where('name', 'IPA Module 6')->first());

        return $user;
    }

    /**
     * @param ModuleAssignerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reminderAssigner(ModuleAssignerRequest $request)
    {
        $tagsData = $this->getReminderTagsByUser($request);
        $infusionSoftContactId = $request->extraParams[ 'infusion_customer_id' ];

        return $this->AddTagsReminderToInfusionSoftForUser($infusionSoftContactId, $tagsData);
    }

    /***
     * @param $infusionSoftContactId
     * @param $tagData
     */
    protected function AddTagsReminderToInfusionSoftForUser($infusionSoftContactId, $tagData)
    {
        $response = [];
        $tagId = $tagData[ 'tagsToAttach' ];
        if (empty($tagId)) {
            $tagId = Tags::where('name', 'Module reminders completed')->first()->id;
        }
        $addTag = $this->infusionSoftHelper->addTag($infusionSoftContactId, $tagId);
        $tagInfo = Tags::find($tagId);
        if ($addTag) {
            $response[ 'status' ] = 'success';
        } else {
            $response[ 'status' ] = 'failed';
        }

        $response[ 'message' ] = 'Reminder Tag Name : ' . $tagInfo->name;

        throw new HttpResponseException(response()->json([
            'data' => $response
        ], JsonResponse::HTTP_OK));
    }
}
