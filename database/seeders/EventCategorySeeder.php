<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\EventCategory;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Music', 'color_gradient' => 'from-purple-500 to-pink-600'],
            ['name' => 'Technology', 'color_gradient' => 'from-blue-500 to-cyan-600'],
            ['name' => 'Food & Wine', 'color_gradient' => 'from-orange-500 to-red-600'],
            ['name' => 'Sports', 'color_gradient' => 'from-green-500 to-emerald-600'],
            ['name' => 'Art & Culture', 'color_gradient' => 'from-pink-500 to-red-700'],
            ['name' => 'Business', 'color_gradient' => 'from-gray-500 to-slate-700'],
            ['name' => 'Education', 'color_gradient' => 'from-indigo-500 to-blue-700'],
            ['name' => 'Health & Wellness', 'color_gradient' => 'from-teal-500 to-green-600'],
            ['name' => 'Film & Theater', 'color_gradient' => 'from-yellow-500 to-orange-600'],
            ['name' => 'Fashion', 'color_gradient' => 'from-rose-500 to-pink-700'],
            ['name' => 'Travel & Adventure', 'color_gradient' => 'from-cyan-500 to-sky-600'],
            ['name' => 'Charity & Causes', 'color_gradient' => 'from-red-500 to-pink-700'],
            ['name' => 'Science', 'color_gradient' => 'from-blue-600 to-indigo-700'],
            ['name' => 'Literature', 'color_gradient' => 'from-violet-500 to-purple-700'],
            ['name' => 'Gaming', 'color_gradient' => 'from-orange-500 to-yellow-600'],
        ];

        foreach ($categories as $cat) {
            EventCategory::create([
                'name'           => $cat['name'],
                'slug'           => Str::slug($cat['name']),
                'color_gradient' => $cat['color_gradient'],
            ]);
        }
    }
}
