<?php
/**
 * Admin Settings
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $menuLabels = $_POST['menu_label'] ?? [];
    $menuUrls = $_POST['menu_url'] ?? [];
    $headerMenuItems = [];

    if (is_array($menuLabels) && is_array($menuUrls)) {
        foreach ($menuLabels as $index => $label) {
            $cleanLabel = trim(strip_tags((string)$label));
            $cleanUrl = trim(strip_tags((string)($menuUrls[$index] ?? '')));

            if ($cleanLabel === '' || $cleanUrl === '') {
                continue;
            }

            $headerMenuItems[] = [
                'label' => $cleanLabel,
                'url' => $cleanUrl,
            ];

            if (count($headerMenuItems) >= 12) {
                break;
            }
        }
    }

    if (isset($_POST['test_email'])) {
        $email = inputStr('email', '', 'POST');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Please enter a valid test email address.');
        }

        $sent = sendEmail(
            $email,
            'SMTP Test - ' . getSetting('site_name', 'Boutique Store'),
            '<h2>SMTP connection successful</h2><p>This test email confirms that your current store email settings can send messages successfully.</p>'
        );

        jsonResponse(
            $sent,
            $sent ? 'Test email sent successfully.' : 'SMTP connection failed. Please review your saved gateway settings.'
        );
    }
    
    $settings = [
        'site_name' => inputStr('site_name', '', 'POST'),
        'site_tagline' => inputStr('site_tagline', '', 'POST'),
        'site_currency' => inputStr('site_currency', 'INR', 'POST'),
        'site_currency_symbol' => inputStr('site_currency_symbol', '₹', 'POST'),
        'default_shipping_cost' => (float)inputStr('default_shipping_cost', 0, 'POST'),
        'min_order_free_shipping' => (float)inputStr('min_order_free_shipping', 0, 'POST'),
        'gst_percent' => (float)inputStr('gst_percent', 0, 'POST'),
        'theme_primary_color' => inputStr('theme_primary_color', '#0F0F0F', 'POST'),
        'home_style' => inputStr('home_style', 'editorial', 'POST'),
        'header_style' => (int)inputStr('header_style', 1, 'POST'),
        'footer_style' => (int)inputStr('footer_style', 1, 'POST'),
        'header_menu_items' => json_encode($headerMenuItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        
        // Contact Info
        'site_phone' => inputStr('site_phone', '', 'POST'),
        'site_email' => inputStr('site_email', '', 'POST'),
        'site_address' => inputStr('site_address', '', 'POST'),
        
        // Social Links
        'facebook_url' => inputStr('facebook_url', '', 'POST'),
        'instagram_url' => inputStr('instagram_url', '', 'POST'),
        'twitter_url' => inputStr('twitter_url', '', 'POST'),
        'youtube_url' => inputStr('youtube_url', '', 'POST'),
        'linkedin_url' => inputStr('linkedin_url', '', 'POST'),
        'pinterest_url' => inputStr('pinterest_url', '', 'POST'),
        'whatsapp_number' => inputStr('whatsapp_number', '', 'POST'),
        
        // Razorpay
        'razorpay_key' => inputStr('razorpay_key', '', 'POST'),
        'razorpay_secret' => inputStr('razorpay_secret', '', 'POST'),
        
        // PhonePe
        'phonepe_merchant_id' => inputStr('phonepe_merchant_id', '', 'POST'),
        'phonepe_salt_key' => inputStr('phonepe_salt_key', '', 'POST'),
        'phonepe_env' => inputStr('phonepe_env', 'UAT', 'POST'),
        
        // Paytm
        'paytm_merchant_id' => inputStr('paytm_merchant_id', '', 'POST'),
        'paytm_merchant_key' => inputStr('paytm_merchant_key', '', 'POST'),
        'paytm_env' => inputStr('paytm_env', 'TEST', 'POST'),

        // SMTP Config
        'smtp_host' => inputStr('smtp_host', '', 'POST'),
        'smtp_port' => inputStr('smtp_port', '587', 'POST'),
        'smtp_user' => inputStr('smtp_user', '', 'POST'),
        'smtp_pass' => inputStr('smtp_pass', '', 'POST', false), // Don't clean password spaces/chars
        'smtp_encryption' => inputStr('smtp_encryption', 'tls', 'POST'),

        // Shiprocket
        'shiprocket_email' => inputStr('shiprocket_email', '', 'POST'),
        'shiprocket_password' => inputStr('shiprocket_password', '', 'POST', false)
    ];
    
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        foreach ($settings as $key => $val) {
            $stmt->execute([$key, (string)$val]);
        }
        $db->commit();
        refreshSetting();
        setFlash('success', 'Settings updated successfully.');
    } catch (PDOException $e) {
        $db->rollBack();
        setFlash('error', 'Error updating settings.');
    }
    
    redirect(url('admin/settings/index.php'));
}

// Fetch all settings
$stmt = $db->query("SELECT * FROM settings");
$rows = $stmt->fetchAll();
$s = [];
foreach ($rows as $r) {
    $s[$r['key_name']] = $r['value'];
}

$dbStatus = getDatabaseStatus();
$headerMenuItems = getHeaderMenuItems(true);
$headerMenuEditorItems = $headerMenuItems ?: [['label' => '', 'url' => '']];

$pageTitle = 'Site Settings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-900 text-dark mb-1 ls-1">Atelier Suite Configuration</h3>
        <p class="text-muted small mb-0 fw-700 text-uppercase ls-2">Refining Your Brand Parameters</p>
    </div>
</div>

<div class="admin-card border-0 shadow-sm overflow-hidden mb-5">
    <!-- Luxury Tab Header -->
    <div class="bg-white border-bottom">
        <ul class="nav nav-tabs nav-tabs-custom border-0 px-4" id="settingsTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                    <i class="fa-solid fa-gem me-2"></i> Boutique
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="mail-tab" data-bs-toggle="tab" data-bs-target="#mail" type="button" role="tab">
                    <i class="fa-solid fa-paper-plane me-2"></i> Communication
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="presence-tab" data-bs-toggle="tab" data-bs-target="#presence" type="button" role="tab">
                    <i class="fa-solid fa-share-nodes me-2"></i> Presence
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="protocol-tab" data-bs-toggle="tab" data-bs-target="#protocol" type="button" role="tab">
                    <i class="fa-solid fa-shield-halved me-2"></i> Protocol
                </button>
            </li>
        </ul>
    </div>
    
    <div class="p-5">
        <form action="<?= url('admin/settings/index.php') ?>" method="POST" enctype="multipart/form-data" id="settingsForm">
            <?= csrfField() ?>
            
            <div class="tab-content" id="settingsTabsContent">
                <!-- Tabs will be populated in subsequent steps -->
    
                <!-- Boutique General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="row g-4 mb-5">
                        <div class="col-md-7">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Identity & Metadata</h6>
                            <div class="mb-4">
                                <label class="form-label">Boutique Name</label>
                                <input type="text" name="site_name" class="form-control" value="<?= e($s['site_name'] ?? '') ?>" placeholder="Luxury Name...">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Exquisite Tagline</label>
                                <input type="text" name="site_tagline" class="form-control" value="<?= e($s['site_tagline'] ?? '') ?>" placeholder="The Ultimate Jewelry Experience...">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Currency Symbol</label>
                                    <input type="text" name="site_currency_symbol" class="form-control" value="<?= e($s['site_currency_symbol'] ?? '₹') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Currency Code</label>
                                    <input type="text" name="site_currency" class="form-control" value="<?= e($s['site_currency'] ?? 'INR') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Visual Essence</h6>
                            <div class="mb-4">
                                <label class="form-label fw-800">Theme Primary Accent</label>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border">
                                    <input type="color" name="theme_primary_color" class="form-control form-control-color border-0" value="<?= e($s['theme_primary_color'] ?? '#c5a059') ?>">
                                    <span class="text-muted small fw-700 text-uppercase ls-1" style="font-size: 0.65rem;">Brand Highlight Color</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Homepage Experience</label>
                                <select name="home_style" class="form-select">
                                    <option value="editorial" <?= ($s['home_style'] ?? 'editorial') === 'editorial' ? 'selected' : '' ?>>Current Editorial Homepage</option>
                                    <option value="marketplace" <?= ($s['home_style'] ?? 'editorial') === 'marketplace' ? 'selected' : '' ?>>Reference-Inspired Marketplace Homepage</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Header Experience</label>
                                <select name="header_style" class="form-select">
                                    <option value="1" <?= ($s['header_style'] ?? 1) == 1 ? 'selected' : '' ?>>Style 1: Professional</option>
                                    <option value="2" <?= ($s['header_style'] ?? 1) == 2 ? 'selected' : '' ?>>Style 2: Minimal Centered</option>
                                    <option value="3" <?= ($s['header_style'] ?? 1) == 3 ? 'selected' : '' ?>>Style 3: Contemporary Fixed</option>
                                    <option value="4" <?= ($s['header_style'] ?? 1) == 4 ? 'selected' : '' ?>>Style 4: Luxury Atelier</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Footer Experience</label>
                                <select name="footer_style" class="form-select">
                                    <option value="1" <?= ($s['footer_style'] ?? 1) == 1 ? 'selected' : '' ?>>Style 1: Luxury Editorial</option>
                                    <option value="2" <?= ($s['footer_style'] ?? 1) == 2 ? 'selected' : '' ?>>Style 2: Minimal Bright</option>
                                    <option value="3" <?= ($s['footer_style'] ?? 1) == 3 ? 'selected' : '' ?>>Style 3: Dark Conversion</option>
                                    <option value="4" <?= ($s['footer_style'] ?? 1) == 4 ? 'selected' : '' ?>>Style 4: Elegant Centered</option>
                                </select>
                            </div>
                            <div class="rounded-4 border p-4 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-900 text-uppercase ls-1 fs-9">Database Status</span>
                                    <span class="badge rounded-pill <?= $dbStatus['connected'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $dbStatus['connected'] ? 'Connected' : 'Issue' ?>
                                    </span>
                                </div>
                                <div class="small text-muted fw-700">Database: <?= e($dbStatus['database']) ?></div>
                                <div class="small text-muted fw-700">Tables Detected: <?= (int)$dbStatus['tables'] ?></div>
                                <div class="small text-muted fw-700">Driver: <?= e($dbStatus['driver']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="rounded-4 border bg-white p-4 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                    <div>
                                        <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-1">Header Menu Manager</h6>
                                        <p class="text-muted mb-0 small">Add, remove, and arrange the header links shown on desktop and mobile.</p>
                                    </div>
                                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2 fw-800 text-uppercase ls-1 fs-9" id="addHeaderMenuItem">
                                        <i class="fa-solid fa-plus me-2"></i> Add Menu Item
                                    </button>
                                </div>

                                <div id="headerMenuBuilder" class="d-grid gap-3">
                                    <?php foreach ($headerMenuEditorItems as $item): ?>
                                        <div class="row g-3 align-items-end menu-builder-row" data-menu-row>
                                            <div class="col-md-4">
                                                <label class="form-label">Menu Label</label>
                                                <input type="text" name="menu_label[]" class="form-control" value="<?= e($item['label'] ?? '') ?>" placeholder="Temple Jewelry">
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label">Menu Link</label>
                                                <input type="text" name="menu_url[]" class="form-control" value="<?= e($item['url'] ?? '') ?>" placeholder="products.php?category=temple-jewelry">
                                            </div>
                                            <div class="col-md-1 d-grid">
                                                <button type="button" class="btn btn-outline-danger rounded-4 py-2" data-remove-menu-item aria-label="Remove menu item">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <template id="headerMenuRowTemplate">
                                    <div class="row g-3 align-items-end menu-builder-row" data-menu-row>
                                        <div class="col-md-4">
                                            <label class="form-label">Menu Label</label>
                                            <input type="text" name="menu_label[]" class="form-control" placeholder="Bridal Collection">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label">Menu Link</label>
                                            <input type="text" name="menu_url[]" class="form-control" placeholder="products.php?featured=1">
                                        </div>
                                        <div class="col-md-1 d-grid">
                                            <button type="button" class="btn btn-outline-danger rounded-4 py-2" data-remove-menu-item aria-label="Remove menu item">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div class="mt-3 small text-muted">
                                    Use relative links like <code>products.php</code>, <code>about-us.php</code>, or <code>products.php?category=dainty-earrings</code>. Row order becomes menu order.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Communication (SMTP) Settings -->
                <div class="tab-pane fade" id="mail" role="tabpanel">
                    <div class="alert badge-soft-info border d-flex align-items-center mb-5 p-3 rounded-4">
                        <i class="fa-solid fa-circle-info me-3 fa-lg"></i>
                        <div>
                            <strong class="text-uppercase ls-1 fs-9 d-block">Protocol Note</strong>
                            <span class="fw-600">Secure SMTP protocols for automated fulfillment updates.</span>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">SMTP Gateway</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?= e($s['smtp_host'] ?? '') ?>" placeholder="smtp.hostinger.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Secure Port</label>
                            <input type="text" name="smtp_port" class="form-control" value="<?= e($s['smtp_port'] ?? '465') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="ssl" <?= ($s['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL (Standard)</option>
                                <option value="tls" <?= ($s['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS (Secure)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gateway Username</label>
                            <input type="text" name="smtp_user" class="form-control" value="<?= e($s['smtp_user'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gateway Secret</label>
                            <input type="password" name="smtp_pass" class="form-control" value="<?= e($s['smtp_pass'] ?? '') ?>" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="button" id="testEmailBtn" class="btn btn-outline-primary fw-900 rounded-pill px-4 ls-1 text-uppercase fs-9 py-2 border-2">
                        <i class="fa-solid fa-flask-vial me-2"></i> Test Communication
                    </button>
                </div>
                
                <!-- Presence (Social) Settings -->
                <div class="tab-pane fade" id="presence" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Direct Engagement</h6>
                            <div class="mb-4">
                                <label class="form-label">WhatsApp Concierge</label>
                                <input type="text" name="whatsapp_number" class="form-control" value="<?= e($s['whatsapp_number'] ?? '') ?>" placeholder="+91...">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Instagram Portfolio</label>
                                <input type="url" name="instagram_url" class="form-control" value="<?= e($s['instagram_url'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Global Channels</h6>
                            <div class="mb-4">
                                <label class="form-label">Facebook Showcase</label>
                                <input type="url" name="facebook_url" class="form-control" value="<?= e($s['facebook_url'] ?? '') ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Pinterest Collection</label>
                                <input type="url" name="pinterest_url" class="form-control" value="<?= e($s['pinterest_url'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Protocol Settings -->
                <div class="tab-pane fade" id="protocol" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Logistics Protocol</h6>
                            <div class="mb-4">
                                <label class="form-label">Standard Shipping (INR)</label>
                                <input type="number" step="0.01" name="default_shipping_cost" class="form-control" value="<?= e($s['default_shipping_cost'] ?? 0) ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Threshold for Complimentary Delivery</label>
                                <input type="number" step="0.01" name="min_order_free_shipping" class="form-control" value="<?= e($s['min_order_free_shipping'] ?? 0) ?>">
                            </div>
                            
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 mt-5 border-bottom pb-2">Shiprocket Integration</h6>
                            <div class="mb-4">
                                <label class="form-label">Shiprocket API Email</label>
                                <input type="email" name="shiprocket_email" class="form-control" value="<?= e($s['shiprocket_email'] ?? '') ?>" placeholder="admin@example.com">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Shiprocket API Password</label>
                                <input type="password" name="shiprocket_password" class="form-control" value="<?= e($s['shiprocket_password'] ?? '') ?>" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-900 text-dark text-uppercase ls-2 fs-9 mb-4 border-bottom pb-2">Fiscal Setup</h6>
                            <div class="mb-4">
                                <label class="form-label">Tax Allocation (GST %)</label>
                                <input type="number" step="0.01" name="gst_percent" class="form-control" value="<?= e($s['gst_percent'] ?? 0) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <div class="border-top mt-5 pt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary fw-900 rounded-pill px-5 py-3 ls-1 text-uppercase fs-8 shadow-gold">
                    <i class="fa-solid fa-gem me-2"></i> Update Brand Essence
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Phase 17 SweetAlert Settings Suite
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Commit Changes?',
        text: "The boutique parameters will be updated across the system instantly.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Commit Changes',
        cancelButtonText: 'Retain State',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

document.getElementById('testEmailBtn').addEventListener('click', function() {
    Swal.fire({
        title: 'Communication Test',
        text: "Enter a recipient to verify the SMTP gateway connection.",
        input: 'email',
        inputValue: '<?= e($s['smtp_user'] ?? '') ?>',
        showCancelButton: true,
        confirmButtonText: 'Transmit Package',
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2 rounded-pill fw-900 ls-1 shadow-gold',
            cancelButton: 'btn btn-light px-4 py-2 rounded-pill fw-900 ls-1 border ms-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const btn = this;
            btn.disabled = true;
            
            Swal.fire({
                title: 'Transmitting...',
                html: 'Securing connection to the boutique gateway...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    const formData = new FormData();
                    formData.append('test_email', '1');
                    formData.append('email', result.value);
                    formData.append('csrf_token', '<?= csrfToken() ?>');
                    
                    fetch('<?= url('admin/settings/index.php') ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire(data.success ? 'Success' : 'Error', data.message, data.success ? 'success' : 'error');
                    })
                    .catch(e => {
                        Swal.fire('Failure', 'System gateway is currently unreachable.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
                }
            });
        }
    });
});

const headerMenuBuilder = document.getElementById('headerMenuBuilder');
const headerMenuRowTemplate = document.getElementById('headerMenuRowTemplate');
const addHeaderMenuItemBtn = document.getElementById('addHeaderMenuItem');

if (headerMenuBuilder && headerMenuRowTemplate && addHeaderMenuItemBtn) {
    addHeaderMenuItemBtn.addEventListener('click', function() {
        const fragment = headerMenuRowTemplate.content.cloneNode(true);
        headerMenuBuilder.appendChild(fragment);
    });

    headerMenuBuilder.addEventListener('click', function(event) {
        const removeButton = event.target.closest('[data-remove-menu-item]');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('[data-menu-row]');
        if (row) {
            row.remove();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
