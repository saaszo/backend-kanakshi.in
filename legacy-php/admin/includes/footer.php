<?php
/**
 * Admin Footer — Professional Enterprise
 */
?>
    </main> <!-- /.admin-content -->

    <footer class="py-3 px-6 border-top bg-white" style="padding: 12px 24px;">
        <div class="d-flex justify-content-between align-items-center">
            <small style="font-size:12px; color:var(--text-muted);">
                &copy; <?= date('Y') ?> <strong style="color:var(--text-secondary);"><?= e(getSetting('site_name')) ?></strong>
            </small>
            <small style="font-size:12px; color:var(--text-muted);">v1.0.0</small>
        </div>
    </footer>

</div> <!-- /.admin-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ── Sidebar toggle (mobile) ──────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('toggleSidebar');
    const closeBtn  = document.getElementById('closeSidebar');

    function openSidebar()  { sidebar?.classList.add('show');    overlay?.classList.add('show'); }
    function closeSidebar() { sidebar?.classList.remove('show'); overlay?.classList.remove('show'); }

    toggleBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click',  closeSidebar);
    overlay?.addEventListener('click',   closeSidebar);

    // ── Flash alert auto-dismiss ─────────────────────────────
    document.querySelectorAll('.flash-alert[data-dismiss-after]').forEach(el => {
        const delay = parseInt(el.dataset.dismissAfter, 10) || 4000;
        setTimeout(() => {
            el.animate([{ opacity: 1, transform: 'translateX(0)' }, { opacity: 0, transform: 'translateX(20px)' }], 300)
              .onfinish = () => {
                el.remove();
                const bar = document.getElementById('flashBar');
                if (bar && !bar.hasChildNodes()) bar.remove();
              };
        }, delay);
    });

    // ── TinyMCE ──────────────────────────────────────────────
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-editor',
            height: 320,
            menubar: false,
            plugins: 'lists link table code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            branding: false,
            promotion: false,
            skin: 'oxide',
            content_css: 'default',
        });
    }
});
</script>

<?php if (isset($extraAdminJs)) echo $extraAdminJs; ?>
</body>
</html>
