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
        foreach ($allTags as $key => $value) {
            DB::table('tags')->insert([
                'id' => $value->id,
                'name' => $value->name,
                'description' => $value->description,
                'category' => $value->category,
            ]);
        }
    }
}
