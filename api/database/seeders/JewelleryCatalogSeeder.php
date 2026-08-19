<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JewelleryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            'rings' => [
                'name' => 'Rings',
                'description' => 'Solitaires, couple promise bands, cocktail accents & 925 sterling silver rings.',
                'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=600&auto=format&fit=crop',
            ],
            'earrings' => [
                'name' => 'Earrings',
                'description' => 'Solitaire studs, hoops, huggies, teardrop danglers, and heritage jhumkas.',
                'image' => 'https://images.unsplash.com/photo-1635767798638-3e25273a8236?q=80&w=600&auto=format&fit=crop',
            ],
            'necklaces' => [
                'name' => 'Necklaces & Pendants',
                'description' => 'Heart lockets, chains, chokers, personalised name necklaces & solitaire pendants.',
                'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=600&auto=format&fit=crop',
            ],
            'bracelets' => [
                'name' => 'Bracelets & Bangles',
                'description' => 'Continuous tennis bracelets, charm cuffs, evil eye talismans & mangalsutra bracelets.',
                'image' => 'https://images.unsplash.com/photo-1611591475850-8b1b22e1a3b5?q=80&w=600&auto=format&fit=crop',
            ],
            'gold-lab-diamonds' => [
                'name' => 'Gold & Lab Diamonds',
                'description' => '14K & 18K solid real gold certified with ethically grown DEF color lab diamonds.',
                'image' => 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?q=80&w=600&auto=format&fit=crop',
            ],
            'silver-jewellery' => [
                'name' => '925 Sterling Silver',
                'description' => 'Pure 925 sterling silver hallmarked with anti-tarnish rhodium protective coating.',
                'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=600&auto=format&fit=crop',
            ],
            'mangalsutra' => [
                'name' => 'Modern Mangalsutra',
                'description' => 'Contemporary minimalist and everyday solitaires for modern brides.',
                'image' => 'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?q=80&w=600&auto=format&fit=crop',
            ],
            'mens-jewellery' => [
                'name' => "Men's Jewellery",
                'description' => 'Diamond-cut Cuban chains, rugged oxidized rings, and masculine silver bracelets.',
                'image' => 'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?q=80&w=600&auto=format&fit=crop',
            ],
            'gifting-edits' => [
                'name' => 'Gifts & Hampers',
                'description' => 'Curated luxury gift boxes with velvet pouch, certificate card, and personal note.',
                'image' => 'https://images.unsplash.com/photo-1513094735237-8f2714d57c13?q=80&w=600&auto=format&fit=crop',
            ],
        ];

        foreach ($categories as $slug => $data) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image' => $data['image'],
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $categoryMap = DB::table('categories')->pluck('id', 'slug');

        $products = [
            [
                'name' => 'Silver Classic Solitaire Ring',
                'slug' => 'silver-classic-solitaire-ring',
                'category_slug' => 'rings',
                'price' => 3499,
                'sale_price' => 1999,
                'stock' => 50,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 1420,
                'material' => '925 Sterling Silver • AAA+ CZ Solitaire',
                'short_desc' => 'A breathtaking 6-prong 1.5 Carat Solitaire ring crafted in pure 925 Sterling Silver with anti-tarnish rhodium plating.',
                'description' => 'Nothing commands timeless elegance quite like a classic solitaire. The Silver Classic Solitaire Ring features a laser-cut AAA+ grade Cubic Zirconia stone set in a secure 6-prong 925 sterling silver basket. Comes with an Authenticity Certificate and signature velvet gift box.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => 'Rose Gold Eternal Heart Loop Necklace',
                'slug' => 'rose-gold-heart-loop-necklace',
                'category_slug' => 'necklaces',
                'price' => 4299,
                'sale_price' => 2499,
                'stock' => 40,
                'is_featured' => true,
                'avg_rating' => 4.8,
                'review_count' => 980,
                'material' => '925 Sterling Silver • 18K Rose Gold Plated',
                'short_desc' => 'Interlocking dual heart pendant accented with micro-pave crystals in warm 18K Rose Gold plating.',
                'description' => 'Celebrate infinite affection with the Rose Gold Eternal Heart Loop Necklace. Two entwined hearts suspend from an adjustable fine silver chain.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => '925 Silver Classic Tennis Charm Bracelet',
                'slug' => 'classic-tennis-charm-bracelet',
                'category_slug' => 'bracelets',
                'price' => 5999,
                'sale_price' => 3299,
                'stock' => 30,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 750,
                'material' => '925 Sterling Silver • AAA+ Swiss CZ',
                'short_desc' => 'A seamless continuous line of brilliant-cut crystals bezel-set in flexible 925 Sterling Silver.',
                'description' => 'The quintessential icon of glamour. Our Silver Classic Tennis Bracelet features individually hand-set brilliant stones linked by flexible articulated silver joints.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1611591475850-8b1b22e1a3b5?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => 'Sparkling Solitaire Drop Earrings',
                'slug' => 'sparkling-crystal-drop-earrings',
                'category_slug' => 'earrings',
                'price' => 2999,
                'sale_price' => 1799,
                'stock' => 45,
                'is_featured' => true,
                'avg_rating' => 4.8,
                'review_count' => 640,
                'material' => '925 Sterling Silver • Pear-cut CZ',
                'short_desc' => 'Graceful teardrop crystals that catch the light with every movement, crafted in 925 Silver.',
                'description' => 'Add instant radiance to your face with these Sparkling Solitaire Drop Earrings. Featuring a pear-cut crystal suspended beneath a sparkling stud.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1635767798638-3e25273a8236?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => '18K Solid Gold & Lab Diamond Pendant',
                'slug' => '18k-gold-lab-diamond-pendant',
                'category_slug' => 'gold-lab-diamonds',
                'price' => 14999,
                'sale_price' => 8999,
                'stock' => 15,
                'is_featured' => true,
                'avg_rating' => 5.0,
                'review_count' => 320,
                'material' => '18K Yellow Gold (Hallmarked) • 0.50 Ct IGI Lab Diamond',
                'short_desc' => 'Real 18K solid yellow gold holding an IGI-certified 0.50 Ct brilliant round lab-grown diamond.',
                'description' => 'Invest in conscious luxury with our flagship 18K Gold Lab Diamond Solitaire Pendant. Hand-set in certified 18K hallmarked solid yellow gold.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => 'Rose Gold Evil Eye Charm Bracelet',
                'slug' => 'evil-eye-protection-charm-bracelet',
                'category_slug' => 'bracelets',
                'price' => 2899,
                'sale_price' => 1699,
                'stock' => 55,
                'is_featured' => true,
                'avg_rating' => 4.8,
                'review_count' => 1120,
                'material' => '925 Sterling Silver • 18K Rose Gold • Blue Enamel',
                'short_desc' => 'Protective Greek Evil Eye talisman with sapphire blue enamel and pave cubic zirconia.',
                'description' => 'Wear your good vibes and ward off negativity. This dainty evil eye charm bracelet features rich hand-applied cobalt blue and turquoise enamel.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => "Men's 925 Sterling Silver Cuban Chain",
                'slug' => 'mens-cuban-link-silver-chain',
                'category_slug' => 'mens-jewellery',
                'price' => 7499,
                'sale_price' => 4499,
                'stock' => 25,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 510,
                'material' => 'Solid 925 Sterling Silver (Heavy 14g)',
                'short_desc' => 'Heavy 5mm diamond-cut bevelled Cuban link chain in pure hallmarked 925 Sterling Silver.',
                'description' => 'Bold, confident, and unapologetically stylish. Our Men\'s Cuban Chain is engineered from solid 925 sterling silver with diamond-cut bevels.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1611591475850-8b1b22e1a3b5?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
            [
                'name' => 'Forever Love Silver Couple Promise Bands',
                'slug' => 'forever-love-couple-promise-rings',
                'category_slug' => 'rings',
                'price' => 6499,
                'sale_price' => 3799,
                'stock' => 35,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 890,
                'material' => 'Pair of 925 Sterling Silver Rings',
                'short_desc' => 'A matching pair of his & her adjustable promise rings engraved with subtle comfort-fit band.',
                'description' => 'A symbol of shared dreams and unbreakable bonds. This pair of matching promise rings is crafted in pure 925 Sterling Silver.',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?q=80&w=800&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1605100804763-247f67b3557e?q=80&w=800&auto=format&fit=crop',
                ]),
            ],
        ];

        foreach ($products as $prod) {
            $catId = $categoryMap[$prod['category_slug']] ?? null;
            DB::table('products')->updateOrInsert(
                ['slug' => $prod['slug']],
                [
                    'category_id' => $catId,
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'sale_price' => $prod['sale_price'],
                    'stock' => $prod['stock'],
                    'is_featured' => $prod['is_featured'],
                    'avg_rating' => $prod['avg_rating'],
                    'review_count' => $prod['review_count'],
                    'material' => $prod['material'],
                    'short_desc' => $prod['short_desc'],
                    'description' => $prod['description'],
                    'images' => $prod['images'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
