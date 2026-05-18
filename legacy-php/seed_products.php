<?php
/**
 * Demo Product Seeding Script
 * Populates the database with 5 luxury masterpieces.
 */
require_once __DIR__ . '/config/db.php';
$db = getDB();

$products = [
    [
        'category_id' => 3, // Statement Necklaces
        'name'        => 'Imperial Maharani Haar',
        'slug'        => 'imperial-maharani-haar',
        'description' => 'A royal masterpiece featuring recursive Kundan gold work and deep green emeralds. This multi-layered "Haar" is inspired by the heritage of the Indian royalty.',
        'short_desc'  => 'Luxurious multi-layered Kundan & Emerald necklace.',
        'price'       => 1450000,
        'stock'       => 2,
        'sku'         => 'JW-MH-001',
        'images'      => json_encode(['uploads/products/maharani-haar.png']),
        'is_featured' => 1
    ],
    [
        'category_id' => 2, // Luxury Rings
        'name'        => 'Eternal Solitaire Vow',
        'slug'        => 'eternal-solitaire-vow',
        'description' => 'A 5-carat sparkling diamond solitaire set on an 22k solid gold band. Packaged in a signature burgundy velvet box.',
        'short_desc'  => '5-carat diamond solitaire on 22k gold.',
        'price'       => 820000,
        'stock'       => 5,
        'sku'         => 'JW-SR-002',
        'images'      => json_encode(['uploads/products/solitaire-vow.png']),
        'is_featured' => 1
    ],
    [
        'category_id' => 1, // Divine Sculptures
        'name'        => 'Heirloom Gaja Ganesha',
        'slug'        => 'heirloom-gaja-ganesha',
        'description' => 'Hand-engraved antique brass Ganesha sculpture. A heritage piece representing wisdom and auspicious beginnings.',
        'short_desc'  => 'Antique brass Ganesha with intricate engravings.',
        'price'       => 340000,
        'stock'       => 3,
        'sku'         => 'DS-GG-003',
        'images'      => json_encode(['uploads/products/gaja-ganesha.png']),
        'is_featured' => 1
    ],
    [
        'category_id' => 4, // Dainty Earrings
        'name'        => 'Celestial Drop Earrings',
        'slug'        => 'celestial-drop-earrings',
        'description' => 'Minimalist modern rose gold drop earrings featuring delicate freshwater pearl accents. Perfect for elegant evening wear.',
        'short_desc'  => 'Rose gold and pearl minimalist drop earrings.',
        'price'       => 120000,
        'stock'       => 10,
        'sku'         => 'JW-DE-004',
        'images'      => json_encode(['uploads/products/celestial-earrings.png']),
        'is_featured' => 1
    ],
    [
        'category_id' => 5, // Temple Jewelry
        'name'        => 'Temple Gatha Kada',
        'slug'        => 'temple-gatha-kada',
        'description' => 'A heavy 24k gold plated Temple Jewelry Kada with exquisite relief carvings of deities and semi-precious stone work.',
        'short_desc'  => 'Heavy gold Kada with deity stone work.',
        'price'       => 580000,
        'stock'       => 4,
        'sku'         => 'JW-TK-005',
        'images'      => json_encode(['uploads/products/temple-kada.png']),
        'is_featured' => 1
    ]
];

header('Content-Type: text/plain');
echo "Seeding Demo Products...\n\n";

foreach ($products as $p) {
    try {
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$p['slug']]);
        if ($stmt->fetch()) {
            echo "Product '{$p['name']}' already exists.\n";
            continue;
        }

        // Insert
        $sql = "INSERT INTO products (category_id, name, slug, description, short_desc, price, stock, sku, images, is_featured, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $p['category_id'], $p['name'], $p['slug'], $p['description'], 
            $p['short_desc'], $p['price'], $p['stock'], $p['sku'], 
            $p['images'], $p['is_featured']
        ]);
        echo "SUCCESS: Seeded '{$p['name']}'.\n";
    } catch (Exception $e) {
        echo "ERROR: {$p['name']} -> " . $e->getMessage() . "\n";
    }
}

echo "\n--- Seeding Complete ---";
?>
