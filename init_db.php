<?php
// init_db.php - Initialize and Seed SQLite Database
require_once 'db.php';

$db = get_db_connection();

// 1. Create Tables
$db->exec("DROP TABLE IF EXISTS order_items");
$db->exec("DROP TABLE IF EXISTS orders");
$db->exec("DROP TABLE IF EXISTS users");
$db->exec("DROP TABLE IF EXISTS products");
$db->exec("DROP TABLE IF EXISTS categories");

$db->exec("CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    image_url TEXT
)");

$db->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    name TEXT NOT NULL,
    subtitle TEXT,
    price INTEGER,
    rating REAL,
    review_count INTEGER DEFAULT 0,
    image_url TEXT,
    badge TEXT,
    description TEXT,
    is_featured INTEGER DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)");

$db->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    total_price INTEGER,
    status TEXT DEFAULT 'Pending',
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    product_id INTEGER,
    quantity INTEGER,
    price INTEGER,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

// Categories
$categories = [
    ['Drift Pro', 'drift', 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?q=80&w=800&auto=format&fit=crop&fm=webp'],
    ['Off-Road Rebels', 'offroad', 'https://images.unsplash.com/photo-1581232238728-3e36e8151499?q=80&w=800&auto=format&fit=crop&fm=webp'],
    ['Speed Masters', 'speed', 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?q=80&w=800&auto=format&fit=crop&fm=webp'],
    ['Custom Builds', 'custom', 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?q=80&w=800&auto=format&fit=crop&fm=webp'],
    ['The Vault', 'vault', 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?q=80&w=800&auto=format&fit=crop&fm=webp']
];

$stmt = $db->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
foreach ($categories as $cat) {
    $stmt->execute($cat);
}

// 3. Seed Products (100 total, 20 per category)
$car_types = [
    1 => ['GTR R35 Drift Spec', 'High Velocity', 'Turbo RS', 'Silvia S15', 'RX-7 FD Pro', 'AE86 Initial-D', 'Supra Drift King', '370Z Slide', 'Chaser JZX100', '180SX Pro', 'BRZ Drift', 'Mustang RTR', 'Charger Slide', 'M3 Drift', 'NSX Pro', 'Skyline R32', 'Fairlady Z', 'S2000 Pro', 'Cresta JZX100', 'Laurel C33'],
    2 => ['Traxxas Maxx', 'Xports Monster', 'Summit 4WD', 'Kraton 6S', 'E-Revo VXL', 'X-Maxx 8S', 'Slash 4X4', 'Stampede VXL', 'Rustler 4X4', 'Hoss VXL', 'TRX-4 Defender', 'SCX10 III', 'Capra 1.9', 'Ryft RBX10', 'Bomber RR10', 'Wraith 1.9', 'LMT Grave', 'Sledge 6S', 'Outcast 8S', 'Infraction V3'],
    3 => ['Lamborghini Sian', 'Speed Pack', 'Porsche 911 GT3', 'Ferrari F8', 'McLaren 720S', 'Audi R8 LMS', 'Ford GT Pro', 'Corvette C8.R', 'Bugatti Chiron', 'Aston Martin V12', 'AMG GT3', 'Viper ACR', 'GT-R GT3', 'NSX-GT', 'Supra GT4', 'M4 GT3', '911 RSR', 'Huracan GT3', '488 GTE', '720S GT3'],
    4 => ['F-150 Raptor', 'Offroad Special', 'Jeep Gladiator', 'Ram TRX Pro', 'Toyota Hilux', 'Land Cruiser', 'Defender 110', 'Power Wagon', 'Colorado ZR2', 'Titan Warrior', 'Silverado ZR2', 'Bronco Wildtrak', 'Cherokee XJ', 'Pajero EVO', 'Patrol Y61', 'Unimog U5000', 'Baja Rey 2.0', 'Trophy Truck', 'Rock Racer', 'Desert Speed'],
    5 => ['Xports Special 01', 'Track Star 02', 'Speed Demon 03', 'Apex Racer 04', 'Nitro King 05', 'Drift Master 06', 'High Octane 07', 'Turbo Charged 08', 'Racing Spec 09', 'Performance 10', 'Xports Special 11', 'Track Star 12', 'Speed Demon 13', 'Apex Racer 14', 'Nitro King 15', 'Drift Master 16', 'High Octane 17', 'Turbo Charged 18', 'Racing Spec 19', 'Performance 20']
];

$subtitles = ['Pro Series', 'Elite Edition', 'Special Spec', 'Racing Grade', 'Collector Choice', 'Super Drift', 'Performance', 'Retro Tech', 'Next Gen', 'Track King'];
$badges = [null, 'NEW', 'RARE', 'LIMITED', 'FLAGSHIP', 'PERFORMANCE', 'RETRO'];

$stmt = $db->prepare("INSERT INTO products (category_id, name, subtitle, price, rating, review_count, image_url, badge, description, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($car_types as $cat_id => $names) {
    foreach ($names as $idx => $name) {
        $subtitle = $subtitles[array_rand($subtitles)];
        $price = rand(5000, 45000);
        $rating = rand(42, 50) / 10;
        $reviews = rand(10, 350);
        $badge = $badges[array_rand($badges)];
        $is_featured = ($idx < 2) ? 1 : 0; 
        
        // Use varied unsplash for better theme consistency (verified active IDs)
        // Image pools for variety
        $image_pools = [
            1 => ['drift_cat.png', 'blue_gt_racer.png', 'p1.png', 'p2.png', 'racing_cat.png'], // Drift
            2 => ['monster_truck.png', 'offroad_cat.png', 'yellow_buggy.png', 'kids_cat.png'], // Monster
            3 => ['racing_cat.png', 'hero_car.png', 'p3.png', 'p4.png', 'blue_gt_racer.png'], // Speed
            4 => ['offroad_cat.png', 'monster_truck.png', 'p1.png', 'p2.png', 'yellow_buggy.png'], // Custom
            5 => ['hero_car.png', 'blue_gt_racer.png', 'p3.png', 'p4.png', 'racing_cat.png']  // Vault
        ];
        
        $pool = $image_pools[$cat_id] ?? ['hero_car.png'];
        // Use loop index $i to cycle through images
        $img_name = $pool[($i - 1) % count($pool)];
        $image_url = "assets/images/" . $img_name; 
        
        $description = "Professional grade high-performance remote control model of the $name. Engineered for precision $subtitle. Perfect for competitive racing and technical performance.";

        $stmt->execute([
            $cat_id, $name, $subtitle, $price, $rating, $reviews, $image_url, $badge, $description, $is_featured
        ]);
    }
}

echo "Database initialized and seeded with 100 products and 5 categories successfully!";
?>
