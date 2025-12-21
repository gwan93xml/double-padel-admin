<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = BlogCategory::all();
        $author = User::first(); // Menggunakan user pertama sebagai author

        if ($categories->isEmpty() || !$author) {
            return;
        }

        $blogs = [
            [
                'title' => 'Getting Started with Laravel',
                'content' => 'Learn the basics of Laravel framework and start building amazing web applications...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'laravel,php,web-development',
            ],
            [
                'title' => 'Best Practices for Business Growth',
                'content' => 'Discover proven strategies to accelerate your business growth and increase revenue...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'business,strategy,growth',
            ],
            [
                'title' => 'Healthy Living Tips',
                'content' => 'Simple and effective tips to maintain a healthy lifestyle and improve your wellbeing...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'health,wellness,lifestyle',
            ],
            [
                'title' => 'Sports Performance Training',
                'content' => 'Master the techniques and training methods used by professional athletes...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'sports,training,fitness',
            ],
            [
                'title' => 'The Future of Technology',
                'content' => 'Exploring emerging technologies that will shape the future of our digital world...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'technology,ai,innovation',
            ],
            [
                'title' => 'Cloud Computing Explained',
                'content' => 'Understand the fundamentals of cloud computing and how it can benefit your business...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'cloud,technology,devops',
            ],
            [
                'title' => 'Digital Marketing Strategies for 2025',
                'content' => 'Implement cutting-edge digital marketing techniques to reach your target audience...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'marketing,business,digital',
            ],
            [
                'title' => 'Meditation and Mental Wellness',
                'content' => 'Explore mindfulness practices to reduce stress and improve mental health...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'health,meditation,wellness',
            ],
            [
                'title' => 'Marathon Training Guide',
                'content' => 'Complete guide to preparing for and completing your first marathon...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'sports,running,marathon',
            ],
            [
                'title' => 'Artificial Intelligence in Healthcare',
                'content' => 'Discover how AI is revolutionizing the healthcare industry and patient care...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'technology,ai,healthcare',
            ],
            [
                'title' => 'Startup Funding Essentials',
                'content' => 'Everything you need to know about raising capital for your startup venture...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'business,startup,funding',
            ],
            [
                'title' => 'Yoga for Beginners',
                'content' => 'Start your yoga journey with these beginner-friendly poses and techniques...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'health,yoga,fitness',
            ],
            [
                'title' => 'Tennis Techniques and Skills',
                'content' => 'Master the essential techniques to improve your tennis game...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'sports,tennis,training',
            ],
            [
                'title' => 'Web Development Trends 2025',
                'content' => 'Stay updated with the latest web development technologies and frameworks...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'technology,web-development,trends',
            ],
            [
                'title' => 'Sustainable Business Models',
                'content' => 'Learn how to build profitable businesses while protecting the environment...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'business,sustainability,environment',
            ],
            [
                'title' => 'Nutrition Secrets for Athletes',
                'content' => 'Optimize your diet to enhance athletic performance and recovery...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'health,nutrition,sports',
            ],
            [
                'title' => 'Blockchain Technology Basics',
                'content' => 'Understand blockchain and its applications in various industries...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'technology,blockchain,cryptocurrency',
            ],
            [
                'title' => 'Leadership Skills for Managers',
                'content' => 'Develop essential leadership skills to inspire and motivate your team...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'business,leadership,management',
            ],
            [
                'title' => 'Sleep Optimization Tips',
                'content' => 'Improve your sleep quality with proven science-backed techniques...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'health,sleep,wellness',
            ],
            [
                'title' => 'CrossFit Workouts and Tips',
                'content' => 'Enhance your fitness routine with high-intensity CrossFit training methods...',
                'image' => 'https://picsum.photos/800/600',
                'tags' => 'sports,fitness,crossfit',
            ],
        ];

        foreach ($blogs as $index => $blog) {
            Blog::create([
                'category_id' => $categories->get($index % $categories->count())->id,
                'author_id' => $author->id,
                'title' => $blog['title'],
                'slug' => Str::slug($blog['title']),
                'content' => $blog['content'],
                'image' => $blog['image'],
                'tags' => $blog['tags'],
                'views' => 0,
            ]);
        }
    }
}
