<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Maincategory;
use App\Models\Subcategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Maincategory::create([
            "name" => "ქალი"
        ]);
        Category::create([
            "name" => "კაბა",
            "maincategory_id" => 1
        ]);
        Subcategory::create([
            "name" => "კაბა",
            "category_id" => 1,
            "maincategory_id" => 1
        ]);
    }
}
