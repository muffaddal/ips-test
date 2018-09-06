<?php

use Illuminate\Database\Seeder;
use App\Http\Helpers\InfusionsoftHelper;
use App\Tags;
use Illuminate\Support\Facades\DB;

class createTags extends Seeder
{
    public $tags;
    public function __construct(Tags $tags) {
        $this->tags = $tags;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $infusionSoft = new InfusionsoftHelper();
        $allTags = collect($infusionSoft->getAllTags());
        $moduleId = [];
        foreach ($allTags as $key => $value) {
            $moduleName = $value->name;
            for($i=1;$i<=7;$i++) {
                if($moduleName == 'Module reminders completed') {
                    $moduleIdData = DB::table('modules')->select('id')->where('name', '=', 'Module reminders completed' )->take(1)->get()->all();
                    $moduleId[$key] = $moduleIdData[0]->id;
                }

                if (strpos($moduleName, 'IEA Module '.$i) !== false) {
                    $moduleIdData = DB::table('modules')->select('id')->where('name', 'like', '%IEA Module '.$i.'%' )->take(1)->get()->all();
                    $moduleId[$key] = $moduleIdData[0]->id;

                }

                if (strpos($moduleName, 'IPA Module '.$i) !== false) {
                    $moduleIdData = DB::table('modules')->select('id')->where('name', 'like', '%IPA Module '.$i.'%' )->take(1)->get()->all();
                    $moduleId[$key] = $moduleIdData[0]->id;
                }

                if (strpos($moduleName, 'IAA Module '.$i) !== false) {
                    $moduleIdData = DB::table('modules')->select('id')->where('name', 'like', '%IAA Module '.$i.'%' )->take(1)->get()->all();
                    $moduleId[$key] = $moduleIdData[0]->id;
                }
            }
            DB::table('tags')->insert([
                'id' => $value->id,
                'name' => $value->name,
                'description' => $value->description,
                'category' => $value->category,
                'module_id' => $moduleId[$key],
            ]);
        }
    }
}
