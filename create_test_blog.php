<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\Artisan;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cat = BlogCategory::firstOrCreate(['name' => 'Guides', 'slug' => 'guides']);

$blog = Blog::create([
    'category_id' => $cat->id,
    'title' => 'How to Sell Your Car Fast in Dubai',
    'slug' => 'how-to-sell-your-car-fast-in-dubai',
    'excerpt' => 'A quick guide to selling your car in Dubai using our new Content Outline feature.',
    'content' => '
        <p>Selling your car in Dubai doesn\'t have to be a headache. Follow these simple steps to get the best price quickly.</p>
        <h2>Step 1: Get a Free Valuation</h2>
        <p>First, use our online tool to get an instant price for your car based on market data.</p>
        <h2>Step 2: Book an Inspection</h2>
        <p>Bring your car to one of our branches for a quick 20-minute physical check by our experts.</p>
        <h2>Step 3: Get Paid Instantly</h2>
        <p>Once we agree on the price, we handle all the RTA paperwork and pay you cash or bank transfer immediately.</p>
    ',
    'outline' => [
        ['title' => 'Step 1: Get a Free Valuation', 'anchor' => 'step-1-get-a-free-valuation'],
        ['title' => 'Step 2: Book an Inspection', 'anchor' => 'step-2-book-an-inspection'],
        ['title' => 'Step 3: Get Paid Instantly', 'anchor' => 'step-3-get-paid-instantly'],
    ],
    'published_at' => now(),
]);

echo "Test Blog Created Successfully! ID: " . $blog->id . "\n";
echo "Slug: " . $blog->slug . "\n";
