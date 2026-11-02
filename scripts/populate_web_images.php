<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

$mainImages = [
    'product_1_main.jpg',
    'product_2_main.jpg',
    'product_3_main.jpg',
    'product_4_main.jpg',
    'product_5_main.jpg',
    'product_6_main.jpg',
    'product_7_main.jpg',
    'product_8_main.jpg',
    'outfitter_prod_1.jpg',
    'outfitter_prod_2.jpg',
    'outfitter_prod_3.jpg',
    'outfitter_prod_4.jpg',
    'outfitter_prod_5.jpg',
    'outfitter_prod_6.jpg',
    'outfitter_prod_7.jpg',
    'outfitter_prod_8.jpg',
];

$galleryMap = [
    'product_1_main.jpg' => ['product_1_gal1.jpg', 'product_1_gal2.jpg'],
    'product_2_main.jpg' => ['product_2_gal1.jpg', 'product_2_gal2.jpg'],
    'product_3_main.jpg' => ['product_3_gal1.jpg', 'product_3_gal2.jpg'],
    'product_4_main.jpg' => ['product_4_gal1.jpg', 'product_4_gal2.jpg'],
    'product_5_main.jpg' => ['product_5_gal1.jpg', 'product_5_gal2.jpg'],
    'product_6_main.jpg' => ['product_6_gal1.jpg', 'product_6_gal2.jpg'],
    'product_7_main.jpg' => ['product_7_gal1.jpg', 'product_7_gal2.jpg'],
    'product_8_main.jpg' => ['product_8_gal1.jpg', 'product_8_gal2.jpg'],
];

$products = Product::orderBy('id', 'asc')->get();
echo "Found " . $products->count() . " products.\n";

$updatedCount = 0;
foreach ($products as $index => $product) {
    $mainImg = $mainImages[$index % count($mainImages)];
    
    // Check if current image exists on disk
    $currentExists = false;
    if ($product->web_main_image && file_exists(public_path('uploads/products/' . $product->web_main_image))) {
        $currentExists = true;
    }

    if (!$currentExists) {
        $product->web_main_image = $mainImg;
        if (empty($product->image)) {
            $product->image = $mainImg;
        }
        $product->save();
        $updatedCount++;

        // Add gallery images if available
        if (isset($galleryMap[$mainImg])) {
            foreach ($galleryMap[$mainImg] as $sortOrder => $galImg) {
                DB::table('product_web_images')->updateOrInsert(
                    ['product_id' => $product->id, 'image_path' => $galImg],
                    ['sort_order' => $sortOrder, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}

echo "Done! Updated $updatedCount products with images and gallery photos.\n";
