<?php

namespace App\Services;

use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogRevision;
use App\Models\BlogTag;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LittleDivinityEditorialBlogPublisher
{
    /**
     * @return array{created:int,updated:int,skipped:int,slugs:array<int,string>}
     */
    public function publish(bool $refresh = true): array
    {
        $adminId = User::query()->orderBy('id')->value('id');
        $author = $this->resolveAuthor();
        $categories = $this->resolveCategories();
        $products = $this->resolveProducts();

        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'slugs' => [],
        ];

        foreach ($this->posts($categories, $products) as $entry) {
            $result['slugs'][] = $entry['slug'];

            $post = BlogPost::query()->where('slug', $entry['slug'])->first();
            if ($post && !$refresh) {
                $result['skipped']++;
                continue;
            }

            $payload = [
                'title' => $entry['title'],
                'slug' => $entry['slug'],
                'excerpt' => $entry['excerpt'],
                'content' => $entry['content'],
                'featured_image' => $entry['featured_image'],
                'featured_image_alt' => $entry['featured_image_alt'],
                'blog_author_id' => $author->id,
                'blog_category_id' => $entry['category_id'],
                'status' => 'published',
                'published_at' => $entry['published_at'],
                'meta_title' => $entry['meta_title'],
                'meta_description' => $entry['meta_description'],
                'canonical_url' => null,
                'og_title' => $entry['og_title'],
                'og_description' => $entry['og_description'],
                'og_image' => $entry['featured_image'],
                'twitter_title' => $entry['twitter_title'],
                'twitter_description' => $entry['twitter_description'],
                'twitter_image' => $entry['featured_image'],
                'primary_keyword' => $entry['primary_keyword'],
                'secondary_keywords' => $entry['secondary_keywords'],
                'reading_time' => $this->calculateReadingTime($entry['content']),
                'seo_noindex' => false,
                'seo_nofollow' => false,
                'schema_type' => 'BlogPosting',
                'faq_json' => $entry['faq_json'],
                'related_products_json' => $entry['related_products_json'],
                'created_by' => $post?->created_by ?: $adminId,
                'updated_by' => $adminId,
                'last_updated_at' => now(),
            ];

            $wasChanged = !$post
                || $post->title !== $payload['title']
                || $post->excerpt !== $payload['excerpt']
                || $post->content !== $payload['content']
                || ($post->faq_json ?? []) !== $payload['faq_json']
                || $post->featured_image !== $payload['featured_image']
                || $post->meta_title !== $payload['meta_title']
                || $post->meta_description !== $payload['meta_description'];

            if ($post) {
                $post->update($payload);
                $result['updated']++;
            } else {
                $post = BlogPost::query()->create($payload);
                $result['created']++;
                $wasChanged = true;
            }

            $tagIds = collect($entry['tags'])
                ->map(fn (string $name) => BlogTag::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                )->id)
                ->values()
                ->all();

            $post->tags()->sync($tagIds);

            if ($wasChanged) {
                BlogRevision::query()->create([
                    'blog_post_id' => $post->id,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'content' => $post->content,
                    'faq_json' => $post->faq_json ?? [],
                    'updated_by' => $adminId,
                ]);
            }
        }

        return $result;
    }

    private function resolveAuthor(): BlogAuthor
    {
        return BlogAuthor::query()->firstOrCreate(
            ['slug' => 'little-divinity-editorial'],
            [
                'name' => 'Little Divinity Editorial',
                'bio' => 'Little Divinity Editorial curates stories on brass living, ritual-led homes, handcrafted gifting, and the enduring wisdom of Indian metal traditions.',
                'avatar_alt' => 'Little Divinity Editorial Team',
                'twitter_handle' => null,
            ]
        );
    }

    /**
     * @return array<string,int>
     */
    private function resolveCategories(): array
    {
        $map = [];

        $definitions = [
            'brass-living' => [
                'name' => 'Brass Living',
                'description' => 'Stories about timeless brass traditions, heritage-led interiors, and meaningful living with handcrafted metal décor.',
                'meta_title' => 'Brass Living Guides | Little Divinity Blog',
                'meta_description' => 'Explore brassware traditions, home styling ideas, and cultural insights that bring warmth, wellness, and heritage into everyday living.',
            ],
            'kitchen-rituals' => [
                'name' => 'Kitchen Rituals',
                'description' => 'Practical and cultural guides for using brass and kansa in modern kitchens, gifting, and daily table rituals.',
                'meta_title' => 'Kitchen Rituals & Brassware Guides | Little Divinity',
                'meta_description' => 'Discover how brass, kansa, and slow rituals can reshape the modern Indian kitchen with craft, wellness, and beauty.',
            ],
            'copper-wellness' => [
                'name' => 'Copper Wellness',
                'description' => 'Guides focused on tamra jal, copper hydration habits, and age-old wellness rituals adapted for contemporary homes.',
                'meta_title' => 'Copper Wellness & Tamra Jal Guides | Little Divinity',
                'meta_description' => 'Learn how to adopt copper water rituals, choose the right vessels, and care for copper wellness essentials at home.',
            ],
            'care-and-maintenance' => [
                'name' => 'Care & Maintenance',
                'description' => 'Cleaning, polishing, and care instructions for brass, kansa, and handcrafted idols so they age beautifully for years.',
                'meta_title' => 'Brass & Kansa Care Instructions | Little Divinity',
                'meta_description' => 'Simple, practical care instructions for brass utensils, kansa serveware, antique-finish idols, and raw brass décor.',
            ],
        ];

        foreach ($definitions as $slug => $definition) {
            $category = BlogCategory::query()->firstOrCreate(
                ['slug' => $slug],
                $definition
            );

            $map[$slug] = $category->id;
        }

        return $map;
    }

    /**
     * @return array<string,array{id:int,image:?string}>
     */
    private function resolveProducts(): array
    {
        $slugs = [
            'heritage-brass-decor-accent-1',
            'heritage-brass-decor-accent-2',
            'little-divinity-brass-idol-set',
            'brass-bell-pooja-decor',
            'brass-table-decor-accent',
            'antique-brass-home-decor-set',
        ];

        $products = Product::query()
            ->whereIn('slug', $slugs)
            ->get(['id', 'slug', 'images']);

        return $products
            ->mapWithKeys(function (Product $product) {
                $images = is_array($product->images) ? $product->images : [];

                return [
                    $product->slug => [
                        'id' => $product->id,
                        'image' => $images[0] ?? null,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @param array<string,int> $categories
     * @param array<string,array{id:int,image:?string}> $products
     * @return array<int,array<string,mixed>>
     */
    private function posts(array $categories, array $products): array
    {
        $publishedBase = Carbon::parse('2026-05-19 09:30:00');

        return [
            [
                'title' => 'Discover Why Brass: The Timeless Elegance and Health Benefits of Brassware',
                'slug' => 'discover-why-brass-the-timeless-elegance-and-health-benefits-of-brassware',
                'category_id' => $categories['brass-living'],
                'published_at' => Carbon::parse('2024-10-14 10:00:00'),
                'featured_image' => $products['heritage-brass-decor-accent-1']['image'] ?? '/storage/products/little-divinity-krishna.jpg',
                'featured_image_alt' => 'Handcrafted brass decor accent representing the timeless beauty of brassware',
                'tags' => ['Brassware', 'Indian Heritage', 'Home Decor', 'Spiritual Living', 'Brass Utensils'],
                'primary_keyword' => 'benefits of brassware',
                'secondary_keywords' => 'timeless brass decor, brass utensils, indian brass traditions, brass home decor',
                'related_products_json' => $this->productIds($products, [
                    'heritage-brass-decor-accent-1',
                    'heritage-brass-decor-accent-2',
                    'little-divinity-brass-idol-set',
                ]),
                'faq_json' => [
                    [
                        'question' => 'Why is brass considered special in Indian homes?',
                        'answer' => 'Brass is valued for its warm appearance, durability, ritual significance, and long-standing place in Indian kitchens, temples, and gifting traditions.',
                    ],
                    [
                        'question' => 'Can brassware be used every day?',
                        'answer' => 'Yes. With proper care and use, brass serveware, décor accents, and ritual objects can fit beautifully into daily life as well as festive occasions.',
                    ],
                ],
                'excerpt' => 'Brassware brings together visual warmth, ritual value, long life, and traditional wellness habits, making it one of the most enduring materials in the Indian home.',
                'meta_title' => 'Benefits of Brassware: Why Brass Still Belongs in the Modern Indian Home',
                'meta_description' => 'Discover why brassware remains timeless in Indian homes through its elegance, ritual value, durability, and everyday usefulness in décor and dining.',
                'og_title' => 'Benefits of Brassware: Why Brass Still Belongs in the Modern Indian Home',
                'og_description' => 'Explore how brassware combines timeless beauty, cultural depth, and practical everyday value in a modern Indian home.',
                'twitter_title' => 'Benefits of Brassware: Why Brass Still Belongs in the Modern Indian Home',
                'twitter_description' => 'Explore how brassware combines timeless beauty, cultural depth, and practical everyday value in a modern Indian home.',
                'content' => $this->discoverWhyBrassContent(),
            ],
            [
                'title' => 'From Mohenjo-Daro to Your Kitchen: Why Brass Still Belongs in the Indian Home',
                'slug' => 'from-mohenjo-daro-to-your-kitchen-why-brass-still-belongs-in-the-indian-home',
                'category_id' => $categories['brass-living'],
                'published_at' => $publishedBase->copy(),
                'featured_image' => $products['little-divinity-brass-idol-set']['image'] ?? '/storage/products/little-divinity-candle-stand.png',
                'featured_image_alt' => 'Curated brass decor composition connecting ancient Indian heritage to the modern home',
                'tags' => ['Mohenjo-Daro', 'Brass Living', 'Indian Craft', 'Vastu', 'Home Styling'],
                'primary_keyword' => 'why brass in indian homes',
                'secondary_keywords' => 'mohenjo-daro brass, anti microbial brass, vastu aligned decor, ancestral kitchen wisdom',
                'related_products_json' => $this->productIds($products, [
                    'little-divinity-brass-idol-set',
                    'antique-brass-home-decor-set',
                    'heritage-brass-decor-accent-2',
                ]),
                'faq_json' => [
                    [
                        'question' => 'Why do people connect brass with Indian heritage?',
                        'answer' => 'Brass has been part of Indian worship, cooking, gifting, and architecture for generations, so it naturally carries a strong sense of continuity and memory.',
                    ],
                    [
                        'question' => 'Does brass fit into a modern apartment?',
                        'answer' => 'Absolutely. A few well-chosen brass accents can add warmth and story to a minimal or contemporary home without making it feel heavy or old-fashioned.',
                    ],
                ],
                'excerpt' => 'From ancient civilisation to today’s dining shelf, brass continues to feel relevant because it blends cultural memory, utility, and visual warmth in one material.',
                'meta_title' => 'From Mohenjo-Daro to Your Kitchen: Why Brass Still Belongs in Indian Homes',
                'meta_description' => 'Explore why brass still matters in the Indian home, from heritage and vastu-led styling to everyday utility and modern decorative warmth.',
                'og_title' => 'From Mohenjo-Daro to Your Kitchen: Why Brass Still Belongs in Indian Homes',
                'og_description' => 'A story-driven look at why brass continues to feel relevant in Indian homes across décor, rituals, and everyday living.',
                'twitter_title' => 'From Mohenjo-Daro to Your Kitchen: Why Brass Still Belongs in Indian Homes',
                'twitter_description' => 'A story-driven look at why brass continues to feel relevant in Indian homes across décor, rituals, and everyday living.',
                'content' => $this->mohenjoDaroContent(),
            ],
            [
                'title' => 'The Ayurvedic Kitchen, Reimagined: Everyday Brass and Kansa Rituals',
                'slug' => 'the-ayurvedic-kitchen-reimagined-everyday-brass-and-kansa-rituals',
                'category_id' => $categories['kitchen-rituals'],
                'published_at' => $publishedBase->copy()->addDay(),
                'featured_image' => $products['brass-bell-pooja-decor']['image'] ?? '/demo-products/little-divinity-real-1.jpg',
                'featured_image_alt' => 'Traditional brass and kansa vessels styled for an Ayurvedic kitchen ritual',
                'tags' => ['Ayurvedic Kitchen', 'Kansa', 'Brass Utensils', 'Slow Living', 'Kitchen Rituals'],
                'primary_keyword' => 'ayurvedic kitchen with brass and kansa',
                'secondary_keywords' => 'kansa thali benefits, brass cookware ritual, slow cooked indian kitchen, traditional serveware',
                'related_products_json' => $this->productIds($products, [
                    'brass-bell-pooja-decor',
                    'brass-table-decor-accent',
                    'heritage-brass-decor-accent-1',
                ]),
                'faq_json' => [
                    [
                        'question' => 'What is kansa used for in a home?',
                        'answer' => 'Kansa is often used for serving and dining rituals because many families appreciate its tactile feel, balanced weight, and connection to traditional food culture.',
                    ],
                    [
                        'question' => 'Do I need to replace my entire kitchen with brassware?',
                        'answer' => 'No. Many people begin with one or two meaningful pieces, like a serving bowl, water vessel, or festive thali, and build their ritual slowly over time.',
                    ],
                ],
                'excerpt' => 'A modern Ayurvedic kitchen does not need to feel old-world. Brass and kansa can quietly reintroduce slower, more intentional dining rituals into contemporary homes.',
                'meta_title' => 'The Ayurvedic Kitchen, Reimagined with Brass and Kansa | Little Divinity',
                'meta_description' => 'Learn how brass and kansa can bring slower rituals, heritage-led dining, and thoughtful serving traditions back into the modern kitchen.',
                'og_title' => 'The Ayurvedic Kitchen, Reimagined with Brass and Kansa',
                'og_description' => 'A practical guide to bringing brass and kansa into a more intentional, tradition-rooted kitchen routine.',
                'twitter_title' => 'The Ayurvedic Kitchen, Reimagined with Brass and Kansa',
                'twitter_description' => 'A practical guide to bringing brass and kansa into a more intentional, tradition-rooted kitchen routine.',
                'content' => $this->ayurvedicKitchenContent(),
            ],
            [
                'title' => 'Copper Wellness for the Modern Home: A Practical Guide to Tamra Jal',
                'slug' => 'copper-wellness-for-the-modern-home-a-practical-guide-to-tamra-jal',
                'category_id' => $categories['copper-wellness'],
                'published_at' => $publishedBase->copy()->addDays(2),
                'featured_image' => $products['heritage-brass-decor-accent-2']['image'] ?? '/storage/products/little-divinity-portrait.png',
                'featured_image_alt' => 'Copper wellness vessel styled for a tamra jal ritual in a modern bedroom corner',
                'tags' => ['Tamra Jal', 'Copper Wellness', 'Morning Rituals', 'Ayurveda', 'Hydration'],
                'primary_keyword' => 'tamra jal guide',
                'secondary_keywords' => 'copper water benefits, bedside copper bottle, ayurvedic hydration ritual, copper vessel care',
                'related_products_json' => $this->productIds($products, [
                    'heritage-brass-decor-accent-2',
                    'antique-brass-home-decor-set',
                ]),
                'faq_json' => [
                    [
                        'question' => 'What is tamra jal?',
                        'answer' => 'Tamra jal refers to water stored in a copper vessel for a period of time, a practice many Indian households associate with mindful hydration rituals.',
                    ],
                    [
                        'question' => 'How long should water sit in a copper vessel?',
                        'answer' => 'A common household practice is to keep water in a copper vessel overnight so it is ready for the morning routine.',
                    ],
                ],
                'excerpt' => 'Copper water rituals remain popular because they are simple, soothing, and easy to fold into modern mornings without feeling performative or complicated.',
                'meta_title' => 'Tamra Jal Guide: Copper Wellness Rituals for the Modern Home',
                'meta_description' => 'A practical tamra jal guide covering copper water rituals, vessel selection, timing, and simple care for modern homes.',
                'og_title' => 'Tamra Jal Guide: Copper Wellness Rituals for the Modern Home',
                'og_description' => 'A simple and elegant guide to making copper water part of a steady, everyday wellness ritual.',
                'twitter_title' => 'Tamra Jal Guide: Copper Wellness Rituals for the Modern Home',
                'twitter_description' => 'A simple and elegant guide to making copper water part of a steady, everyday wellness ritual.',
                'content' => $this->tamraJalContent(),
            ],
            [
                'title' => 'Caring Instructions for Little Divinity Brass, Kansa, and Handcrafted Idols',
                'slug' => 'caring-instructions-for-little-divinity-brass-kansa-and-handcrafted-idols',
                'category_id' => $categories['care-and-maintenance'],
                'published_at' => $publishedBase->copy()->addDays(3),
                'featured_image' => $products['brass-table-decor-accent']['image'] ?? '/storage/products/little-divinity-candle-stand.png',
                'featured_image_alt' => 'Handcrafted brass and kansa items arranged for a care and maintenance guide',
                'tags' => ['Brass Care', 'Kansa Care', 'Idol Cleaning', 'Product Maintenance', 'Handcrafted Decor'],
                'primary_keyword' => 'how to care for brass and kansa',
                'secondary_keywords' => 'pitambari cleaning, lemon baking soda brass, antique finish idol care, raw brass maintenance',
                'related_products_json' => $this->productIds($products, [
                    'brass-table-decor-accent',
                    'little-divinity-brass-idol-set',
                    'brass-bell-pooja-decor',
                ]),
                'faq_json' => [
                    [
                        'question' => 'Can I wash brass utensils daily with dish soap?',
                        'answer' => 'Yes. Mild dish soap, a soft sponge, and immediate drying are usually enough for daily care before you do a deeper weekly clean.',
                    ],
                    [
                        'question' => 'Should antique-finish idols be scrubbed hard?',
                        'answer' => 'No. Antique-finish idols should be wiped gently with a soft cloth, because harsh scrubbing can disturb the intentional surface finish.',
                    ],
                ],
                'excerpt' => 'A little care goes a long way. With the right cleaning habits, your Little Divinity brass, kansa, and idol pieces can stay beautiful for years.',
                'meta_title' => 'How to Care for Brass, Kansa, and Handcrafted Idols | Little Divinity',
                'meta_description' => 'Follow simple daily and weekly care routines for brass utensils, kansa serveware, antique-finish idols, and raw brass pieces.',
                'og_title' => 'How to Care for Brass, Kansa, and Handcrafted Idols',
                'og_description' => 'Simple care steps to keep your Little Divinity brass, kansa, and handcrafted idols looking beautiful over time.',
                'twitter_title' => 'How to Care for Brass, Kansa, and Handcrafted Idols',
                'twitter_description' => 'Simple care steps to keep your Little Divinity brass, kansa, and handcrafted idols looking beautiful over time.',
                'content' => $this->careInstructionsContent(),
            ],
        ];
    }

    /**
     * @param array<string,array{id:int,image:?string}> $products
     * @param array<int,string> $slugs
     * @return array<int,int>
     */
    private function productIds(array $products, array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug) => $products[$slug]['id'] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function calculateReadingTime(string $content): int
    {
        $words = str_word_count(strip_tags($content));

        return max(1, (int) ceil($words / 180));
    }

    private function discoverWhyBrassContent(): string
    {
        return trim(<<<'HTML'
<p>In a world where trends come and go, some materials never lose their place. Brass is one of them. For generations, Indian homes have reached for brass in temples, dining rituals, gifting traditions, and everyday objects that deserve to last. At Little Divinity, we see brass not just as a beautiful metal, but as a bridge between heritage, home, and daily intention.</p>
<h2>Aesthetic Appeal: Brass Adds Timeless Beauty</h2>
<p>Brass carries a warm golden hue that instantly softens a room and gives it depth. Whether it appears as a deity idol in a pooja corner, a statement décor accent on a console, or serveware placed on a festive table, brass makes a space feel considered and grounded. It reflects light gently, creates warmth without loudness, and brings an heirloom character that many modern materials struggle to imitate.</p>
<p>At Little Divinity, our brass collections are crafted to feel both rooted and versatile. A piece can hold cultural memory, while still blending beautifully into a contemporary apartment or a carefully styled family home.</p>
<h2>Health and Everyday Utility: Why Families Still Return to Brass Utensils</h2>
<p>For many Indian families, brass has long been associated with everyday usefulness as much as beauty. Brass is an alloy of copper and zinc, and traditional households have often valued it in kitchens and dining spaces because it feels substantial, durable, and culturally familiar. Today, many people are rediscovering brass and kansa for slower meal rituals, intentional serving, and a more grounded sensory experience at the table.</p>
<ul>
<li><strong>Traditional kitchen value:</strong> Brass and kansa pieces are often appreciated in Indian homes for their connection to ancestral food practices.</li>
<li><strong>Ritual-led dining:</strong> A brass thali, bowl, or water vessel can make everyday meals feel more deliberate and festive.</li>
<li><strong>Material presence:</strong> Unlike disposable tableware, brass adds weight, texture, and longevity to a meal setting.</li>
</ul>
<h2>Durability and Sustainability: Built to Last</h2>
<p>One of brassware’s strongest advantages is that it lasts. A well-cared-for brass piece can serve a family for decades, and in many homes such objects become part of generational memory. Instead of replacing things every few years, brass invites a more sustainable relationship with the objects we buy.</p>
<p>Brass is also recyclable, which adds to its long-term value. It is one of those materials that feels worth bringing home because it can age beautifully, hold meaning, and remain useful over time.</p>
<h2>Spiritual and Cultural Significance: A Material That Connects You to Tradition</h2>
<p>Brass plays a quiet but significant role in Indian spiritual life. It is seen in temples, aartis, oil lamps, puja thalis, and handcrafted idols that become central to a family’s daily rhythm. Bringing brass into the home is often less about display and more about atmosphere: it helps create spaces that feel peaceful, anchored, and reverent.</p>
<p>Whether it is a brass diya for evening prayers or a deity idol for a dedicated altar, the material itself carries a sense of ritual continuity that many families continue to cherish.</p>
<h2>Easy to Maintain, Easy to Love</h2>
<p>Brass rewards consistency, not complexity. Most pieces can be kept beautiful with gentle cleaning, proper drying, and occasional polishing. A small amount of regular care preserves shine and lets the object age gracefully. At Little Divinity, we believe good care is part of the ownership experience, not a burden.</p>
<h2>Versatility: Brass for Daily Life and Special Occasions</h2>
<p>Few materials move as easily between daily use and celebration. Brass fits a bedside water setup, a kitchen shelf, a wedding gift, a festive pooja arrangement, or a living-room styling story with equal ease. That is why it continues to feel relevant: it is practical enough for daily life and meaningful enough for special moments.</p>
<h2>The Timeless Allure of Brass</h2>
<p>From its visual elegance to its deep cultural roots, brass remains one of the most emotionally resonant materials you can bring into a home. It connects the present to something older, slower, and more intentional. If you want your spaces to feel warm, storied, and enduring, brass is not a trend to borrow from. It is a tradition worth living with.</p>
<p><strong>Ready to bring brass home?</strong> Explore the Little Divinity collections and discover pieces designed to add beauty, ritual, and meaning to everyday living.</p>
HTML);
    }

    private function mohenjoDaroContent(): string
    {
        return trim(<<<'HTML'
<p>Indian homes have never treated brass as a passing decorative choice. Long before modern wellness language and design trends arrived, brass already had a place in kitchens, temples, and gifting rituals. From the memory of ancient civilisations to the rhythm of a present-day home, brass still feels relevant because it brings together utility, symbolism, and beauty in one material.</p>
<h2>Why the Story of Brass Still Matters</h2>
<p>When we speak about brass in Indian living, we are really speaking about continuity. The same instinct that once placed handcrafted metalware at the centre of community life still shows up today in pooja décor, festive serveware, and statement pieces that make a space feel rooted. Brass is not simply old; it is enduring.</p>
<blockquote>Modern science may now explain anti-microbial surfaces and material longevity, but Indian households built trust in brass long before those terms entered design language.</blockquote>
<h2>What Our Ancestors Understood Instinctively</h2>
<p>Traditional homes valued objects that were practical, repairable, and worthy of keeping. Brass met all three needs. It could be used, polished, stored, passed down, and brought out again for the next generation. The material held up beautifully in both ritual and routine, and that gave it emotional as well as practical importance.</p>
<ul>
<li><strong>It felt sacred:</strong> Brass was naturally at home in lamps, bells, and idols.</li>
<li><strong>It felt useful:</strong> It worked across storage, serving, and décor-led utility.</li>
<li><strong>It felt lasting:</strong> Families trusted it to stay with them for years, not seasons.</li>
</ul>
<h2>Why Brass Still Works in Modern Homes</h2>
<p>Today’s homes may be smaller, lighter, and more minimal, but that does not make brass less relevant. In fact, it often makes brass more valuable. A single warm metal accent can do the work of many decorative objects. It adds texture to plain walls, depth to neutral corners, and character to homes that might otherwise feel visually flat.</p>
<p>Brass also fits beautifully into vastu-aware styling. Many homeowners prefer materials that feel spiritually aligned, elemental, and calming. Brass, with its warmth and ritual familiarity, naturally supports that atmosphere.</p>
<h2>How to Bring This Heritage Home Thoughtfully</h2>
<p>You do not need to recreate an old-world setup to live with brass meaningfully. Start with pieces that solve a real styling or ritual need:</p>
<ol>
<li>A handcrafted idol or diya for a sacred corner.</li>
<li>A statement décor accent for a console or sideboard.</li>
<li>A brass utility object that can be used during hosting or festivals.</li>
</ol>
<p>These choices make brass feel lived-in rather than ornamental. They connect your home to an older design intelligence without making it feel themed or over-styled.</p>
<h2>Brass as a Living Tradition</h2>
<p>To bring brass into your home is to bring home a language of care, continuity, and cultural memory. It reminds us that the most beautiful materials are often the ones that do not need to prove themselves every few years. They have already lasted.</p>
HTML);
    }

    private function ayurvedicKitchenContent(): string
    {
        return trim(<<<'HTML'
<p>The modern kitchen is full of convenience, but many people are looking for something it often lacks: rhythm. That is why traditional brass and kansa pieces are finding their way back into homes. They slow us down just enough to make meals feel intentional again, without demanding a total lifestyle overhaul.</p>
<h2>The Ayurvedic Kitchen, Reimagined</h2>
<p>When people talk about an Ayurvedic kitchen, they often imagine something rigid or deeply old-fashioned. In reality, the spirit of it is simple: cook with care, serve with attention, and choose vessels that make the act of eating feel complete. Brass and kansa fit naturally into this framework because they bring material depth, ritual value, and a certain old-world grace to everyday dining.</p>
<h2>Why Brass and Kansa Still Feel Relevant</h2>
<p>Many Indian families continue to value brass and kansa not only for how they look, but for how they change the feel of a meal. The weight of a bowl, the finish of a serving spoon, or the quiet shine of a thali can shift a table from rushed to considered.</p>
<ul>
<li><strong>They support slower serving rituals:</strong> plating, hosting, and sharing feel more mindful.</li>
<li><strong>They add warmth to the table:</strong> especially in kitchens that rely on neutral woods, stone, or plain ceramics.</li>
<li><strong>They create continuity:</strong> a visible connection to grandmothers, family meals, and festive traditions.</li>
</ul>
<h2>How to Start Without Overcomplicating Your Kitchen</h2>
<p>You do not need to replace everything at once. A more practical approach is to begin with a few meaningful additions:</p>
<ol>
<li><strong>A kansa katori or serving bowl</strong> for everyday meals.</li>
<li><strong>A brass water vessel</strong> for the dining table or bedside ritual.</li>
<li><strong>A festive thali</strong> for hosting, celebrations, and ritual serving moments.</li>
</ol>
<p>This keeps the transition natural. Your kitchen stays modern, but it begins to carry more story and more sensory richness.</p>
<h2>Food Tastes Different When the Ritual Changes</h2>
<p>Part of the appeal of traditional serveware is not just what it does to the table visually, but what it does to pace. Slow-cooked rice, a spoon of ghee, a served dal, or a festive sweet presented in metalware feels different from eating directly out of a rushed setup. The vessel changes how the meal is received.</p>
<h2>A Kitchen That Feels Remembered</h2>
<p>Brass and kansa do not ask the kitchen to move backwards. They simply ask it to remember what made a meal feel whole. If your home is ready for more intentional dining, a few heritage pieces can quietly begin that shift.</p>
HTML);
    }

    private function tamraJalContent(): string
    {
        return trim(<<<'HTML'
<p>Some wellness rituals endure because they are easy to live with. Tamra jal, or water stored in a copper vessel, is one of those habits. It does not ask for a dramatic routine or a complicated setup. It simply asks for consistency, and many people appreciate that simplicity.</p>
<h2>Why Copper Water Rituals Still Resonate</h2>
<p>Copper vessels have been part of Indian households for centuries. In many homes, a copper lota, bottle, or matka near the bedside or in the dining area was less a design statement and more a quiet daily rhythm. That ritual continues to appeal today because it feels both ancient and practical.</p>
<blockquote>Wellness often becomes sustainable only when it is simple enough to repeat. Tamra jal remains relevant because it fits neatly into that idea.</blockquote>
<h2>Creating a Modern Tamra Jal Practice</h2>
<p>You do not need a large ritual corner or a traditional setup to begin. A well-crafted copper bottle, jug, or dispenser can fit easily into a modern bedroom, work desk, or kitchen shelf. The goal is not to perform tradition loudly, but to let it settle naturally into your day.</p>
<ul>
<li><strong>At bedside:</strong> keep a copper bottle or tumbler ready overnight.</li>
<li><strong>At the dining table:</strong> use a copper water vessel as a daily visual ritual.</li>
<li><strong>In a wellness corner:</strong> place it alongside calming objects that encourage slower mornings.</li>
</ul>
<h2>How Long Should Water Rest?</h2>
<p>A common household approach is to fill the vessel at night and drink the water the next morning. This keeps the practice simple and easy to remember. More important than perfection is continuity.</p>
<h2>How to Choose the Right Copper Vessel</h2>
<p>Think first about your routine. Someone who wants a travel-friendly solution may prefer a bottle. Someone who enjoys hosting may prefer a larger tabletop dispenser. A bedside ritual may call for a bottle-and-cup pairing. The right vessel is the one you will actually use.</p>
<h2>Simple Care Makes the Ritual Sustainable</h2>
<p>Copper needs occasional cleaning to retain its finish. A gentle homemade polish using lemon and baking soda can help restore brightness. Dry the vessel well after washing, and avoid neglecting it for long periods. A little maintenance makes the ritual feel satisfying rather than inconvenient.</p>
<h2>Bringing Ancient Calm into Modern Mornings</h2>
<p>Tamra jal is ultimately less about trend and more about rhythm. It is a way to begin the day with something grounded, tactile, and familiar. In a world that often pushes speed, a copper water ritual offers a softer start.</p>
HTML);
    }

    private function careInstructionsContent(): string
    {
        return trim(<<<'HTML'
<p>Little Divinity brass and kansa pieces are designed to be lived with. They are not objects that must sit untouched behind glass. With just a little care, they can stay beautiful through daily use, festive gatherings, and years of family memory. The goal is not perfection. The goal is consistency.</p>
<h2>1. Utensils: Brass and Kansa for Daily Use</h2>
<p>For everyday cleaning, wash utensils with mild dish soap and a soft sponge. Rinse well and dry immediately to avoid water marks. A weekly deeper clean helps restore shine and keeps the surface feeling fresh.</p>
<p><strong>Weekly deep-clean routine:</strong></p>
<ol>
<li>Wet the utensil lightly.</li>
<li>Apply a small amount of brass cleaning powder such as Pitambari.</li>
<li>Scrub gently in circular motions using a soft sponge.</li>
<li>Rinse thoroughly and dry with a soft cloth.</li>
</ol>
<p>For a natural polish, a lemon-and-baking-soda paste works beautifully. Use it gently, rinse thoroughly, and dry the piece well.</p>
<h2>2. Idols with Antique Finish</h2>
<p>Antique-finish idols should be cared for more gently than polished raw brass. Dust them with a soft dry cloth as part of your regular routine. For an occasional deeper clean, wipe gently with a damp cloth instead of scrubbing.</p>
<p>Once cleaned, let the idol dry fully. Some households also like to place the piece in soft sunlight for a short time before applying a tiny amount of coconut oil with a cloth to revive the finish and add warmth to the surface.</p>
<h2>3. Idols with Colored Finish</h2>
<p>Colored-finish pieces need the lightest touch. Use a damp cloth to wipe the surface and avoid any abrasive rubbing. After cleaning, allow the piece to dry naturally under a fan or in indirect sunlight. Direct soaking or aggressive washing can disturb the finish.</p>
<h2>4. Idols Without Polish: Raw Brass</h2>
<p>Raw brass responds beautifully to regular buffing. A dry cloth can bring back a quick glow, while occasional polishing with brass cleaners or a lemon-based paste can restore deeper brightness. Always dry these pieces immediately after washing to avoid spotting.</p>
<h2>Make Care a Ritual, Not a Chore</h2>
<p>The easiest way to maintain handcrafted metalware is to make care part of its rhythm. A quick wipe after use, a weekly polish, and gentle handling during storage are often enough. Good care protects the finish, but it also deepens the relationship you have with the object.</p>
<p>At Little Divinity, we believe the best décor and serveware should feel beautiful both on the day you bring it home and years later. A little maintenance ensures that your brass, kansa, and handcrafted idols continue to do exactly that.</p>
HTML);
    }
}
