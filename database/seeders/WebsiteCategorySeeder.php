<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\WebsiteCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WebsiteCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WebsiteCategory::truncate();
        WebsiteCategory::insert([
            [
                'name' => "Education",
                'types' => "header,footer,about_section,service_section",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => "Health",
                'types' => "header,footer,about_section,service_section",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => "Digital Marketing",
                'types' => "header,footer,about_section,service_section",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => "Information",
                'types' => "header,footer,about_section,service_section",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => "Others",
                'types' => "header,footer,about_section,service_section",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);

    }
}
