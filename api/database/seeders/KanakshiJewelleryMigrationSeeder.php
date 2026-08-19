<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KanakshiJewelleryMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        }

        // Delete all old products and non-jewellery data
        DB::table('product_variants')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();

        // 1. Insert 9 Core Fine Jewellery Categories
        $categories = [
            [
                'name' => 'Rings',
                'slug' => 'rings',
                'description' => 'Solitaires, couple promise bands, cocktail accents & 925 sterling silver rings.',
                'image' => '/jewellery/solitaire-ring.jpg',
                'sort_order' => 1,
            ],
            [
                'name' => 'Earrings',
                'slug' => 'earrings',
                'description' => 'Solitaire studs, hoops, huggies, teardrop danglers, and heritage jhumkas.',
                'image' => '/jewellery/drop-earrings.jpg',
                'sort_order' => 2,
            ],
            [
                'name' => 'Necklaces & Pendants',
                'slug' => 'necklaces',
                'description' => 'Heart lockets, chains, chokers, personalised name necklaces & solitaire pendants.',
                'image' => '/jewellery/heart-necklace.jpg',
                'sort_order' => 3,
            ],
            [
                'name' => 'Bracelets & Bangles',
                'slug' => 'bracelets',
                'description' => 'Continuous tennis bracelets, charm cuffs, evil eye talismans & mangalsutra bracelets.',
                'image' => '/jewellery/tennis-bracelet.jpg',
                'sort_order' => 4,
            ],
            [
                'name' => 'Gold & Lab Diamonds',
                'slug' => 'gold-lab-diamonds',
                'description' => '14K & 18K solid real gold certified with ethically grown DEF color lab diamonds.',
                'image' => '/jewellery/gold-pendant.jpg',
                'sort_order' => 5,
            ],
            [
                'name' => '925 Sterling Silver',
                'slug' => 'silver-jewellery',
                'description' => 'Pure 925 sterling silver hallmarked with anti-tarnish rhodium protective coating.',
                'image' => '/jewellery/silver-collection.jpg',
                'sort_order' => 6,
            ],
            [
                'name' => 'Modern Mangalsutra',
                'slug' => 'mangalsutra',
                'description' => 'Contemporary minimalist and everyday solitaires for modern brides.',
                'image' => '/jewellery/mangalsutra-collection.jpg',
                'sort_order' => 7,
            ],
            [
                'name' => "Men's Jewellery",
                'slug' => 'mens-jewellery',
                'description' => 'Diamond-cut Cuban chains, rugged oxidized rings, and masculine silver bracelets.',
                'image' => '/jewellery/mens-cuban-chain.jpg',
                'sort_order' => 8,
            ],
            [
                'name' => 'Gifts & Hampers',
                'slug' => 'gifting-edits',
                'description' => 'Curated luxury gift boxes with velvet pouch, certificate card, and personal note.',
                'image' => '/jewellery/gift-set-combo.jpg',
                'sort_order' => 9,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'image' => $cat['image'],
                    'is_active' => true,
                    'sort_order' => $cat['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $categoryMap = DB::table('categories')->pluck('id', 'slug');

        // 2. Comprehensive Products for EVERY single category
        $products = [
            // --- RINGS ---
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
                'bullet_points' => json_encode([
                    '925 Sterling Silver with Hallmarking stamp',
                    'Brilliant 1.5 Carat AAA+ Cubic Zirconia centre stone',
                    'Anti-Tarnish Rhodium Finish for lifelong shine',
                    'Includes Certificate of Authenticity & Luxury Box',
                    'Hypoallergenic & nickel-free for sensitive skin'
                ]),
                'images' => json_encode([
                    '/jewellery/solitaire-ring.jpg',
                    '/jewellery/couple-promise-rings.jpg',
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
                'description' => 'A symbol of shared dreams and unbreakable bonds. This pair of matching promise rings is crafted in pure 925 Sterling Silver with comfort-fit curved edges.',
                'bullet_points' => json_encode([
                    'Includes set of 2 rings (1 Men\'s band, 1 Women\'s Solitaire band)',
                    'Free-size adjustable fit for both rings',
                    '925 Sterling Silver hallmarked'
                ]),
                'images' => json_encode([
                    '/jewellery/couple-promise-rings.jpg',
                    '/jewellery/solitaire-ring.jpg',
                ]),
            ],
            [
                'name' => 'Infinity Sparkle Stackable Silver Ring',
                'slug' => 'infinity-sparkle-stackable-ring',
                'category_slug' => 'rings',
                'price' => 2999,
                'sale_price' => 1699,
                'stock' => 45,
                'is_featured' => false,
                'avg_rating' => 4.8,
                'review_count' => 620,
                'material' => '925 Sterling Silver • Micro-Pavé Stones',
                'short_desc' => 'Delicate interlocking infinity loop studded with micro-pavé Swiss cubic zirconia.',
                'description' => 'Wrap your finger in continuous brilliance with our Infinity Sparkle Stackable Ring. Perfect for stacking or wearing as an everyday dainty signature.',
                'bullet_points' => json_encode([
                    'Pure 925 Sterling Silver',
                    'High-grade micro-pavé setting',
                    'Smooth inner band for daily comfort'
                ]),
                'images' => json_encode([
                    '/jewellery/solitaire-ring.jpg',
                    '/jewellery/couple-promise-rings.jpg',
                ]),
            ],

            // --- EARRINGS ---
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
                'bullet_points' => json_encode([
                    '925 Pure Silver with BIS certification',
                    'Lightweight ergonomic drop design',
                    'Secure push-back butterfly closure',
                    'Anti-allergenic rhodium polish'
                ]),
                'images' => json_encode([
                    '/jewellery/drop-earrings.jpg',
                    '/jewellery/solitaire-ring.jpg',
                ]),
            ],
            [
                'name' => 'Classic 925 Silver Solitaire Studs',
                'slug' => 'classic-solitaire-silver-studs',
                'category_slug' => 'earrings',
                'price' => 2499,
                'sale_price' => 1399,
                'stock' => 60,
                'is_featured' => false,
                'avg_rating' => 4.9,
                'review_count' => 1830,
                'material' => '925 Sterling Silver • 1.0 Ct AAA+ CZ Studs',
                'short_desc' => 'Essential 1.0 Carat solitaire ear studs in hypoallergenic rhodium-plated sterling silver.',
                'description' => 'The ultimate everyday fine jewellery staple. Clean, brilliant 4-prong basket studs that elevate your boardroom look and dinner dates alike.',
                'bullet_points' => json_encode([
                    '100% 925 Sterling Silver certified',
                    '1.00 Carat brilliant round cut stones',
                    'Hypoallergenic for 24/7 comfortable wear'
                ]),
                'images' => json_encode([
                    '/jewellery/drop-earrings.jpg',
                    '/jewellery/gold-pendant.jpg',
                ]),
            ],
            [
                'name' => 'Rose Gold Pave Huggie Hoops',
                'slug' => 'rose-gold-huggie-hoop-earrings',
                'category_slug' => 'earrings',
                'price' => 3299,
                'sale_price' => 1899,
                'stock' => 40,
                'is_featured' => false,
                'avg_rating' => 4.7,
                'review_count' => 490,
                'material' => '925 Sterling Silver • 18K Rose Gold Plated',
                'short_desc' => 'Dainty mini huggie hoops lined with sparkling crystals in radiant rose gold finish.',
                'description' => 'Seamless click-top huggie hoops that hug your earlobes in radiant 18K rose gold and brilliant pavé stones.',
                'bullet_points' => json_encode([
                    'Click-latch closure for ultimate safety',
                    '18K Rose Gold protective plating',
                    'Diameter: 12mm'
                ]),
                'images' => json_encode([
                    '/jewellery/drop-earrings.jpg',
                    '/jewellery/heart-necklace.jpg',
                ]),
            ],

            // --- NECKLACES & PENDANTS ---
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
                'bullet_points' => json_encode([
                    'Authentic 925 Sterling Silver core',
                    '18K Rose Gold electro-plated with protective E-Coat',
                    'Chain length: 16 inches + 2-inch extension',
                    'Secured with sturdy lobster claw clasp',
                    '6-Month Plating Guarantee included'
                ]),
                'images' => json_encode([
                    '/jewellery/heart-necklace.jpg',
                    '/jewellery/gold-pendant.jpg',
                ]),
            ],
            [
                'name' => 'Solitaire Dewdrop Silver Choker Necklace',
                'slug' => 'solitaire-crystal-silver-choker',
                'category_slug' => 'necklaces',
                'price' => 3899,
                'sale_price' => 2199,
                'stock' => 30,
                'is_featured' => false,
                'avg_rating' => 4.9,
                'review_count' => 540,
                'material' => '925 Sterling Silver • Floating Solitaire',
                'short_desc' => 'A floating solitaire stone suspended gracefully along a fine faceted rhodium chain.',
                'description' => 'Minimalist modern elegance. The Solitaire Dewdrop necklace catches the neckline with effortless sophistication.',
                'bullet_points' => json_encode([
                    'Pure 925 Sterling Silver',
                    '0.75 Carat brilliant solitaire drop',
                    'Adjustable chain with hallmarked tag'
                ]),
                'images' => json_encode([
                    '/jewellery/heart-necklace.jpg',
                    '/jewellery/drop-earrings.jpg',
                ]),
            ],

            // --- BRACELETS & BANGLES ---
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
                'bullet_points' => json_encode([
                    'Hallmarked 925 Sterling Silver structure',
                    'Double safety clasp for secure wear',
                    'Stone size: 3mm each, total 4.2 Carats',
                    'Length: 7 inches with removable extender link',
                    'Complimentary silver polishing cloth included'
                ]),
                'images' => json_encode([
                    '/jewellery/tennis-bracelet.jpg',
                    '/jewellery/evil-eye-bracelet.jpg',
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
                'bullet_points' => json_encode([
                    '925 Sterling Silver with 18K Rose Gold Plating',
                    'Handcrafted enamel evil eye talisman',
                    'Adjustable sliding ball mechanism fits all wrist sizes'
                ]),
                'images' => json_encode([
                    '/jewellery/evil-eye-bracelet.jpg',
                    '/jewellery/tennis-bracelet.jpg',
                ]),
            ],
            [
                'name' => 'Dainty Butterfly Sparkle Silver Bracelet',
                'slug' => 'dainty-butterfly-charm-silver-bracelet',
                'category_slug' => 'bracelets',
                'price' => 3199,
                'sale_price' => 1799,
                'stock' => 35,
                'is_featured' => false,
                'avg_rating' => 4.8,
                'review_count' => 430,
                'material' => '925 Sterling Silver • CZ Pavé',
                'short_desc' => 'Whimsical butterfly motif flanked by bezel crystals on an adjustable silver chain.',
                'description' => 'Symbolizing transformation and grace, this butterfly bracelet brings delicate charm to your daily wrist stack.',
                'bullet_points' => json_encode([
                    '925 Sterling Silver certified',
                    'Adjustable link extender',
                    'Anti-tarnish rhodium coating'
                ]),
                'images' => json_encode([
                    '/jewellery/tennis-bracelet.jpg',
                    '/jewellery/evil-eye-bracelet.jpg',
                ]),
            ],

            // --- GOLD & LAB DIAMONDS ---
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
                'bullet_points' => json_encode([
                    'Real 18K Solid Gold (BIS Hallmarked)',
                    'IGI Certificate of Authenticity card included',
                    '0.50 Carat DEF Color, VVS Clarity Lab-Grown Diamond'
                ]),
                'images' => json_encode([
                    '/jewellery/gold-pendant.jpg',
                    '/jewellery/heart-necklace.jpg',
                ]),
            ],
            [
                'name' => '18K Solid Gold 1.00 Ct Lab Diamond Solitaire Ring',
                'slug' => '18k-gold-solitaire-lab-diamond-ring',
                'category_slug' => 'gold-lab-diamonds',
                'price' => 28999,
                'sale_price' => 19999,
                'stock' => 10,
                'is_featured' => true,
                'avg_rating' => 5.0,
                'review_count' => 210,
                'material' => '18K Solid Gold • 1.00 Ct Certified Lab Diamond',
                'short_desc' => 'An opulent 1.00 Carat IGI certified lab diamond ring set in 18K hallmarked solid gold.',
                'description' => 'The epitome of sustainable luxury. Featuring a certified 1.00 Carat round brilliant lab diamond in an 18K yellow gold band with tapered shoulders.',
                'bullet_points' => json_encode([
                    '18K Solid Gold (BIS Hallmarked)',
                    '1.00 Carat DEF Color VVS Clarity Lab Diamond',
                    'IGI Physical Certificate Card Included'
                ]),
                'images' => json_encode([
                    '/jewellery/gold-pendant.jpg',
                    '/jewellery/solitaire-ring.jpg',
                ]),
            ],

            // --- 925 STERLING SILVER ---
            [
                'name' => 'Pure 925 Silver Brilliant Solitaire Ring',
                'slug' => '925-sterling-silver-solitaire-ring',
                'category_slug' => 'silver-jewellery',
                'price' => 3299,
                'sale_price' => 1899,
                'stock' => 50,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 920,
                'material' => 'Pure 925 Sterling Silver • Rhodium E-Coat',
                'short_desc' => 'Mirror-polished 925 Silver band featuring an ultra-sparkle cubic zirconia solitaire.',
                'description' => 'Crafted from pure 925 sterling silver with a thick rhodium barrier to prevent oxidization and preserve pristine mirror shine.',
                'bullet_points' => json_encode([
                    'BIS Hallmarked 925 Stamp',
                    'Rhodium plated for tarnish resistance',
                    'Free luxury velvet gift box'
                ]),
                'images' => json_encode([
                    '/jewellery/solitaire-ring.jpg',
                    '/jewellery/tennis-bracelet.jpg',
                ]),
            ],
            [
                'name' => '925 Sterling Silver Royal Tennis Necklace',
                'slug' => '925-silver-shimmer-tennis-necklace',
                'category_slug' => 'silver-jewellery',
                'price' => 8999,
                'sale_price' => 4999,
                'stock' => 20,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 380,
                'material' => '925 Sterling Silver • Full Tennis CZ Collar',
                'short_desc' => 'A breathtaking all-around continuous river of sparkling crystals in solid 925 Silver.',
                'description' => 'Turn every head with the Royal Tennis Necklace. A cascading line of flawless crystals that illuminates your collarbone with red-carpet glamour.',
                'bullet_points' => json_encode([
                    'Solid 925 Sterling Silver framework',
                    'Articulated bezel cups for fluid movement',
                    'Double-locking luxury box clasp'
                ]),
                'images' => json_encode([
                    '/jewellery/tennis-bracelet.jpg',
                    '/jewellery/heart-necklace.jpg',
                ]),
            ],

            // --- MODERN MANGALSUTRA ---
            [
                'name' => 'Modern Solitaire 925 Silver Mangalsutra',
                'slug' => 'modern-solitaire-black-bead-mangalsutra',
                'category_slug' => 'mangalsutra',
                'price' => 3999,
                'sale_price' => 2299,
                'stock' => 40,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 780,
                'material' => '925 Sterling Silver • Traditional Black Beads',
                'short_desc' => 'A contemporary 1-Carat solitaire pendant balanced by sacred black beads on a silver chain.',
                'description' => 'Redefining marital jewellery for the modern woman. This sleek solitaire mangalsutra effortlessly complements western formals as well as ethnic wear.',
                'bullet_points' => json_encode([
                    '925 Pure Silver with Rhodium protection',
                    'Authentic sacred black spinel beads',
                    'Chain length: 16 inches + 2 inches extender'
                ]),
                'images' => json_encode([
                    '/jewellery/heart-necklace.jpg',
                    '/jewellery/gold-pendant.jpg',
                ]),
            ],
            [
                'name' => 'Daily Wear Black Bead Mangalsutra Bracelet',
                'slug' => 'dainty-black-bead-mangalsutra-bracelet',
                'category_slug' => 'mangalsutra',
                'price' => 2999,
                'sale_price' => 1799,
                'stock' => 35,
                'is_featured' => true,
                'avg_rating' => 4.8,
                'review_count' => 610,
                'material' => '925 Sterling Silver • 18K Rose Gold • Black Beads',
                'short_desc' => 'Minimalist modern wrist mangalsutra with alternating gold links and black beads.',
                'description' => 'A chic everyday wrist alternative to the traditional neckpiece. Lightweight, comfortable, and meaningful.',
                'bullet_points' => json_encode([
                    '18K Rose Gold plated 925 Silver',
                    'Adjustable slide closure mechanism',
                    'Hypoallergenic for 24/7 daily wear'
                ]),
                'images' => json_encode([
                    '/jewellery/evil-eye-bracelet.jpg',
                    '/jewellery/tennis-bracelet.jpg',
                ]),
            ],

            // --- MEN'S JEWELLERY ---
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
                'bullet_points' => json_encode([
                    'Solid 925 Sterling Silver (approx 14.5 grams)',
                    '5mm flat curb chain profile',
                    'Length: 20 inches with heavy-duty lobster clasp'
                ]),
                'images' => json_encode([
                    '/jewellery/mens-cuban-chain.jpg',
                    '/jewellery/mens-cuban-chain.jpg',
                ]),
            ],
            [
                'name' => "Men's Solid Silver Bold Signet Ring",
                'slug' => 'mens-oxidised-lion-shield-ring',
                'category_slug' => 'mens-jewellery',
                'price' => 3999,
                'sale_price' => 2499,
                'stock' => 30,
                'is_featured' => false,
                'avg_rating' => 4.8,
                'review_count' => 390,
                'material' => 'Solid 925 Sterling Silver (Heavy Band)',
                'short_desc' => 'Subtle brushed matte top with mirror-polished bevelled edges in solid silver.',
                'description' => 'A statement of quiet masculine strength. Features a solid 925 silver core with comfortable curved inner profile for all-day wear.',
                'bullet_points' => json_encode([
                    'Solid Hallmarked 925 Silver',
                    'Comfort-fit inner core',
                    'Resistant to scratches and everyday wear'
                ]),
                'images' => json_encode([
                    '/jewellery/mens-cuban-chain.jpg',
                    '/jewellery/solitaire-ring.jpg',
                ]),
            ],

            // --- GIFTS & HAMPERS ---
            [
                'name' => "Couple's Forever Promise Luxury Gift Hamper",
                'slug' => 'couples-forever-promise-luxury-hamper',
                'category_slug' => 'gifting-edits',
                'price' => 7999,
                'sale_price' => 4999,
                'stock' => 25,
                'is_featured' => true,
                'avg_rating' => 5.0,
                'review_count' => 640,
                'material' => 'Set of 2 925 Silver Rings + Velvet Presentation Box',
                'short_desc' => 'The ultimate gifting combo: Matching promise rings, luxury velvet box, scented candle & certificate.',
                'description' => 'Designed to create unforgettable memories. This gift hamper includes matching 925 Silver Promise Bands packaged in an LED-lit velvet presentation box with authenticity certificate.',
                'bullet_points' => json_encode([
                    'Includes pair of adjustable 925 Silver rings',
                    'Luxury LED velvet keepsake gift box',
                    'Personalized gift message card included'
                ]),
                'images' => json_encode([
                    '/jewellery/couple-promise-rings.jpg',
                    '/jewellery/solitaire-ring.jpg',
                ]),
            ],
            [
                'name' => 'Royal Solitaire Pendant & Earrings Gift Set',
                'slug' => 'royal-solitaire-pendant-earrings-combo',
                'category_slug' => 'gifting-edits',
                'price' => 5999,
                'sale_price' => 3499,
                'stock' => 30,
                'is_featured' => true,
                'avg_rating' => 4.9,
                'review_count' => 820,
                'material' => '925 Sterling Silver Combo Set',
                'short_desc' => 'Matching solitaire pendant necklace and stud earrings in a signature pink velvet box.',
                'description' => 'Give the complete ensemble. A 1.0 Carat solitaire pendant paired with matching solitaire studs in hallmarked 925 Sterling Silver.',
                'bullet_points' => json_encode([
                    'Complete 2-piece fine jewellery set',
                    '925 Sterling Silver with Rhodium protection',
                    'Signature luxury velvet unboxing experience'
                ]),
                'images' => json_encode([
                    '/jewellery/heart-necklace.jpg',
                    '/jewellery/drop-earrings.jpg',
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
                    'bullet_points' => $prod['bullet_points'],
                    'images' => $prod['images'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 3. Update store settings
        DB::table('store_settings')->updateOrInsert(
            ['id' => 1],
            [
                'site_name' => 'Kanakshi Fine Jewellery',
                'site_tagline' => 'Everyday Luxury Fine Jewellery | 925 Sterling Silver, Gold & Lab-Grown Diamonds',
                'business_name' => 'Kanakshi Fine Jewellery Pvt Ltd',
                'business_email' => 'care@kanakshi.in',
                'business_phone' => '+91 98765 43210',
                'support_email' => 'care@kanakshi.in',
                'support_phone' => '+91 98765 43210',
                'whatsapp_number' => '+91 98765 43210',
                'address_line1' => 'Kanakshi Registered Office, DLF Horizon Plaza',
                'address_line2' => 'Golf Course Road, Sector 43',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'pincode' => '122002',
                'country' => 'India',
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'show_topbar' => true,
                'topbar_bg_color' => '#1a1a1a',
                'topbar_text_color' => '#ffffff',
                'topbar_offers' => json_encode([
                    'FLAT ₹500 OFF on Orders Above ₹2,999 | Code: SPARKLE500',
                    'Free Insured Express Delivery Across India',
                    '100% Certified 925 Sterling Silver & Hallmarked Gold',
                    '100% Anti-Tarnish Finish & Easy 7-Day Returns',
                ]),
                'updated_at' => $now,
            ]
        );

        // 3.1 Update Social Links
        DB::table('social_links')->truncate();
        $socials = [
            ['platform' => 'instagram', 'title' => 'Instagram', 'handle' => '@kanakshi.in', 'url' => 'https://instagram.com/kanakshi.in', 'icon' => 'instagram', 'sort_order' => 1],
            ['platform' => 'facebook', 'title' => 'Facebook', 'handle' => 'kanakshi.in', 'url' => 'https://facebook.com/kanakshi.in', 'icon' => 'facebook', 'sort_order' => 2],
            ['platform' => 'pinterest', 'title' => 'Pinterest', 'handle' => 'kanakshi.in', 'url' => 'https://pinterest.com/kanakshi.in', 'icon' => 'pinterest', 'sort_order' => 3],
            ['platform' => 'youtube', 'title' => 'YouTube', 'handle' => '@kanakshi.in', 'url' => 'https://youtube.com/@kanakshi.in', 'icon' => 'youtube', 'sort_order' => 4],
            ['platform' => 'whatsapp', 'title' => 'WhatsApp', 'handle' => '+91 98765 43210', 'url' => 'https://wa.me/919876543210', 'icon' => 'whatsapp', 'sort_order' => 5],
        ];
        foreach ($socials as $s) {
            DB::table('social_links')->insert([
                'platform' => $s['platform'],
                'title' => $s['title'],
                'handle' => $s['handle'],
                'url' => $s['url'],
                'icon' => $s['icon'],
                'sort_order' => $s['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 4. Update menu items
        DB::table('menu_items')->truncate();
        $headerMenus = [
            ['title' => 'All Jewellery', 'url' => '/shop', 'sort_order' => 1, 'location' => 'header'],
            ['title' => 'Gold & Lab Diamonds', 'url' => '/shop/gold-lab-diamonds', 'sort_order' => 2, 'location' => 'header'],
            ['title' => '925 Silver', 'url' => '/shop/silver-jewellery', 'sort_order' => 3, 'location' => 'header'],
            ['title' => 'Rings', 'url' => '/shop/rings', 'sort_order' => 4, 'location' => 'header'],
            ['title' => 'Earrings', 'url' => '/shop/earrings', 'sort_order' => 5, 'location' => 'header'],
            ['title' => 'Necklaces', 'url' => '/shop/necklaces', 'sort_order' => 6, 'location' => 'header'],
            ['title' => 'Bracelets', 'url' => '/shop/bracelets', 'sort_order' => 7, 'location' => 'header'],
            ['title' => 'Mangalsutra', 'url' => '/shop/mangalsutra', 'sort_order' => 8, 'location' => 'header'],
            ['title' => "Men's", 'url' => '/shop/mens-jewellery', 'sort_order' => 9, 'location' => 'header'],
            ['title' => 'Gifts', 'url' => '/shop/gifting-edits', 'sort_order' => 10, 'location' => 'header'],
        ];

        foreach ($headerMenus as $hm) {
            DB::table('menu_items')->insert([
                'title' => $hm['title'],
                'url' => $hm['url'],
                'sort_order' => $hm['sort_order'],
                'location' => $hm['location'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 5. Update homepage sections
        DB::table('homepage_sections')->truncate();
        DB::table('homepage_sections')->insert([
            'section_key' => 'hero',
            'section_type' => 'hero',
            'title' => 'Everyday Luxury Made For You',
            'heading' => 'Everyday Luxury Made For You',
            'content' => '100% Certified 925 Sterling Silver, 18K Real Gold & Ethical Lab-Grown Diamonds. Crafted with Anti-Tarnish Rhodium Finish.',
            'button_text' => 'Shop New Arrivals →',
            'button_url' => '/shop?sort=bestseller',
            'image_url' => '/jewellery/hero-banner.jpg',
            'config' => json_encode([
                'slides' => [
                    [
                        'alt' => 'The Solitaire Diamond & Silver Edit',
                        'title' => 'Everyday Luxury Made For You',
                        'subtitle' => '100% Certified 925 Sterling Silver, 18K Real Gold & Ethical Lab-Grown Diamonds.',
                        'image' => '/jewellery/hero-banner.jpg',
                        'href' => '/shop?sort=bestseller',
                        'is_active' => true,
                    ],
                    [
                        'alt' => '925 Sterling Silver Everlasting Collection',
                        'title' => 'Pure 925 Sterling Silver',
                        'subtitle' => 'Rhodium-coated everyday elegance designed for daily wear.',
                        'image' => '/jewellery/tennis-bracelet.jpg',
                        'href' => '/shop/silver-jewellery',
                        'is_active' => true,
                    ],
                ],
                'promos' => [],
            ]),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }
}
