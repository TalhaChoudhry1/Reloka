<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT
        items.*,
        homes.home_name,
        categories.category_name
     FROM items
     JOIN homes ON items.home_id = homes.home_id
     JOIN categories ON items.category_id = categories.category_id
     WHERE homes.user_id = ?"
);

$stmt->execute([$user_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Items | Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .main-content {
            max-width: 1500px;
            padding: 0 60px;
        }

        .items-hero {
            height: 300px;
            position: relative;
            overflow: hidden;
            margin: 35px 0 45px;
        }

        .items-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .items-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(25, 39, 30, 0.78),
                rgba(25, 39, 30, 0.08)
            );
        }

        .hero-text {
            position: absolute;
            z-index: 1;
            left: 40px;
            bottom: 38px;
            color: white;
            max-width: 580px;
        }

        .hero-label {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #E5C5AF;
            margin-bottom: 15px;
        }

        .hero-text h1 {
            font-family: Georgia, serif;
            font-size: 46px;
            font-weight: bold;
        }

        .hero-text p {
            font-size: 16px;
            font-weight: 500;
            line-height: 1.7;
            color: #FFFFFF;
            margin-top: 12px;
        }

        .items-section {
            margin-bottom: 60px;
        }

        .items-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .items-heading h2 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            color: #263B32;
        }

        .items-heading p {
            font-size: 15px;
            color: #5F645B;
            margin-top: 8px;
        }

        .items-list {
            border-top: 1px solid #DDDCD3;
        }

        .item-row {
            display: grid;
            grid-template-columns: 1.8fr 1.1fr 1fr 1.3fr auto;
            align-items: center;
            gap: 20px;
            padding: 22px 12px;
            border-bottom: 1px solid #DDDCD3;
        }

        .item-info h3 {
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: bold;
            color: #263B32;
        }

        .item-info p {
            font-size: 15px;
            color: #5F645B;
            margin-top: 7px;
        }

        .item-detail {
            font-size: 16px;
            font-weight: 500;
            color: #263B32;
            margin-top: 7px;
        }

        .item-label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #5F645B;
            margin-bottom: 8px;
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-actions a,
        .item-actions button {
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .item-actions a {
            color: #263B32;
            text-decoration: none;
        }

        .item-actions button {
            color: #B87558;
        }

        .empty-state {
            padding: 35px 10px;
            border-top: 1px solid #DDDCD3;
            border-bottom: 1px solid #DDDCD3;
        }

        .empty-state h3 {
            font-family: Georgia, serif;
            font-size: 26px;
            font-weight: bold;
            color: #263B32;
        }

        .empty-state p {
            font-size: 15px;
            color: #5F645B;
            margin: 12px 0 22px;
        }

        @media (max-width: 1100px) {

            .item-row {
                grid-template-columns: 1.5fr 1fr 1fr;
                gap: 20px;
            }

            .item-actions {
                grid-column: 1 / -1;
            }

        }

        @media (max-width: 850px) {

            .main-content {
                padding: 0 30px;
            }

            .items-hero {
                height: 270px;
            }

            .hero-text h1 {
                font-size: 38px;
            }

        }

        @media (max-width: 700px) {

            .main-content {
                padding: 0 20px;
            }

            .items-hero {
                height: 300px;
                margin-top: 25px;
            }

            .hero-text {
                left: 22px;
                right: 20px;
                bottom: 25px;
            }

            .hero-text h1 {
                font-size: 32px;
            }

            .items-heading {
                align-items: flex-start;
                gap: 20px;
                flex-direction: column;
            }

            .item-row {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .item-actions {
                grid-column: 1 / -1;
            }

        }

        @media (max-width: 450px) {

            .item-row {
                grid-template-columns: 1fr;
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


    <main class="main-content">

        <header class="topbar">

            <span>PERSONAL PROPERTY REGISTER</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </header>


        <section class="items-hero">

            <img
                src="../assets/images/items-living-room.jpg"
                alt="A warm, furnished living room"
            >

            <div class="hero-text">

                <p class="hero-label">YOUR SPACE / ITEMS</p>

                <h1>Things worth keeping.</h1>

                <p>
                    From the furniture you sit on to the little things
                    you use every day, keep a record of what you own.
                </p>

            </div>

        </section>


        <section class="items-section">

            <div class="items-heading">

                <div>

                    <h2>Your belongings</h2>

                    <p>
                        <?php echo count($items); ?>
                        <?php echo count($items) === 1 ? "item in your register" : "items in your register"; ?>
                    </p>

                </div>

                <a href="add.php" class="primary-button">
                    + Add Item
                </a>

            </div>


            <?php if (count($items) > 0): ?>

                <div class="items-list">

                    <?php foreach ($items as $item): ?>

                        <div class="item-row">

                            <div class="item-info">

                                <h3>
                                    <?php echo htmlspecialchars($item["item_name"]); ?>
                                </h3>

                                <p>
                                    <?php echo htmlspecialchars($item["brand"] ?? "No brand specified"); ?>
                                </p>

                            </div>


                            <div class="item-detail">

                                <span class="item-label">KEPT AT</span>

                                <?php echo htmlspecialchars($item["home_name"]); ?>

                            </div>


                            <div class="item-detail">

                                <span class="item-label">CATEGORY</span>

                                <?php echo htmlspecialchars($item["category_name"]); ?>

                            </div>


                            <div class="item-detail">

                                <span class="item-label">PURCHASE PRICE</span>

                                <?php
                                echo $item["purchase_price"] !== null
                                    ? "Rs. " . number_format((float)$item["purchase_price"], 2)
                                    : "Not specified";
                                ?>

                            </div>


                            <div class="item-actions">

                                <a href="edit.php?id=<?php echo $item['item_id']; ?>">
                                    Edit
                                </a>

                                <form action="delete.php" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this item?');">

                                    <input
                                        type="hidden"
                                        name="item_id"
                                        value="<?php echo $item['item_id']; ?>"
                                    >

                                    <button type="submit">Delete</button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <h3>Nothing on the list just yet.</h3>

                    <p>
                        Start adding the things that make your home yours.
                    </p>

                    <a href="add.php" class="primary-button">
                        + Add Your First Item
                    </a>

                </div>

            <?php endif; ?>

        </section>


        <footer class="dashboard-footer">
            RELOKA &nbsp; · &nbsp; A RECORD OF WHAT MATTERS.
        </footer>

    </main>

</div>

</body>

</html>