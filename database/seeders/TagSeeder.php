<?php
// database/seeders/TagSeeder.php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Programming & Tech
            ['name' => 'python', 'type' => 'official', 'status' => 'active'],
            ['name' => 'javascript', 'type' => 'official', 'status' => 'active'],
            ['name' => 'php', 'type' => 'official', 'status' => 'active'],
            ['name' => 'java', 'type' => 'official', 'status' => 'active'],
            ['name' => 'web-development', 'type' => 'official', 'status' => 'active'],
            ['name' => 'mobile-app', 'type' => 'official', 'status' => 'active'],
            ['name' => 'database', 'type' => 'official', 'status' => 'active'],
            ['name' => 'ai-ml', 'type' => 'official', 'status' => 'active'],
            
            // Learning Level
            ['name' => 'beginner', 'type' => 'official', 'status' => 'active'],
            ['name' => 'intermediate', 'type' => 'official', 'status' => 'active'],
            ['name' => 'advanced', 'type' => 'official', 'status' => 'active'],
            ['name' => 'tutorial', 'type' => 'official', 'status' => 'active'],
            ['name' => 'guide', 'type' => 'official', 'status' => 'active'],
            ['name' => 'tips', 'type' => 'official', 'status' => 'active'],
            ['name' => 'advice', 'type' => 'official', 'status' => 'active'],
            
            // Student Year
            ['name' => 'freshman', 'type' => 'official', 'status' => 'active'],
            ['name' => 'sophomore', 'type' => 'official', 'status' => 'active'],
            ['name' => 'junior', 'type' => 'official', 'status' => 'active'],
            ['name' => 'senior', 'type' => 'official', 'status' => 'active'],
            
            // Campus Life
            ['name' => 'housing', 'type' => 'official', 'status' => 'active'],
            ['name' => 'dining', 'type' => 'official', 'status' => 'active'],
            ['name' => 'transportation', 'type' => 'official', 'status' => 'active'],
            ['name' => 'library', 'type' => 'official', 'status' => 'active'],
            ['name' => 'sports', 'type' => 'official', 'status' => 'active'],
            ['name' => 'fitness', 'type' => 'official', 'status' => 'active'],
            
            // Events
            ['name' => 'workshop', 'type' => 'official', 'status' => 'active'],
            ['name' => 'seminar', 'type' => 'official', 'status' => 'active'],
            ['name' => 'competition', 'type' => 'official', 'status' => 'active'],
            ['name' => 'hackathon', 'type' => 'official', 'status' => 'active'],
            ['name' => 'networking', 'type' => 'official', 'status' => 'active'],
            ['name' => 'social', 'type' => 'official', 'status' => 'active'],
            ['name' => 'volunteer', 'type' => 'official', 'status' => 'active'],
            
            // Career
            ['name' => 'internship', 'type' => 'official', 'status' => 'active'],
            ['name' => 'job', 'type' => 'official', 'status' => 'active'],
            ['name' => 'career', 'type' => 'official', 'status' => 'active'],
            ['name' => 'resume', 'type' => 'official', 'status' => 'active'],
            ['name' => 'interview', 'type' => 'official', 'status' => 'active'],
            ['name' => 'job-fair', 'type' => 'official', 'status' => 'active'],
            
            // Urgency & Help
            ['name' => 'urgent', 'type' => 'official', 'status' => 'active'],
            ['name' => 'help-needed', 'type' => 'official', 'status' => 'active'],
            ['name' => 'question', 'type' => 'official', 'status' => 'active'],
            ['name' => 'discussion', 'type' => 'official', 'status' => 'active'],
            ['name' => 'announcement', 'type' => 'official', 'status' => 'active'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
        
        $this->command->info('Tags seeded successfully!');
    }
}
