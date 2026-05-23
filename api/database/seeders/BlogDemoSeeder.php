<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\BlogRevision;
use App\Models\Product;

class BlogDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing']) && !env('SEED_BLOG_DEMO', false)) {
            return;
        }

        $existingSlugs = [
            'designing-a-sacred-sanctuary-modern-pooja-room-styling-guides',
            'the-lost-wax-heritage-how-ancient-brass-idols-are-sculpted',
            'akhand-diya-sacred-symbolism-caring-for-festive-brass-lamps',
        ];

        if (BlogPost::whereIn('slug', $existingSlugs)->count() === count($existingSlugs)) {
            return;
        }

        // 1. Create Demo Author
        $author = BlogAuthor::firstOrCreate(['slug' => 'aditi-sharma'], [
            'name' => 'Aditi Sharma',
            'bio' => 'Aditi Sharma is a senior heritage stylist and home design consultant with over a decade of experience in Indian traditional aesthetics. She is passionate about reviving ancient brass craft techniques and integrating them into modern apartments.',
            'avatar' => null,
            'avatar_alt' => 'Aditi Sharma, Heritage Consultant',
            'twitter_handle' => 'aditi_stylist'
        ]);

        // 2. Create Demo Categories
        $cat1 = BlogCategory::firstOrCreate(['slug' => 'sacred-spaces'], [
            'name' => 'Sacred Spaces',
            'description' => 'Expert guides and traditional ideas for styling serene home pooja rooms, altars, and spiritual meditation corners.',
            'meta_title' => 'Home Pooja Room Design & Brass Styling Guides',
            'meta_description' => 'Explore professional Vastu and aesthetic ideas to style traditional and modern pooja room layouts with handcrafted brass elements.'
        ]);

        $cat2 = BlogCategory::firstOrCreate(['slug' => 'artisanal-craft'], [
            'name' => 'Artisanal Craft',
            'description' => 'Delving deep into the rich history, techniques, and multigenerational artisan communities behind Indian metalwork.',
            'meta_title' => 'Lost Wax Cast Brass History & Handcrafted Metalwork',
            'meta_description' => 'Uncover the ancient craft processes, history, and heritage stories of Indian metal casting and brass idol makers.'
        ]);

        $cat3 = BlogCategory::firstOrCreate(['slug' => 'spiritual-living'], [
            'name' => 'Spiritual Living',
            'description' => 'Daily wellness rituals, festival decoration guides, and mindful living recommendations for modern homes.',
            'meta_title' => 'Daily Wellness Rituals & Festival Styling Ideas',
            'meta_description' => 'Tips for incorporating spiritual wellness, traditional styling, and daily brass oil lamp rituals into a modern lifestyle.'
        ]);

        // 3. Create Demo Tags
        $tag1 = BlogTag::firstOrCreate(['slug' => 'brass-decor'], ['name' => 'Brass Decor']);
        $tag2 = BlogTag::firstOrCreate(['slug' => 'pooja-room-vastu'], ['name' => 'Pooja Room Vastu']);
        $tag3 = BlogTag::firstOrCreate(['slug' => 'lost-wax-craft'], ['name' => 'Lost Wax Craft']);
        $tag4 = BlogTag::firstOrCreate(['slug' => 'festival-styling'], ['name' => 'Festival Styling']);
        $tag5 = BlogTag::firstOrCreate(['slug' => 'care-and-cleaning'], ['name' => 'Care & Cleaning']);

        // 4. Fetch dynamic products to link as related products
        $productIds = Product::where('is_active', true)->limit(3)->pluck('id')->toArray();

        // 5. Create Blog Posts
        
        // Post 1: Sacred Spaces
        $post1 = BlogPost::create([
            'title' => 'Designing a Sacred Sanctuary: Modern Pooja Room Styling Guides',
            'slug' => 'designing-a-sacred-sanctuary-modern-pooja-room-styling-guides',
            'excerpt' => 'Discover how to blend traditional Vastu principles with contemporary minimalist design to create a serene, brass-accented pooja room corner.',
            'content' => '<h2>The Essence of a Home Altar</h2><p>In the bustle of modern life, a home pooja room or spiritual corner serves as an anchor of peace. Whether you have a dedicated room or a quiet alcove in your living room, styling this sacred space is a deeply personal and artistic ritual. Traditional home altars are transitioning away from heavy dark wood cabinetry toward open, airy, and light-filled spaces accented with exquisite brass artifacts.</p><blockquote>"The pooja corner is not just a place for daily rituals; it is a visual representation of silence, gratitude, and ancestral connection in the household."</blockquote><h2>1. Vastu Alignment: Direction and Lighting</h2><p>According to Vastu Shastra, the northeast direction (Ishanya corner) is the most auspicious quadrant for spiritual energy. When aligning your altar, place deities facing either East or West. For lighting, introduce a continuous soft glow. Avoid harsh white fluorescent bulbs; instead, embrace the warm flickers of pure brass oil lamps and soft, warm-colored spotlight downlights that highlight the handcarved features of your brass idols.</p><h2>2. The Art of Layering Brass Accents</h2><p>To create a cohesive, elevated aesthetic, layer your brass objects by size and purpose. Start with a prominent brass center Singhasan or high pedestal for the main deity. Place supporting accents such as brass oil cups (diyas), incense stand holders, and offering thali plates symmetrically around the base. A staggered height arrangement immediately builds depth and guides the eye toward the center of your altar.</p><ul><li><strong> Pedestals & Singhasans:</strong> Use elevated platforms to give focal deities prominence.</li><li><strong> Symmetric Diyas:</strong> Place brass diyas on both the left and right sides to frame the altar space beautifully.</li><li><strong> Natural Accents:</strong> Intersperse warm brass metals with fresh marigolds, green leaves, or jasmines to breathe life and color into the setting.</li></ul><h2>3. Maintaining Spiritual Longevity</h2><p>Pure brass develops a natural, beautiful rich patina over time due to contact with air and moisture. To maintain the bright golden-brass shine, wash your ceremonial pieces regularly using organic cleaners such as pitambari powder, lemon juice mixed with table salt, or tamarind paste. Avoid harsh steel wool scrubbing pads which can leave permanent micro-scratches on delicate hand-finished features.</p>',
            'featured_image' => '/demo-products/little-divinity-real-1.jpg',
            'featured_image_alt' => 'Elegant home pooja altar styled with warm brass diyas and idols',
            'blog_author_id' => $author->id,
            'blog_category_id' => $cat1->id,
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'meta_title' => 'How to Style a Modern Pooja Room: Vastu & Brass Altar Layouts',
            'meta_description' => 'Learn expert techniques to style a serene modern pooja room. Discover proper Vastu directions, deity pedestal layering, and organic brass care tips.',
            'primary_keyword' => 'pooja room styling',
            'secondary_keywords' => 'modern pooja room, vastu direction, brass diyas, home altar decor',
            'reading_time' => 4,
            'schema_type' => 'BlogPosting',
            'faq_json' => [
                ['question' => 'Which direction should the home pooja room face?', 'answer' => 'According to Vastu, the ideal direction is the Northeast quadrant. Deities should be placed such that they face East or West, and the practitioner faces East or North while performing daily rituals.'],
                ['question' => 'How can I clean brass idols naturally?', 'answer' => 'You can rub them gently with a paste of lemon juice and salt, or soak them briefly in warm tamarind water, then wash with warm water and buff with a soft microfiber dry cloth to restore the bright gold shine.']
            ],
            'related_products_json' => $productIds,
            'created_by' => 1,
            'updated_by' => 1,
            'last_updated_at' => now()
        ]);
        $post1->tags()->sync([$tag1->id, $tag2->id, $tag4->id]);
        
        BlogRevision::create([
            'blog_post_id' => $post1->id,
            'title' => $post1->title,
            'excerpt' => $post1->excerpt,
            'content' => $post1->content,
            'faq_json' => $post1->faq_json,
            'updated_by' => 1
        ]);

        // Post 2: Artisanal Craft
        $post2 = BlogPost::create([
            'title' => 'The Lost Wax Heritage: How Ancient Brass Idols are Sculpted',
            'slug' => 'the-lost-wax-heritage-how-ancient-brass-idols-are-sculpted',
            'excerpt' => 'Step inside our traditional metal casting studios to explore the ancient, multi-thousand-year-old Dhokra and Madhuchista Vidhana lost-wax sculpting craft.',
            'content' => '<h2>An Ancient Legacy Transmitted Over Millennia</h2><p>Behind every intricately detailed brass deity lies a profound metallurgical tradition that dates back over 4,500 years. The legendary "Dancing Girl" of Mohenjo-daro, cast during the Indus Valley Civilization, stands as a testament to India\'s mastery of the lost-wax casting technique—known historically in Sanskrit texts as <em>Madhuchista Vidhana</em>.</p><p>Today, this meticulous craft is kept alive by highly skilled, multigenerational artisan families in heritage casting clusters such as Aligarh, Moradabad, and Swamimalai. Each idol is entirely unique, requiring weeks of handcarving and molten metal shaping.</p><h2>The Multi-Step Craft Process</h2><ol><li><strong>The Clay Core:</strong> The artisan first sculpts a rough clay model of the deity, providing the inner foundation.</li><li><strong>Wax Layering:</strong> The clay model is covered in a layer of pure beeswax mixed with natural resins. The artisan painstakingly carves every fine facial expression, jewelry pattern, and drapery folds directly onto this wax layer.</li><li><strong>The Outer Shell:</strong> The detailed wax model is coated with successive layers of fine clay mixed with river silt, forming a solid, hardened outer mold.</li><li><strong>Draining the Wax:</strong> The clay mold is heated in a furnace, causing the inner wax model to melt and drain out through a small channel, leaving a hollow cavity that perfectly preserves the negative impression of the wax design.</li><li><strong>Pouring Molten Brass:</strong> Liquid brass, melted at temperatures exceeding 1000°C, is poured carefully into the empty clay mold cavity.</li><li><strong>Chiseling and Buffing:</strong> Once the metal cools, the clay casing is broken open to reveal the raw brass sculpture. The piece then undergoes detailed hand chiseling, sand buffing, and intricate polishing to create the exquisite antiqued accents.</li></ol><blockquote>"Each lost-wax mold is shattered to release the brass sculpture inside, meaning every single finished idol is an unrepeatable masterpiece of human hands."</blockquote>',
            'featured_image' => null,
            'featured_image_alt' => null,
            'blog_author_id' => $author->id,
            'blog_category_id' => $cat2->id,
            'status' => 'published',
            'published_at' => now()->subDays(6),
            'meta_title' => 'Lost Wax Casting Technique: Crafting Traditional Brass Sculptures',
            'meta_description' => 'Delve into the step-by-step history of India\'s ancient lost-wax casting technique. Discover how traditional Moradabad artisans shape brass sculptures.',
            'primary_keyword' => 'lost wax casting',
            'secondary_keywords' => 'madhuchista vidhana, brass casting, metal sculpture history, indian artisans',
            'reading_time' => 5,
            'schema_type' => 'Article',
            'faq_json' => [],
            'related_products_json' => [],
            'created_by' => 1,
            'updated_by' => 1,
            'last_updated_at' => now()
        ]);
        $post2->tags()->sync([$tag1->id, $tag3->id]);
        
        BlogRevision::create([
            'blog_post_id' => $post2->id,
            'title' => $post2->title,
            'excerpt' => $post2->excerpt,
            'content' => $post2->content,
            'faq_json' => $post2->faq_json,
            'updated_by' => 1
        ]);

        // Post 3: Spiritual Living
        $post3 = BlogPost::create([
            'title' => 'Akhand Diya: Sacred Symbolism & Caring for Festive Brass Lamps',
            'slug' => 'akhand-diya-sacred-symbolism-caring-for-festive-brass-lamps',
            'excerpt' => 'Learn the profound spiritual significance of lighting an Akhand Diya and explore practical cleaning steps to keep your brass lamps shining bright.',
            'content' => '<h2>Lighting the Divine Spark</h2><p>In Indian culture, light represents knowledge, spiritual wisdom, and the dispel of darkness. An Akhand Diya—a continuous brass oil lamp that burns for hours or days—is a prominent feature of major celebrations such as Diwali, Navratri, and auspicious housewarming ceremonies. Choosing a heavy, high-quality pure brass Akhand Diya ensures safety, longevity, and a beautifully radiating golden flame.</p><h2>The Symbolism of the Oil Lamp</h2><p>Every element of a traditional diya holds a symbolic spiritual significance:</p><ul><li><strong>The Brass Body:</strong> Represents our physical body—our earthly vessel.</li><li><strong>The Ghee or Sesame Oil:</strong> Represents our human attachments, ego, and desires. As the oil burns away, our ego is slowly dissolved.</li><li><strong>The Cotton Wick:</strong> Represents our inner spirit or soul.</li><li><strong>The Flame:</strong> Represents the rising path toward higher spiritual consciousness and pure wisdom.</li></ul><h2>Professional Brass Cleaning & Care Guide</h2><p>Because oil, soot, and heat build up quickly on brass diyas, regular care is essential. Use this quick guide to keep your festive brassware in pristine condition:</p><h2>1. Soak Out the Oil Resolute</h2><p>Before scrubbing, place the cooled diya in a basin of warm, soapy water for 5 minutes. This softens dried oil, sticky ghee, and soot residues instantly without abrasive scraping.</p><h2>2. Polish with Tamarind and Salt</h2><p>Mix natural tamarind paste with a teaspoon of table salt. Rub it thoroughly over the brass lamp body. The organic tartaric acid reacts naturally to dissolve black soot stains and dull copper oxidation, restoring the bright, warm gold brass shimmer.</p><h2>3. Buff with a Dry Microfiber Cloth</h2><p>After rinsing thoroughly under fresh water, dry the diya immediately. Buffing with a dry microfiber cloth prevents water spots and creates a beautiful, mirror-like protective shine.</p>',
            'featured_image' => null,
            'featured_image_alt' => null,
            'blog_author_id' => $author->id,
            'blog_category_id' => $cat3->id,
            'status' => 'published',
            'published_at' => now()->subDays(12),
            'meta_title' => 'Akhand Diya: Significance & Practical Brass Cleaning Tips',
            'meta_description' => 'Uncover the sacred spiritual meaning of the Akhand Diya. Follow our simple, organic step-by-step cleaning guides to keep brass lamps shiny and clean.',
            'primary_keyword' => 'akhand diya care',
            'secondary_keywords' => 'brass diya cleaning, spiritual meaning, oil lamp ritual, indian festivals',
            'reading_time' => 3,
            'schema_type' => 'BlogPosting',
            'faq_json' => [
                ['question' => 'What is the best oil for a brass diya?', 'answer' => 'Sesame oil (til oil) and mustard oil are considered highly auspicious and burn cleanly. Pure cow ghee is also excellent for high-priority rituals and leaves a sweet aroma.'],
                ['question' => 'How can I prevent soot buildup on brass diyas?', 'answer' => 'Trim the cotton wick regularly to prevent a split wick, and ensure that the lamp is placed away from heavy direct drafts to maintain a steady, soot-free flame.']
            ],
            'related_products_json' => $productIds,
            'created_by' => 1,
            'updated_by' => 1,
            'last_updated_at' => now()
        ]);
        $post3->tags()->sync([$tag1->id, $tag4->id, $tag5->id]);
        
        BlogRevision::create([
            'blog_post_id' => $post3->id,
            'title' => $post3->title,
            'excerpt' => $post3->excerpt,
            'content' => $post3->content,
            'faq_json' => $post3->faq_json,
            'updated_by' => 1
        ]);
    }
}
