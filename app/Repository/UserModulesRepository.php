<?php namespace App\Repository;

use Illuminate\Support\Facades\DB;
/**
 * Created by PhpStorm.
 * User: mufaddaln
 * Date: 7/9/18
 * Time: 8:36 AM
 */
class UserModulesRepository
{

    /*****
     * @param $course
     * @param $userId
     * @return array
     */
   public function getModulesCompletedByUserByCourses($course, $userId)
   {
       return DB::table('user_completed_modules')
         ->select('user_completed_modules.user_id', 'modules.course_key',
             'modules.id as mid', 'modules.name')
         ->join('modules', 'user_completed_modules.module_id', '=', 'modules.id')
         ->where('modules.course_key', $course)
         ->where('user_completed_modules.user_id', '=', $userId)
         ->orderBy('modules.id', 'desc')
         ->get()->all();
   }

    /***
     * @param $courseKey
     * @return array
     */
   public function getStarterModulesForUserByCourse($courseKey)
   {
       return DB::table('modules')
         ->select('modules.id as mid', 'tags.name', 'tags.id as tagId')
         ->join('tags', 'tags.module_id', '=', 'modules.id')
         ->where('course_key', $courseKey)
         ->take(1)->get()->all();
   }

    /***
     * @param $course
     * @param $moduleName
     * @return array
     */
   public function getModulesByCourseAndModuleName($course, $moduleName)
   {
       return DB::table('modules')
                ->select('modules.id as mid', 'tags.name', 'tags.id as tagId')
                ->join('tags', 'tags.module_id', '=', 'modules.id')
                ->where('course_key', $course)
                ->where('modules.name', '=', $moduleName)
                ->get()->all();
   }
}