<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'announcement',
            'alert',
            'disaster',
            'event',
        ];

        for ($i = 1; $i <= 20; $i++) {
            DB::table('news')->insert([
                'barangay' => 'Punta',
                'title' => "Barangay Punta Update #{$i}",
                'content' => "This is sample news content number {$i} for Barangay Punta. Stay informed and safe at all times.",
                'category' => $categories[array_rand($categories)],
                'image' => null, // you can add fake image later
                'status' => 'published',
                'user_id' => 16,
                'published_at' => Carbon::now()->subDays(rand(0, 30)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
