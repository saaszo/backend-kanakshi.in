<?php
/**
 * Shipping Policy
 */
require_once __DIR__ . '/config/config.php';

$dynamicPage = getDynamicPageBySlug('shipping-policy', true);

$pageTitle = $dynamicPage['meta_title'] ?: 'Shipping Policy';
$metaDesc = $dynamicPage['meta_desc'] ?: 'Learn about dispatch times, delivery timelines, and shipping support.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5" style="background:#fcfaf5;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="text-center mb-5">
                    <span class="section-tag reveal">Shipping</span>
                    <h1 class="reveal" style="font-style:normal;">Clear Delivery Timelines, Premium Communication</h1>
                    <p class="text-secondary reveal">We keep shipping information simple so customers know what to expect after they order.</p>
                </div>
                <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5 reveal">
                    <h2 class="fs-3" style="font-style:normal;">Dispatch Window</h2>
                    <p class="text-secondary">Most ready-to-ship orders are packed within 24 to 48 business hours. Peak sale days, personalization, or remote delivery zones can extend processing slightly.</p>

                    <h2 class="fs-3 mt-4" style="font-style:normal;">Delivery Estimates</h2>
                    <p class="text-secondary">Metro orders usually arrive faster, while regional and remote areas may take longer. Once a tracking ID is generated, you can follow shipment progress from the tracking page.</p>

                    <h2 class="fs-3 mt-4" style="font-style:normal;">Support During Transit</h2>
                    <p class="text-secondary">If a parcel is delayed, damaged, or marked delivered incorrectly, contact support with your order number and we will investigate the next best step.</p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="<?= url('track-order.php') ?>" class="btn-lux-primary">Track My Order</a>
                        <a href="<?= url('contact-us.php') ?>" class="btn-lux-outline">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($dynamicPage && !empty($dynamicPage['content'])): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 reveal">
                <div class="bg-light rounded-4 border p-4 p-md-5 designer-typography">
                    <?= $dynamicPage['content'] ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.designer-typography {
    color: var(--text-secondary);
    line-height: 1.85;
}
.designer-typography h2,
.designer-typography h3,
.designer-typography h4 {
    color: var(--text-primary);
    font-style: normal;
    margin-bottom: 1rem;
    margin-top: 1.8rem;
}
.designer-typography p,
.designer-typography ul {
    margin-bottom: 1rem;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
