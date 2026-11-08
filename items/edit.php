<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Get item ID
$item_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$item_id) {
    die("Invalid item ID.");
}

// Fetch item belonging to this user
$stmt = $pdo->prepare(
    "SELECT items.*
     FROM items
     JOIN homes ON items.home_id = homes.home_id
     WHERE items.item_id = ? AND homes.user_id = ?"
);

$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}

// Fetch user's homes
$stmt = $pdo->prepare(
    "SELECT * FROM homes WHERE user_id = ?"
);

$stmt->execute([$user_id]);
$homes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $home_id = filter_input(INPUT_POST, "home_id", FILTER_VALIDATE_INT);
    $category_id = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);

    $item_name = trim($_POST["item_name"] ?? "");
    $brand = trim($_POST["brand"] ?? "");
    $purchase_date = $_POST["purchase_date"] ?? "";
    $purchase_price = $_POST["purchase_price"] ?? "";
    $insured_value = $_POST["insured_value"] ?? "";
    $warranty_end = $_POST["warranty_end"] ?? "";

    if (!$home_id || !$category_id || $item_name === "") {

        $error = "Please fill in all required fields.";

    } elseif (strlen($item_name) > 100 || strlen($brand) > 60) {

        $error = "Item name or brand is too long.";

    } elseif (
        ($purchase_price !== "" && (!is_numeric($purchase_price) || $purchase_price < 0)) ||
        ($insured_value !== "" && (!is_numeric($insured_value) || $insured_value < 0))
    ) {

        $error = "Please enter valid prices.";

    } else {

        // Verify home ownership
        $stmt = $pdo->prepare(
            "SELECT home_id FROM homes
             WHERE home_id = ? AND user_id = ?"
        );

        $stmt->execute([$home_id, $user_id]);
        $home_exists = $stmt->fetch();

        // Verify category
        $stmt = $pdo->prepare(
            "SELECT category_id FROM categories
             WHERE category_id = ?"
        );

        $stmt->execute([$category_id]);
        $category_exists = $stmt->fetch();

        if (!$home_exists || !$category_exists) {

            $error = "Invalid home or category.";

        } else {

            // Update item
            $stmt = $pdo->prepare(
                "UPDATE items
                 SET home_id = ?,
                     category_id = ?,
                     item_name = ?,
                     brand = ?,
                     purchase_date = ?,
                     purchase_price = ?,
                     insured_value = ?,
                     warranty_end = ?
                 WHERE item_id = ?"
            );

            $stmt->execute([
                $home_id,
                $category_id,
                $item_name,
                $brand ?: null,
                $purchase_date ?: null,
                $purchase_price !== "" ? $purchase_price : null,
                $insured_value !== "" ? $insured_value : 0,
                $warranty_end ?: null,
                $item_id
            ]);

            header("Location: index.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Item | Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .form-container {
            max-width: 600px;
            margin-bottom: 60px;
        }

        .item-form label {
            display: block;
            font-size: 13px;
            color: #263B32;
            margin-bottom: 8px;
        }

        .item-form input,
        .item-form select {
            width: 100%;
            padding: 13px;
            border: 1px solid #DDDCD3;
            background: #FFFFFF;
            font-size: 13px;
            margin-bottom: 22px;
            outline: none;
        }

        .item-form input:focus,
        .item-form select:focus {
            border-color: #899783;
        }

        .form-error {
            color: #B87558;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .form-links {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 5px;
        }

        .back-link {
            color: #777C73;
            font-size: 12px;
            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            <span class="brand-mark">R.</span>
            <span class="brand-name">reloka</span>
        </div>

        <p class="sidebar-label">WORKSPACE</p>

        <nav class="sidebar-nav">

            <a href="../dashboard.php" class="nav-link">Overview</a>

            <a href="../homes/index.php" class="nav-link">My Homes</a>

            <a href="index.php" class="nav-link active">My Items</a>

            <a href="../condition_logs/index.php" class="nav-link">Condition History</a>

            <a href="../insurance/index.php" class="nav-link">Insurance</a>

            <a href="../warranty/index.php" class="nav-link">Warranty</a>

        </nav>

        <div class="sidebar-bottom">

            <p>YOUR HOME, IN ORDER.</p>

            <a href="../auth/logout.php" class="logout-link">Log out</a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <header class="topbar">

            <span>PERSONAL PROPERTY REGISTER</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </header>


        <section class="page-intro">

            <p class="eyebrow">YOUR SPACE / ITEMS / EDIT</p>

            <h1>Edit your item.</h1>

            <p>Update the details of your belonging.</p>

        </section>


        <section class="form-container">

            <?php if (!empty($error)): ?>

                <p class="form-error">
                    <?php echo htmlspecialchars($error); ?>
                </p>

            <?php endif; ?>


            <form method="POST" class="item-form">

                <label for="home_id">Home</label>

                <select name="home_id" id="home_id" required>

                    <?php foreach ($homes as $home): ?>

                        <option
                            value="<?php echo $home["home_id"]; ?>"
                            <?php echo (($_POST["home_id"] ?? $item["home_id"]) == $home["home_id"]) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($home["home_name"]); ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <label for="item_name">Item Name</label>

                <input
                    type="text"
                    id="item_name"
                    name="item_name"
                    value="<?php echo htmlspecialchars($_POST["item_name"] ?? $item["item_name"]); ?>"
                    maxlength="100"
                    required
                >


                <label for="category_id">Category</label>

                <select name="category_id" id="category_id" required>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?php echo $category["category_id"]; ?>"
                            <?php echo (($_POST["category_id"] ?? $item["category_id"]) == $category["category_id"]) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($category["category_name"]); ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <label for="brand">Brand</label>

                <input
                    type="text"
                    id="brand"
                    name="brand"
                    value="<?php echo htmlspecialchars($_POST["brand"] ?? $item["brand"] ?? ""); ?>"
                    maxlength="60"
                >


                <label for="purchase_date">Purchase Date</label>

                <input
                    type="date"
                    id="purchase_date"
                    name="purchase_date"
                    value="<?php echo htmlspecialchars($_POST["purchase_date"] ?? $item["purchase_date"] ?? ""); ?>"
                >


                <label for="purchase_price">Purchase Price (PKR)</label>

                <input
                    type="number"
                    id="purchase_price"
                    name="purchase_price"
                    value="<?php echo htmlspecialchars($_POST["purchase_price"] ?? $item["purchase_price"] ?? ""); ?>"
                    min="0"
                    step="0.01"
                >


                <label for="insured_value">Insured Value (PKR)</label>

                <input
                    type="number"
                    id="insured_value"
                    name="insured_value"
                    value="<?php echo htmlspecialchars($_POST["insured_value"] ?? $item["insured_value"] ?? "0"); ?>"
                    min="0"
                    step="0.01"
                >


                <label for="warranty_end">Warranty End Date</label>

                <input
                    type="date"
                    id="warranty_end"
                    name="warranty_end"
                    value="<?php echo htmlspecialchars($_POST["warranty_end"] ?? $item["warranty_end"] ?? ""); ?>"
                >


                <div class="form-links">

                    <button type="submit" class="primary-button">
                        Update Item
                    </button>

                    <a href="index.php" class="back-link">
                        Cancel
                    </a>

                </div>

            </form>

        </section>


        <footer class="dashboard-footer">
            RELOKA — A RECORD OF WHAT MATTERS.
        </footer>

    </main>

</div>

</body>

</html>