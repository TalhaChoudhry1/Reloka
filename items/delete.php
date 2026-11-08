<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$item_id = filter_input(INPUT_POST, "item_id", FILTER_VALIDATE_INT);

if (!$item_id) {
    die("Invalid item ID.");
}

// Make sure the item belongs to the logged-in user
$stmt = $pdo->prepare(
    "SELECT items.item_id
     FROM items
     JOIN homes ON items.home_id = homes.home_id
     WHERE items.item_id = ? AND homes.user_id = ?"
);
$stmt->execute([$item_id, $user_id]);

if ($stmt->fetch()) {

    // Remove the item's condition history first, then the item
    $pdo->beginTransaction();

    try {
        $pdo->prepare("DELETE FROM condition_logs WHERE item_id = ?")->execute([$item_id]);
        $pdo->prepare("DELETE FROM items WHERE item_id = ?")->execute([$item_id]);
        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Could not delete this item. Please try again.");
    }
}

header("Location: index.php");
exit;
