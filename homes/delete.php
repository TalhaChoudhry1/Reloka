<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check whether user is logged in
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
$home_id = filter_input(INPUT_POST, "home_id", FILTER_VALIDATE_INT);

if (!$home_id) {
    die("Invalid home ID.");
}

// Make sure the home belongs to the logged-in user
$stmt = $pdo->prepare("SELECT home_id FROM homes WHERE home_id = ? AND user_id = ?");
$stmt->execute([$home_id, $user_id]);

if ($stmt->fetch()) {

    // Remove child records first (condition logs -> items -> home)
    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            "DELETE condition_logs FROM condition_logs
             JOIN items ON condition_logs.item_id = items.item_id
             WHERE items.home_id = ?"
        )->execute([$home_id]);

        $pdo->prepare("DELETE FROM items WHERE home_id = ?")->execute([$home_id]);

        $pdo->prepare("DELETE FROM homes WHERE home_id = ? AND user_id = ?")
            ->execute([$home_id, $user_id]);

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Could not delete this home. Please try again.");
    }
}

header("Location: index.php");
exit;
