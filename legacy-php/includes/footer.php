<?php
/**
 * Global Footer — included at bottom of every frontend page.
 */
?>

<?php 
$footerStyle = (int)getSetting('footer_style', 1);
$footerFile  = __DIR__ . "/footers/footer-v{$footerStyle}.php";
if (file_exists($footerFile)) {
    include $footerFile;
} else {
    include __DIR__ . "/footers/footer-v1.php";
}
?>

<!-- ────────────────────────────────────────────────────────
     WHATSAPP FLOATING WIDGET
──────────────────────────────────────────────────────── -->
<?php if ($wa = getSetting('whatsapp_number', '919876543210')): ?>
<a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $wa)) ?>?text=Hi! I need some help with my shopping." target="_blank" class="whatsapp-float d-flex align-items-center justify-content-center bg-success text-white shadow-lg text-decoration-none" aria-label="Chat on WhatsApp" style="position:fixed; bottom:20px; left:20px; width:60px; height:60px; border-radius:50px; z-index:100; font-size:32px; overflow:hidden;">
    <i class="fa-brands fa-whatsapp" style="z-index: 2; position: relative;"></i>
    <div class="wa-pulse"></div>
</a>
<style>
.whatsapp-float { transition: transform 0.3s ease; }
.whatsapp-float:hover { transform: scale(1.1); color: white; }
.wa-pulse {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.4);
    border-radius: 50%;
    animation: wa_pulse 2s infinite;
}
@keyframes wa_pulse {
    0% { transform: scale(0.9); opacity: 1; }
    100% { transform: scale(1.5); opacity: 0; }
}
</style>
<?php endif; ?>


<!-- ────────────────────────────────────────────────────────
     MOBILE BOTTOM NAVIGATION (Included only on mobile)
──────────────────────────────────────────────────────── -->
<?php include __DIR__ . '/mobile-nav.php'; ?>

<!-- ────────────────────────────────────────────────────────
     SCRIPTS
──────────────────────────────────────────────────────── -->
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<!-- Inline page-specific JS can be echo'd via $extraJs -->
<?php if (!empty($extraJs)) echo $extraJs; ?>

</body>
</html>
