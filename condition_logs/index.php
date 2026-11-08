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
        condition_logs.*,
        items.item_name,
        homes.home_name
     FROM condition_logs
     JOIN items ON condition_logs.item_id = items.item_id
     JOIN homes ON items.home_id = homes.home_id
     WHERE homes.user_id = ?
     ORDER BY condition_logs.log_date DESC, condition_logs.log_id DESC"
);

$stmt->execute([$user_id]);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Condition History | Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .main-content {
            max-width: 1500px;
            padding: 0 60px;
        }

        .history-hero {
            height: 290px;
            position: relative;
            overflow: hidden;
            margin: 35px 0 45px;
        }

        .history-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .history-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(25, 39, 30, 0.78),
                rgba(25, 39, 30, 0.05)
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

        .photo-credit {
            font-size: 12px;
            color: #5F645B;
            margin-top: -35px;
            margin-bottom: 40px;
        }

        .photo-credit a {
            color: #263B32;
            text-decoration: underline;
        }

        .history-section {
            margin-bottom: 60px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .history-header h2 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            color: #263B32;
        }

        .history-count {
            font-size: 15px;
            color: #5F645B;
            margin-top: 8px;
        }

        .record-button {
            display: inline-block;
            background: #263B32;
            color: #F5F3EC;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .record-button:hover {
            background: #354F43;
        }

        .history-list {
            border-top: 1px solid #DDDCD3;
        }

        .history-row {
            display: grid;
            grid-template-columns: 1.3fr 0.9fr 1fr 1.6fr;
            gap: 25px;
            align-items: start;
            padding: 24px 12px;
            border-bottom: 1px solid #DDDCD3;
        }

        .history-item {
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: bold;
            color: #263B32;
        }

        .history-home {
            font-size: 15px;
            color: #5F645B;
            margin-top: 7px;
        }

        .history-label {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #5F645B;
            margin-bottom: 9px;
        }

        .history-value {
            font-size: 16px;
            font-weight: 500;
            color: #263B32;
        }

        .condition-value {
            display: inline-block;
            background: #E9EEE7;
            color: #263B32;
            padding: 8px 13px;
            font-size: 14px;
            font-weight: 600;
        }

        .history-note {
            color: #263B32;
            font-size: 16px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .empty-message {
            padding: 35px 10px;
            border-top: 1px solid #DDDCD3;
            border-bottom: 1px solid #DDDCD3;
        }

        .empty-message h3 {
            font-family: Georgia, serif;
            font-size: 26px;
            font-weight: bold;
            color: #263B32;
        }

        .empty-message p {
            font-size: 15px;
            color: #5F645B;
            margin-top: 10px;
        }

        @media (max-width: 1000px) {

            .history-row {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

        }

        @media (max-width: 850px) {

            .main-content {
                padding: 0 30px;
            }

            .history-hero {
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

            .history-hero {
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

            .history-header {
                align-items: flex-start;
                gap: 20px;
                flex-direction: column;
            }

        }

        @media (max-width: 450px) {

            .history-row {
                grid-template-columns: 1fr;
                gap: 18px;
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

            <p>YOUR HOME, IN ORDER.</p>

            <a href="../auth/logout.php" class="logout-link">
                Log out
            </a>

        </div>

    </aside>


    <main class="main-content">

        <header class="topbar">

            <span>PERSONAL PROPERTY REGISTER</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </header>


        <section class="history-hero">

            <img
                src="../assets/images/condition-care.jpg"
                alt="Someone taking care of wooden furniture at home"
            >

            <div class="hero-text">

                <p class="hero-label">YOUR SPACE / CONDITION</p>

                <h1>Things worth looking after.</h1>

                <p>
                    A little care goes a long way.
                    Keep a record of how your belongings change over time.
                </p>

            </div>

        </section>


        <p class="photo-credit">
            Photograph by
            <a href="https://commons.wikimedia.org/wiki/File:Cleaning_a_coffee_table_at_home_with_cleaning_supplies_and_worn_furniture.jpg" target="_blank">Nenad Stojković</a>
            · CC BY 2.0
        </p>


        <section class="history-section">

            <div class="history-header">

                <div>

                    <h2>A history of your belongings</h2>

                    <p class="history-count">
                        <?php echo count($logs); ?>
                        <?php echo count($logs) === 1 ? "record in your history" : "records in your history"; ?>
                    </p>

                </div>

                <a href="add.php" class="record-button">
                    + Record Condition
                </a>

            </div>


            <?php if (count($logs) > 0): ?>

                <div class="history-list">

                    <?php foreach ($logs as $log): ?>

                        <div class="history-row">

                            <div>

                                <div class="history-item">
                                    <?php echo htmlspecialchars($log["item_name"]); ?>
                                </div>

                                <div class="history-home">
                                    <?php echo htmlspecialchars($log["home_name"]); ?>
                                </div>

                            </div>


                            <div>

                                <div class="history-label">RECORDED ON</div>

                                <div class="history-value">
                                    <?php echo htmlspecialchars($log["log_date"]); ?>
                                </div>

                            </div>


                            <div>

                                <div class="history-label">CONDITION</div>

                                <span class="condition-value">
                                    <?php echo htmlspecialchars($log["condition_status"]); ?>
                                </span>

                            </div>


                            <div>

                                <div class="history-label">NOTES</div>

                                <div class="history-note">
                                    <?php echo htmlspecialchars($log["note"] ?? "No notes added"); ?>
                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-message">

                    <h3>Nothing recorded just yet.</h3>

                    <p>
                        Keep an eye on your belongings and record their condition whenever you need to.
                    </p>

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