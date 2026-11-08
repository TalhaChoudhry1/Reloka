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
            items.insured_value,
            homes.home_name,
            categories.category_name
        FROM items
        JOIN homes ON items.home_id = homes.home_id
        JOIN categories ON items.category_id = categories.category_id
        WHERE homes.user_id = ?
        AND items.insured_value > 0
        ORDER BY items.insured_value DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_insured = 0;

foreach ($items as $item) {
    $total_insured += $item["insured_value"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Insurance - Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .insurance-intro {
            margin: 45px 0 30px;
        }

        .insurance-intro .eyebrow {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #A66E54;
            margin-bottom: 12px;
        }

        .insurance-intro h1 {
            font-family: Georgia, serif;
            font-size: 46px;
            font-weight: bold;
            color: #263B32;
            margin-bottom: 12px;
        }

        .insurance-intro p:last-child {
            color: #5F645B;
            font-size: 17px;
        }

        .insurance-hero {
            height: 280px;
            position: relative;
            overflow: hidden;
            margin-bottom: 45px;
            background: #263B32;
        }

        .insurance-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            opacity: 0.65;
        }

        .insurance-hero-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 50px;
            color: white;
            background: linear-gradient(90deg, rgba(24, 43, 34, 0.85), rgba(24, 43, 34, 0.1));
        }

        .insurance-hero-content p {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 18px;
            color: #E8CDB9;
        }

        .insurance-hero-content h2 {
            font-family: Georgia, serif;
            font-size: 44px;
            font-weight: bold;
            max-width: 500px;
            line-height: 1.2;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 0 35px;
            border-bottom: 1px solid #DDDCD3;
            margin-bottom: 40px;
        }

        .total-label {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #A66E54;
            margin-bottom: 12px;
        }

        .total-value {
            font-family: Georgia, serif;
            font-size: 40px;
            font-weight: bold;
            color: #263B32;
        }

        .total-note {
            color: #5F645B;
            font-size: 15px;
            text-align: right;
        }

        .table-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-heading h2 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            color: #263B32;
        }

        .table-heading span {
            font-size: 14px;
            font-weight: bold;
            color: #5F645B;
        }

        .table-section {
            margin-bottom: 50px;
            overflow-x: auto;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .items-table th {
            padding: 15px 12px;
            border-top: 1px solid #DDDCD3;
            border-bottom: 1px solid #DDDCD3;
            color: #5F645B;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 19px 12px;
            border-bottom: 1px solid #E5E3DA;
            color: #263B32;
            font-size: 16px;
        }

        .items-table td:first-child {
            color: #263B32;
            font-weight: 700;
        }

        .insured-amount {
            color: #263B32 !important;
            font-weight: 700;
            white-space: nowrap;
        }

        .items-table tr:hover {
            background: #EFEEE7;
        }

        .empty-message {
            padding: 35px 0;
            color: #5F645B;
            font-size: 16px;
            border-bottom: 1px solid #DDDCD3;
        }

        @media (max-width: 750px) {
            .insurance-intro h1 {
                font-size: 36px;
            }

            .insurance-hero {
                height: 260px;
            }

            .insurance-hero-content {
                padding: 25px;
            }

            .insurance-hero-content h2 {
                font-size: 32px;
            }

            .total-value {
                font-size: 32px;
            }

            .total-section {
                align-items: flex-start;
                flex-direction: column;
                gap: 15px;
            }

            .total-note {
                text-align: left;
            }

            .items-table {
                min-width: 650px;
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

            <a href="index.php" class="nav-link active">
                Insurance
            </a>

            <a href="../warranty/index.php" class="nav-link">
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
            <span>INSURANCE RECORDS</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>
        </header>

        <section class="insurance-intro">

            <p class="eyebrow">PROTECTION & VALUE</p>

            <h1>Insurance Overview</h1>

            <p>A clear picture of what your belongings are worth.</p>

        </section>

        <section class="insurance-hero">

            <img src="../assets/images/insurance-home.jpg" alt="A welcoming home interior">

            <div class="insurance-hero-content">

                <p>THE VALUE OF YOUR HOME</p>

                <h2>Some things are worth protecting.</h2>

            </div>

        </section>

        <section class="total-section">

            <div>
                <p class="total-label">TOTAL INSURED VALUE</p>

                <h2 class="total-value">
                    PKR <?php echo number_format($total_insured, 2); ?>
                </h2>
            </div>

            <p class="total-note">
                Across <?php echo count($items); ?>
                insured items
            </p>

        </section>

        <section class="table-section">

            <div class="table-heading">

                <h2>Insured belongings</h2>

                <span><?php echo count($items); ?> ITEMS</span>

            </div>

            <?php if (count($items) > 0): ?>

                <table class="items-table">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Home</th>
                            <th>Insured Value</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($item["item_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["brand"] ?? "N/A"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["category_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["home_name"]); ?>
                                </td>

                                <td class="insured-amount">
                                    PKR <?php echo number_format($item["insured_value"], 2); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty-message">
                    You haven't added any insured items yet.
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