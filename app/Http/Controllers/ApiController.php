<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Http\Requests\ModuleAssignerRequest;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Module;

class ApiController extends Controller
{
    public $request, $infusionSoftHelper;
    // Todo: Module reminder assigner

    public function __construct(InfusionsoftHelper $infusionSoftHelper) {
        $this->infusionSoftHelper = $infusionSoftHelper;
    }

    /**
     * @return mixed
     */
    public function exampleCustomer(){

        $uniqid = uniqid();

        $this->infusionSoftHelper->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    public function reminderAssigner(ModuleAssignerRequest $request)
    {
        $contactEmail = $request->contact_email;
        $nativeUser = User::where('email', $contactEmail)->first();
    }
}
