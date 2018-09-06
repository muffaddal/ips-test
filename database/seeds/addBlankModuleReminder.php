<?php

use Illuminate\Database\Seeder;

class addBlankModuleReminder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('modules')->insert([
            'course_key' => 'completed',
            'name' => 'Module reminders completed',
        ]);
    }
}
