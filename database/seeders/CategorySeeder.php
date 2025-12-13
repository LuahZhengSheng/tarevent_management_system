<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General Discussion',
                'slug' => 'general-discussion',
                'description' => 'General topics and casual conversations',
                'icon' => 'bi-chat-dots',
                'color' => '#6c757d',
                'order' => 1,
            ],
            [
                'name' => 'Campus Life',
                'slug' => 'campus-life',
                'description' => 'Everything about campus activities and student life',
                'icon' => 'bi-building',
                'color' => '#0dcaf0',
                'order' => 2,
            ],
            [
                'name' => 'Academic',
                'slug' => 'academic',
                'description' => 'Study tips, course discussions, and academic help',
                'icon' => 'bi-book',
                'color' => '#0d6efd',
                'order' => 3,
            ],
            [
                'name' => 'Events',
                'slug' => 'events',
                'description' => 'Upcoming events, workshops, and activities',
                'icon' => 'bi-calendar-event',
                'color' => '#dc3545',
                'order' => 4,
            ],
            [
                'name' => 'Clubs & Organizations',
                'slug' => 'clubs-organizations',
                'description' => 'Club activities and organization news',
                'icon' => 'bi-people',
                'color' => '#198754',
                'order' => 5,
            ],
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Tech discussions, programming, and innovation',
                'icon' => 'bi-cpu',
                'color' => '#6f42c1',
                'order' => 6,
            ],
            [
                'name' => 'Career & Internships',
                'slug' => 'career-internships',
                'description' => 'Career advice, internship opportunities, and job hunting',
                'icon' => 'bi-briefcase',
                'color' => '#fd7e14',
                'order' => 7,
            ],
            [
                'name' => 'Q&A',
                'slug' => 'qa',
                'description' => 'Questions and answers about anything',
                'icon' => 'bi-question-circle',
                'color' => '#ffc107',
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
        
        $this->command->info('Categories seeded successfully!');
    }
}
