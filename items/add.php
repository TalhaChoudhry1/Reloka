<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Get all homes belonging to this user
$stmt = $pdo->prepare(
    "SELECT * FROM homes WHERE user_id = ?"
);
$stmt->execute([$user_id]);
$homes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all available categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $home_id = filter_input(INPUT_POST, "home_id", FILTER_VALIDATE_INT);
    $category_id = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);

    $item_name = trim($_POST["item_name"] ?? "");
    $brand = trim($_POST["brand"] ?? "");
    $purchase_date = $_POST["purchase_date"] ?? "";
    $purchase_price = $_POST["purchase_price"] ?? "";
    $insured_value = $_POST["insured_value"] ?? "";
    $warranty_end = $_POST["warranty_end"] ?? "";

    // Check required fields
    if (!$home_id || !$category_id || $item_name === "") {
        $error = "Please fill in all required fields.";
    }

    elseif (strlen($item_name) > 100 || strlen($brand) > 60) {
        $error = "Item name or brand is too long.";
    }

    elseif (
        ($purchase_price !== "" && (!is_numeric($purchase_price) || $purchase_price < 0)) ||
        ($insured_value !== "" && (!is_numeric($insured_value) || $insured_value < 0))
    ) {
        $error = "Please enter valid prices.";
    }

    else {

        // Check whether the selected home belongs to this user
        $stmt = $pdo->prepare(
            "SELECT home_id FROM homes
             WHERE home_id = ? AND user_id = ?"
        );
        $stmt->execute([$home_id, $user_id]);
        $home_exists = $stmt->fetch();

        // Check whether the selected category exists
        $stmt = $pdo->prepare(
            "SELECT category_id FROM categories WHERE category_id = ?"
        );
        $stmt->execute([$category_id]);
        $category_exists = $stmt->fetch();

        if (!$home_exists || !$category_exists) {

            $error = "Invalid home or category selected.";

        } else {

            // Insert the new item
            $stmt = $pdo->prepare(
                "INSERT INTO items
                (home_id, category_id, item_name, brand,
                 purchase_date, purchase_price, insured_value, warranty_end)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $home_id,
                $category_id,
                $item_name,
                $brand ?: null,
                $purchase_date ?: null,
                $purchase_price !== "" ? $purchase_price : null,
                $insured_value !== "" ? $insured_value : 0,
                $warranty_end ?: null
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

    <title>Add Item - Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .page-intro {
            margin-bottom: 30px;
        }

        .form-container {
            max-width: 650px;
            background: #ffffff;
            padding: 28px;
            border: 1px solid #e5e5df;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #292c29;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8d8d0;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            background: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #899783;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-top: 25px;
        }

        .save-button {
            background: #263b32;
            color: #ffffff;
            border: none;
            padding: 11px 22px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .save-button:hover {
            background: #354f43;
        }

        .cancel-link {
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }

        .cancel-link:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #b34236;
            background: #fff1ef;
            border: 1px solid #f0d0cc;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .empty-homes {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="brand">
            <span class="brand-mark">R.</span>
            <span class="brand-name">reloka</span>
        </div>

        <p class="sidebar-label">MENU</p>

        <nav class="sidebar-nav">

            <a href="../dashboard.php" class="nav-link">
                Overview
            </a>

            <a href="../homes/index.php" class="nav-link">
                My Homes
            </a>

            <a href="index.php" class="nav-link active">
                My Items
            </a>

            <a href="../condition_logs/index.php" class="nav-link">
                Condition History
            </a>

            <a href="../insurance/index.php" class="nav-link">
                Insurance
            </a>

            <a href="../warranty/index.php" class="nav-link">
                Warranty
            </a>

        </nav>

        <div class="sidebar-bottom">
            <a href="../auth/logout.php" class="logout-link">
                Logout
            </a>
        </div>

    </aside>


    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>
        </header>

        <section class="welcome-section page-intro">

            <p class="eyebrow">MY ITEMS</p>

            <h1>Add a new item</h1>

            <p>Add an item to one of your homes.</p>

        </section>


        <div class="form-container">

            <?php if ($error !== ""): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>


            <?php if (empty($homes)): ?>

                <p class="empty-homes">
                    You need to add a home before you can add items.
                </p>

                <a href="../homes/add.php" class="cancel-link">
                    Add a Home
                </a>

            <?php else: ?>

                <form method="POST">

                    <div class="form-group">
                        <label for="home_id">Select Home *</label>

                        <select name="home_id" id="home_id" required>

                            <option value="">Choose a home</option>

                            <?php foreach ($homes as $home): ?>

                                <option
                                    value="<?php echo $home["home_id"]; ?>"
                                    <?php echo (($_POST["home_id"] ?? "") == $home["home_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($home["home_name"]); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>


                    <div class="form-group">
                        <label for="item_name">Item Name *</label>

                        <input
                            type="text"
                            name="item_name"
                            id="item_name"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($_POST["item_name"] ?? ""); ?>"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="category_id">Category *</label>

                        <select name="category_id" id="category_id" required>

                            <option value="">Choose a category</option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?php echo $category["category_id"]; ?>"
                                    <?php echo (($_POST["category_id"] ?? "") == $category["category_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($category["category_name"]); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>


                    <div class="form-group">
                        <label for="brand">Brand</label>

                        <input
                            type="text"
                            name="brand"
                            id="brand"
                            maxlength="60"
                            value="<?php echo htmlspecialchars($_POST["brand"] ?? ""); ?>"
                        >
                    </div>


                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            value="<?php echo htmlspecialchars($_POST["purchase_date"] ?? ""); ?>"
                        >
                    </div>


                    <div class="form-group">
                        <label for="purchase_price">Purchase Price (PKR)</label>

                        <input
                            type="number"
                            name="purchase_price"
                            id="purchase_price"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($_POST["purchase_price"] ?? ""); ?>"
                        >
                    </div>


                    <div class="form-group">
                        <label for="insured_value">Insured Value (PKR)</label>

                        <input
                            type="number"
                            name="insured_value"
                            id="insured_value"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($_POST["insured_value"] ?? ""); ?>"
                        >
                    </div>


                    <div class="form-group">
                        <label for="warranty_end">Warranty End Date</label>

                        <input
                            type="date"
                            name="warranty_end"
                            id="warranty_end"
                            value="<?php echo htmlspecialchars($_POST["warranty_end"] ?? ""); ?>"
                        >
                    </div>


                    <div class="form-actions">

                        <button type="submit" class="save-button">
                            Save Item
                        </button>

                        <a href="index.php" class="cancel-link">
                            Cancel
                        </a>

                    </div>

                </form>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>
</html>