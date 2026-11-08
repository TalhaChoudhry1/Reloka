<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT
            items.item_name,
            items.brand,
            items.warranty_end,
            homes.home_name
        FROM items
        JOIN homes ON items.home_id = homes.home_id
        WHERE homes.user_id = ?
        AND items.warranty_end IS NOT NULL
        ORDER BY items.warranty_end ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Warranty Tracking - Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .warranty-intro {
            margin: 45px 0 30px;
        }

        .warranty-intro .eyebrow {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #A66E54;
            margin-bottom: 12px;
        }

        .warranty-intro h1 {
            font-family: Georgia, serif;
            font-size: 46px;
            font-weight: bold;
            color: #263B32;
            margin-bottom: 12px;
        }

        .warranty-intro p:last-child {
            color: #5F645B;
            font-size: 17px;
        }

        .warranty-hero {
            height: 270px;
            position: relative;
            overflow: hidden;
            margin-bottom: 45px;
            background: #263B32;
        }

        .warranty-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            opacity: 0.65;
        }

        .warranty-hero-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 50px;
            color: white;
            background: linear-gradient(90deg, rgba(24, 43, 34, 0.85), rgba(24, 43, 34, 0.1));
        }

        .warranty-hero-content p {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #E8CDB9;
            margin-bottom: 18px;
        }

        .warranty-hero-content h2 {
            font-family: Georgia, serif;
            font-size: 44px;
            font-weight: bold;
            max-width: 500px;
            line-height: 1.2;
        }

        .records-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .records-heading h2 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            color: #263B32;
        }

        .records-heading span {
            font-size: 14px;
            font-weight: bold;
            color: #5F645B;
        }

        .table-section {
            overflow-x: auto;
            margin-bottom: 50px;
        }

        .warranty-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .warranty-table th {
            padding: 15px 12px;
            border-top: 1px solid #DDDCD3;
            border-bottom: 1px solid #DDDCD3;
            color: #5F645B;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .warranty-table td {
            padding: 19px 12px;
            border-bottom: 1px solid #E5E3DA;
            color: #263B32;
            font-size: 16px;
        }

        .warranty-table td:first-child {
            color: #263B32;
            font-weight: 700;
        }

        .warranty-table tr:hover {
            background: #EFEEE7;
        }

        .status {
            display: inline-block;
            padding: 8px 13px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 2px;
        }

        .status-active {
            background: #E8EEE7;
            color: #263B32;
        }

        .status-expired {
            background: #F7E9E5;
            color: #8F3A2D;
        }

        .expiry-date {
            color: #263B32 !important;
            white-space: nowrap;
        }

        .empty-message {
            padding: 35px 0;
            color: #5F645B;
            font-size: 16px;
            border-bottom: 1px solid #DDDCD3;
        }

        @media (max-width: 750px) {
            .warranty-intro h1 {
                font-size: 36px;
            }

            .warranty-hero {
                height: 250px;
            }

            .warranty-hero-content {
                padding: 25px;
            }

            .warranty-hero-content h2 {
                font-size: 32px;
            }

            .warranty-table {
                min-width: 600px;
            }
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

        <p class="sidebar-label">WORKSPACE</p>

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

            <a href="../condition_logs/index.php" class="nav-link">
                Condition History
            </a>

            <a href="../insurance/index.php" class="nav-link">
                Insurance
            </a>

            <a href="index.php" class="nav-link active">
                Warranty
            </a>

        </nav>

        <div class="sidebar-bottom">
            YOUR HOME, IN ORDER.

            <a href="../auth/logout.php" class="logout-link">
                Logout
            </a>
        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <span>WARRANTY RECORDS</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </header>

        <section class="warranty-intro">

            <p class="eyebrow">CARE & MAINTENANCE</p>

            <h1>Warranty Tracking</h1>

            <p>Know what's covered and when your warranties expire.</p>

        </section>

        <section class="warranty-hero">

            <img src="../assets/images/warranty-appliance.jpg" alt="Close-up photograph of a household washing machine">

            <div class="warranty-hero-content">

                <p>LOOK AFTER WHAT YOU OWN</p>

                <h2>A little coverage goes a long way.</h2>

            </div>

        </section>

        <section class="table-section">

            <div class="records-heading">

                <h2>Warranty records</h2>

                <span><?php echo count($items); ?> RECORDS</span>

            </div>

            <?php if (count($items) > 0): ?>

                <table class="warranty-table">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Brand</th>
                            <th>Home</th>
                            <th>Warranty Expiry</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($items as $item): ?>

                            <?php
                            $is_active = $item["warranty_end"] >= date("Y-m-d");
                            ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($item["item_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["brand"] ?? "N/A"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["home_name"]); ?>
                                </td>

                                <td class="expiry-date">
                                    <?php echo htmlspecialchars($item["warranty_end"]); ?>
                                </td>

                                <td>
                                    <span class="status <?php echo $is_active ? 'status-active' : 'status-expired'; ?>">
                                        <?php echo $is_active ? "Active" : "Expired"; ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty-message">
                    No warranty information available yet.
                </div>

            <?php endif; ?>

        </section>

        <footer class="dashboard-footer">
            RELOKA &nbsp; · &nbsp; YOUR HOME, IN ORDER.
        </footer>

    </main>

</div>

</body>
</html>