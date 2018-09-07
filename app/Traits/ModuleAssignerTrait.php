<?php namespace App\Traits;

use App\User;
use UserModules;

/**
 * Created by PhpStorm.
 * User: mufaddaln
 * Date: 5/9/18
 * Time: 6:06 PM
 */
trait ModuleAssignerTrait
{

    protected $coursesCompletedByUser = [];

    /***
     * @param $request
     * @return array
     */
    public function getReminderTagsByUser($request): array
    {
        $tagsToAttach = [];
        $contactEmail = $request->contact_email;
        $nativeUser = User::where('email', $contactEmail)->first();
        $coursesTakenByUser = explode(',', $request->extraParams[ 'products' ]);
        foreach ($coursesTakenByUser as $courseKey) {
            $modulesCompletedByUser = UserModules::getModulesCompletedByUserByCourses($courseKey, $nativeUser->id);

            if (empty($modulesCompletedByUser)) {
                $moduleStarter = UserModules::getStarterModulesForUserByCourse($courseKey);
                $tagsToAttach[] = $moduleStarter[ 0 ]->tagId;
            } else {
                $this->coursesCompletedByUser[ $courseKey ] = $modulesCompletedByUser;
            }
        }

        return $this->getTags($coursesTakenByUser, $tagsToAttach);
    }

    /***
     * @param $currentModuleName
     * @param $currentCourse
     * @param $coursesTakenByUser
     * @return array
     * @author Mufaddal.N
     */
    public function getModuleToRemind($currentModuleName, $currentCourse, $coursesTakenByUser)
    {
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
                    if (isset($this->coursesCompletedByUser[ $courseInProgress ])) {
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
                        $currentModuleName = isset($this->coursesCompletedByUser[ $courseKey ]) ? $this->coursesCompletedByUser[ $courseKey ][ 0 ]->name : null;
                        $lastModuleNo = $this->getModuleNumberFromName($currentModuleName);
                        if ($lastModuleNo < 7) {
                            $newModuleName = $this->setNewModuleName($lastModuleNo, $currentModuleName);
                        } else {
                            $courseKey = 'completed';
                            $newModuleName = 'Module reminders completed';
                        }

                        $coursesToSendReminders[ $courseKey ] = $newModuleName;
                        $coursesToSendReminders[ $courseKey ] = $currentModuleName;

                        return $coursesToSendReminders;
                    }
                }
            }
        }
    }

    /**
     * @param $currentModuleName
     * @return int
     * @author Mufaddal.N
     */
    protected function getModuleNumberFromName($currentModuleName): int
    {
        if ( !is_null($currentModuleName) && is_string($currentModuleName)) {
            $moduleName = explode(' ', $currentModuleName);
            $lastModuleNo = (int)end($moduleName);

            return $lastModuleNo;
        }

        return 8;
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
    protected function getTags($coursesTakenByUser, $tagsToAttach): array
    {
        if ( !empty($this->coursesCompletedByUser)) {
            foreach ($this->coursesCompletedByUser as $courseKey => $value) {
                $nextModuleData = $this->getModuleToRemind($value[ 0 ]->name, $courseKey, $coursesTakenByUser);
                $newCourseKey = array_keys($nextModuleData)[ 0 ];
                if ( !is_null($nextModuleData[ $newCourseKey ])) {
                    $moduleToStart = UserModules::getModulesByCourseAndModuleName($newCourseKey, $nextModuleData[ $newCourseKey ]);
                    if ( !empty($moduleToStart)) {
                        $tagsToAttach[] = $moduleToStart[ 0 ]->tagId;
                    }
                }
            }
        }
        $tags = array_unique($tagsToAttach);
        return [
            'tagsToAttach' => array_first($tags),
        ];
    }
}