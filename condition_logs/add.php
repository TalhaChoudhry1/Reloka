<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT items.item_id, items.item_name, homes.home_name
     FROM items
     JOIN homes ON items.home_id = homes.home_id
     WHERE homes.user_id = ?
     ORDER BY items.item_name"
);

$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $item_id = filter_input(INPUT_POST, "item_id", FILTER_VALIDATE_INT);
    $condition_status = $_POST["condition_status"] ?? "";
    $note = trim($_POST["note"] ?? "");
    $log_date = $_POST["log_date"] ?? "";

    $allowed_statuses = ["New", "Good", "Damaged", "Needs Repair"];

    $date_object = DateTime::createFromFormat('!Y-m-d', $log_date);
    $valid_date = $date_object && $date_object->format('Y-m-d') === $log_date;

    if (!$item_id || !in_array($condition_status, $allowed_statuses, true)) {
        $error = "Please select a valid item and condition.";
    }

    elseif (strlen($note) > 255) {
        $error = "Your note cannot exceed 255 characters.";
    }

    elseif (!$valid_date) {
        $error = "Please enter a valid date.";
    }

    else {

        $stmt = $pdo->prepare(
            "SELECT items.item_id
             FROM items
             JOIN homes ON items.home_id = homes.home_id
             WHERE items.item_id = ? AND homes.user_id = ?"
        );

        $stmt->execute([$item_id, $user_id]);
        $item_exists = $stmt->fetch();

        if (!$item_exists) {

            $error = "Invalid item selected.";

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO condition_logs
                 (item_id, log_date, condition_status, note)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $item_id,
                $log_date,
                $condition_status,
                $note ?: null
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

    <title>Record Condition - Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .page-intro {
            margin-bottom: 30px;
        }

        .form-container {
            max-width: 600px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8d8d0;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            background: #ffffff;
            font-family: Arial, sans-serif;
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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

        .empty-message {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="dashboard-layout">

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

            <a href="../items/index.php" class="nav-link">
                My Items
            </a>

            <a href="index.php" class="nav-link active">
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


    <main class="main-content">

        <header class="topbar">
            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>
        </header>


        <section class="welcome-section page-intro">

            <p class="eyebrow">ITEM MANAGEMENT</p>

            <h1>Record item condition</h1>

            <p>Update the current condition of one of your belongings.</p>

        </section>


        <div class="form-container">

            <?php if ($error !== ""): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>


            <?php if (empty($items)): ?>

                <p class="empty-message">
                    You need to add an item before recording its condition.
                </p>

                <a href="../items/add.php" class="cancel-link">
                    Add an Item
                </a>

            <?php else: ?>

                <form method="POST">

                    <div class="form-group">
                        <label for="item_id">Select Item *</label>

                        <select name="item_id" id="item_id" required>

                            <option value="">Choose an item</option>

                            <?php foreach ($items as $item): ?>

                                <option
                                    value="<?php echo $item["item_id"]; ?>"
                                    <?php echo (($_POST["item_id"] ?? "") == $item["item_id"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($item["item_name"] . " - " . $item["home_name"]); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>


                    <div class="form-group">
                        <label for="condition_status">Condition *</label>

                        <select name="condition_status" id="condition_status" required>

                            <option value="">Choose condition</option>

                            <?php foreach (["New", "Good", "Damaged", "Needs Repair"] as $status): ?>

                                <option
                                    value="<?php echo $status; ?>"
                                    <?php echo (($_POST["condition_status"] ?? "") === $status) ? "selected" : ""; ?>
                                >
                                    <?php echo $status; ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>


                    <div class="form-group">
                        <label for="log_date">Date *</label>

                        <input
                            type="date"
                            name="log_date"
                            id="log_date"
                            value="<?php echo htmlspecialchars($_POST["log_date"] ?? date('Y-m-d')); ?>"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="note">Notes</label>

                        <textarea
                            name="note"
                            id="note"
                            maxlength="255"
                            placeholder="Describe the item's condition..."
                        ><?php echo htmlspecialchars($_POST["note"] ?? ""); ?></textarea>
                    </div>


                    <div class="form-actions">

                        <button type="submit" class="save-button">
                            Save Condition
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