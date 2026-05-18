<?php
/**
 * Admin: Export Orders to CSV
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is admin
requireAdmin();

$db = getDB();

// We'll export only Confirmed or Processing orders since those usually need fulfillment.
// But let's allow a URL param to export specific statuses if needed, otherwise all non-cancelled.
$statusFilter = inputStr('status', '', 'GET');

$where = "1=1";
$params = [];

if ($statusFilter) {
    if ($statusFilter === 'active') {
        $where .= " AND o.status IN ('confirmed', 'processing')";
    } else {
        $where .= " AND o.status = ?";
        $params[] = $statusFilter;
    }
}

// Fetch Orders
$sql = "SELECT o.order_number, o.created_at, o.status, COALESCE(o.total_amount, o.total) as order_total,
               o.ship_name AS shipping_name, o.ship_email AS shipping_email, o.ship_phone AS shipping_phone,
               o.ship_address AS shipping_address, o.ship_city AS shipping_city, o.ship_state AS shipping_state, o.ship_pincode AS shipping_pincode,
               o.payment_method, o.payment_status,
               o.notes
        FROM orders o 
        WHERE $where 
        ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers to trigger CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_export_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Column headings
fputcsv($output, [
    'Order No',
    'Date',
    'Status',
    'Total Amount',
    'Customer Name',
    'Email',
    'Phone',
    'Address',
    'City',
    'State',
    'Pincode',
    'Payment Method',
    'Payment Status',
    'Notes'
]);

// Write data rows
if ($orders) {
    foreach ($orders as $o) {
        fputcsv($output, [
            $o['order_number'],
            $o['created_at'],
            ucfirst($o['status']),
            $o['order_total'],
            $o['shipping_name'],
            $o['shipping_email'],
            $o['shipping_phone'],
            // clean up address newlines
            str_replace(["\r\n", "\n", "\r"], ", ", $o['shipping_address']),
            $o['shipping_city'],
            $o['shipping_state'],
            $o['shipping_pincode'],
            strtoupper($o['payment_method']),
            ucfirst($o['payment_status']),
            $o['notes']
        ]);
    }
}

fclose($output);
exit;
