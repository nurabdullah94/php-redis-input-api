<?php

/**
 * Generate Large Sample CSV
 * Creates 100,000 product records for testing
 */

echo "Generating sample CSV with 100,000 products...\n\n";

$filename = 'sample_100k.csv';
$handle = fopen($filename, 'w');

if (!$handle) {
    die("Error: Cannot create file {$filename}\n");
}

// Write header
fputcsv($handle, ['name', 'sku', 'price', 'stock']);

// Product categories for variety
$categories = [
    'Laptop', 'Smartphone', 'Tablet', 'Smartwatch', 'Headphone',
    'Speaker', 'Camera', 'Monitor', 'Keyboard', 'Mouse',
    'Printer', 'Router', 'Modem', 'TV', 'Projector',
    'Gaming Console', 'Drone', 'VR Headset', 'Smart Home', 'Fitness Tracker'
];

$brands = [
    'Apple', 'Samsung', 'Dell', 'HP', 'Lenovo',
    'Asus', 'Acer', 'Sony', 'LG', 'Xiaomi',
    'Huawei', 'OnePlus', 'Google', 'Microsoft', 'Amazon',
    'Logitech', 'Razer', 'Corsair', 'Canon', 'Nikon'
];

$adjectives = [
    'Pro', 'Max', 'Plus', 'Ultra', 'Premium',
    'Elite', 'Advanced', 'Professional', 'Gaming', 'Smart',
    'Wireless', 'Portable', 'Compact', 'Mini', 'Grande'
];

// Generate 100,000 products
$totalProducts = 100000;
$batchSize = 1000;
$batches = $totalProducts / $batchSize;

echo "Total products: " . number_format($totalProducts) . "\n";
echo "Batch size: " . number_format($batchSize) . "\n";
echo "Total batches: " . number_format($batches) . "\n\n";

$startTime = microtime(true);

for ($i = 1; $i <= $totalProducts; $i++) {
    // Generate product data
    $category = $categories[array_rand($categories)];
    $brand = $brands[array_rand($brands)];
    $adjective = $adjectives[array_rand($adjectives)];

    // Product name with variety
    $name = "{$brand} {$category} {$adjective}";
    if ($i % 10 === 0) {
        $name .= " " . ($i / 10);
    }

    // SKU: BRAND-CATEGORY-NUMBER
    $sku = strtoupper(substr($brand, 0, 4)) . '-' .
           strtoupper(substr($category, 0, 4)) . '-' .
           str_pad($i, 6, '0', STR_PAD_LEFT);

    // Price: Random between 100,000 and 50,000,000
    $price = rand(100000, 50000000);

    // Stock: Random between 0 and 1000
    $stock = rand(0, 1000);

    // Write to CSV
    fputcsv($handle, [$name, $sku, $price, $stock]);

    // Progress indicator
    if ($i % $batchSize === 0) {
        $progress = ($i / $totalProducts) * 100;
        $elapsed = microtime(true) - $startTime;
        $estimated = ($elapsed / $i) * $totalProducts;
        $remaining = $estimated - $elapsed;

        echo sprintf(
            "Progress: %s / %s (%.2f%%) - Elapsed: %.2fs - Remaining: %.2fs\n",
            number_format($i),
            number_format($totalProducts),
            $progress,
            $elapsed,
            $remaining
        );
    }
}

fclose($handle);

$endTime = microtime(true);
$duration = $endTime - $startTime;
$fileSize = filesize($filename);

echo "\n";
echo "========================================\n";
echo "Generation Complete!\n";
echo "========================================\n";
echo "File: {$filename}\n";
echo "Total products: " . number_format($totalProducts) . "\n";
echo "File size: " . number_format($fileSize / 1024 / 1024, 2) . " MB\n";
echo "Duration: " . number_format($duration, 2) . " seconds\n";
echo "Speed: " . number_format($totalProducts / $duration, 0) . " products/second\n";
echo "========================================\n\n";
echo "You can now upload this file using:\n";
echo "- Postman: Import → Products → Upload CSV\n";
echo "- cURL: curl -F \"file=@{$filename}\" -H \"Authorization: Bearer <token>\" <api_url>\n";
