<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Service;
use App\Models\Barber;

echo "Fixing Products with real assets...\n";
$productAssets = [
    1 => 'https://m.media-amazon.com/images/I/61wEcdb7tkL._AC_.jpg',
    2 => '/assets/img/pom.jpg',
    3 => '/assets/img/vit.jpg',
    4 => '/assets/img/vow.jpg',
    5 => '/assets/img/hair.jpg',
];

foreach ($productAssets as $id => $path) {
    $p = Product::find($id);
    if ($p) {
        $p->image_path = $path;
        $p->save();
    }
}

echo "Fixing Services with real assets...\n";
Service::all()->each(function($s) {
    $s->image_path = '/assets/img/hair.jpg';
    $s->save();
});

echo "Fixing Barbers with real assets...\n";
$barberAssets = [
    1 => '/assets/img/barber1.jpg',
    2 => '/assets/img/barber2.jpg',
];
foreach ($barberAssets as $id => $path) {
    $b = Barber::find($id);
    if ($b) {
        $b->image_path = $path;
        $b->save();
    }
}

echo "Done!\n";
