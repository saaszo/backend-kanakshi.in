<?php
/**
 * Handle Newsletter Subscription
 */
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$email = inputStr('email', '', 'POST');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}

try {
    $db = getDB();
    
    // Check if already subscribed
    $stmt = $db->prepare("SELECT id FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(true, 'You are already subscribed to our newsletter!');
    }
    
    // Insert new subscriber
    $stmt = $db->prepare("INSERT INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    
    jsonResponse(true, 'Thank you for subscribing! You will receive our latest updates soon.');
    
} catch (PDOException $e) {
    jsonResponse(false, 'An error occurred. Please try again later.');
}
