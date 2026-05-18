<?php
/**
 * Dynamic Page Template — Luxury Design System
 * ------------------------------------------------
 * This template fetches content from the 'pages' table 
 * and renders it with premium boutique typography.
 */
require_once __DIR__ . '/../../config/config.php';

$slug = isset($_pageSlug) ? $_pageSlug : '';
$page = getDynamicPageBySlug($slug, true);

if (!$page) {
    // Falls back to a default 404 or index if not found
    header("Location: " . url('index.php'));
    exit;
}

// Meta & Title setup
$pageTitle = $page['meta_title'] ?: $page['title'];
$metaDesc  = $page['meta_desc'];

require_once ROOT_PATH . '/includes/header.php';
?>

<!-- ══ DESIGNER PAGE HEADER ═══════════════════════════════════ -->
<section class="page-hero-luxury py-5 mb-5" style="background-color: var(--bg-main); border-bottom: 1px solid var(--border-gold);">
    <div class="container py-4 text-center">
        <h1 class="display-3 fw-300 font-heading mb-3 italic-serif animate-up">
            <?= e($page['title']) ?>
        </h1>
        <div class="ls-3 text-uppercase small fw-700 text-muted animate-up-delayed">
            Last Updated: <?= date('F d, Y', strtotime($page['updated_at'])) ?>
        </div>
    </div>
</section>

<!-- ══ CONTENT ARCHIVE ═══════════════════════════════════════ -->
<section class="content-archive py-5 mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="luxury-document-wrapper bg-white p-5 p-md-5 rounded-4 shadow-sm border animate-fade-in">
                    <!-- Dynamic Content -->
                    <div class="document-content designer-typography">
                        <?= $page['content'] ?>
                    </div>

                    <div class="mt-5 pt-5 border-top text-center opacity-50">
                        <img src="<?= url(getSetting('site_logo', 'uploads/logo_default.svg')) ?>" alt="Logo" class="mb-3" style="max-height: 25px; filter: grayscale(1);">
                        <p class="small text-uppercase ls-2">Timeless Elegance &bull; Divine Craftsmanship</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ── Luxury Document Typography ── */
.designer-typography {
    line-height: 1.8;
    color: var(--text-primary);
    font-size: 1.05rem;
}

.designer-typography h2, 
.designer-typography h3, 
.designer-typography h4 {
    font-family: 'Playfair Display', serif;
    color: var(--maroon);
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    font-weight: 500;
}

.designer-typography h2 { border-bottom: 1px solid var(--border-subtle); padding-bottom: 10px; font-size: 2rem; }

.designer-typography p {
    margin-bottom: 1.5rem;
}

.designer-typography ul {
    padding-left: 1.5rem;
    margin-bottom: 2rem;
}

.designer-typography li {
    margin-bottom: 0.75rem;
    position: relative;
}

.designer-typography li::marker {
    color: var(--gold);
}

.italic-serif {
    font-style: italic;
    letter-spacing: -0.01em;
}

.luxury-document-wrapper {
    position: relative;
    overflow: hidden;
}

.luxury-document-wrapper::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon));
}

.animate-up { animation: up 1s ease forwards; }
.animate-up-delayed { animation: up 1s ease 0.3s forwards; opacity: 0; }
.animate-fade-in { animation: fadeIn 1.5s ease forwards; }

@keyframes up {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
