<?php namespace App\Http\Helpers;

use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: mufaddaln
 * Date: 5/9/18
 * Time: 6:06 PM
 */
trait CalculateTagsReminderTrait
{

    protected $coursesCompletedByUser = null;

    /***
     * @param $request
     * @return array
     */
    public function getReminderTagsByUser($request): array
    {
        $contactEmail = $request->contact_email;
        $nativeUser = User::where('email', $contactEmail)->first();
        $tagsToAttach = [];
        $coursesTakenByUser = explode(',', $request->extraParams[ 'products' ]);
        foreach ($coursesTakenByUser as $courseKey) {
            $modulesCompletedByUser = DB::table('user_completed_modules')
                                        ->select('user_completed_modules.user_id', 'modules.course_key',
                                            'modules.id as mid', 'modules.name')
                                        ->join('modules', 'user_completed_modules.module_id', '=', 'modules.id')
                                        ->where('modules.course_key', $courseKey)
                                        ->where('user_completed_modules.user_id', '=', $nativeUser->id)
                                        ->orderBy('modules.id', 'desc')
                                        ->get()->all();

            if (empty($modulesCompletedByUser)) {
                $moduleStarter = DB::table('modules')
                                   ->select('modules.id as mid', 'tags.name')
                                   ->join('tags', 'tags.module_id', '=', 'modules.id')
                                   ->where('course_key', $courseKey)
                                   ->take(1)->get()->all();
                $tagsToAttach[] = $moduleStarter[ 0 ]->name;
            } else {
                $this->coursesCompletedByUser[ $courseKey ] = $modulesCompletedByUser;
            }
        }
        $tagsToAttach = $this->calculateTagsPerCourse($coursesTakenByUser, $tagsToAttach);
        return array_unique($tagsToAttach);
    }

    public function getNextModuleName($currentModuleName, $currentCourse, $coursesTakenByUser)
    {
        $newModuleName = '';
        if ( !empty($currentModuleName) || !is_null($currentModuleName) && is_string($currentModuleName)) {
            $lastModuleNo = $this->getModuleNumberFromName($currentModuleName);
            if ($lastModuleNo < 7) {
                $newModuleName = $this->setNewModuleName($lastModuleNo, $currentModuleName);

                return [
                    $currentCourse => [
                        'new_module_name' => $newModuleName,
                    ],
                ];
            }
            if ($lastModuleNo = 7) {
                $keyToRemove = array_search($currentCourse, $coursesTakenByUser);
                unset($coursesTakenByUser[ $keyToRemove ]);
                if (count($coursesTakenByUser) == 1) {
                    $courseInProgress = array_values($coursesTakenByUser)[ 0 ];
                    if(isset($this->coursesCompletedByUser[ $courseInProgress ])) {
                        $currentModuleName = $this->coursesCompletedByUser[ $courseInProgress ][ 0 ]->name;
                    }
                    $lastModuleNo = $this->getModuleNumberFromName($currentModuleName);
                    if ($lastModuleNo < 7) {
                        $newModuleName = $this->setNewModuleName($lastModuleNo, $currentModuleName);
                    } else {
                        $courseInProgress = 'completed';
                        $newModuleName = 'Module reminders completed';
                    }

                    return [
                        $courseInProgress => [
                            'new_module_name' => $newModuleName,
                        ]
                    ];
                } else {
                    foreach ($coursesTakenByUser as $courseKey) {
                        $currentModuleName = $this->coursesCompletedByUser[ $courseKey ][ 0 ]->name;
                        $lastModuleNo = $this->getModuleNumberFromName($currentModuleName);
                        if ($lastModuleNo < 7) {
                            $newModuleName = $this->setNewModuleName($lastModuleNo, $currentModuleName);
                        } else {
                            $courseKey = 'completed';
                            $newModuleName = 'Module reminders completed';
                        }
                        $coursesToSendReminders[ $courseKey ] = $newModuleName;

                        return $coursesToSendReminders;

                    }
                }
            }
        }
    }

    /**
     * @param $currentModuleName
     * @return int
     */
    protected function getModuleNumberFromName($currentModuleName): int
    {
        $moduleName = explode(' ', $currentModuleName);
        $lastModuleNo = (int)end($moduleName);

        return $lastModuleNo;
    }

    /***
     * @param $lastModuleNumber
     * @param $currentModuleName
     * @return mixed
     */
    protected function setNewModuleName($lastModuleNumber, $currentModuleName)
    {
        $newModuleNo = $lastModuleNumber + 1;
        $newModuleName = str_replace($lastModuleNumber, $newModuleNo, $currentModuleName);

        return $newModuleName;
    }

    /**
     * @param $coursesTakenByUser
     * @param $tagsToAttach
     * @return array
     */
    protected function calculateTagsPerCourse($coursesTakenByUser, $tagsToAttach): array
    {
        if ( !empty($this->coursesCompletedByUser)) {
            foreach ($this->coursesCompletedByUser as $courseKey => $value) {
                $nextModuleData = $this->getNextModuleName($value[ 0 ]->name, $courseKey, $coursesTakenByUser);
                $newCourseKey = array_keys($nextModuleData)[ 0 ];
                if ( !is_null($nextModuleData[ $newCourseKey ])) {
                    $moduleToStart = DB::table('modules')
                                       ->select('modules.id as mid', 'tags.name')
                                       ->join('tags', 'tags.module_id', '=', 'modules.id')
                                       ->where('course_key', $newCourseKey)
                                       ->where('modules.name', '=', $nextModuleData[ $newCourseKey ])
                                       ->get()->all();
                    if(!empty($moduleToStart)) {
                        $tagsToAttach[ $newCourseKey ] = $moduleToStart[ 0 ]->name;
                    }

                }
            }
        }

        return $tagsToAttach;
    }
}