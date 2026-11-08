<?php

session_start();

require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM homes WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_homes = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM items
    JOIN homes ON items.home_id = homes.home_id
    WHERE homes.user_id = ?
");
$stmt->execute([$user_id]);
$total_items = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(items.insured_value), 0)
    FROM items
    JOIN homes ON items.home_id = homes.home_id
    WHERE homes.user_id = ?
");
$stmt->execute([$user_id]);
$total_insured = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM items
    JOIN homes ON items.home_id = homes.home_id
    WHERE homes.user_id = ?
    AND items.warranty_end >= CURDATE()
");
$stmt->execute([$user_id]);
$active_warranties = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>reloka - Overview</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            background: #F5F3EC;
        }

        .sidebar {
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .main-content {
            max-width: 1500px;
            padding: 0 60px;
            background: #F5F3EC;
            position: relative;
        }

        .hero {
            min-height: 390px;
            margin: 35px 0 50px;
            padding: 45px;
            display: flex;
            align-items: flex-end;
            background:
                linear-gradient(
                    rgba(25, 38, 30, 0.55),
                    rgba(25, 38, 30, 0.55)
                ),
                url("assets/images/dashboard-moving-truck.jpg") center center / cover no-repeat;
        }

        .hero-text {
            max-width: 650px;
            color: #FFFFFF;
        }

        .hero-label {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 18px;
            color: #E8C4AE;
        }

        .hero h1 {
            font-family: Georgia, serif;
            font-size: 56px;
            font-weight: bold;
            line-height: 1.2;
        }

        .hero-description {
            font-size: 16px;
            font-weight: 500;
            line-height: 1.8;
            color: #F5F3EC;
            margin-top: 15px;
            max-width: 480px;
        }

        .overview-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .overview-heading h2 {
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: bold;
            color: #263B32;
        }

        .overview-heading span {
            font-size: 10px;
            letter-spacing: 1.5px;
            color: #777C73;
        }

        .overview-area {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 65px;
            padding: 25px 0 40px;
            border-top: 1px solid #DDDCD3;
            border-bottom: 1px solid #DDDCD3;
        }

        .main-count {
            padding: 10px 0;
        }

        .main-count-label {
            font-size: 10px;
            letter-spacing: 1.5px;
            color: #777C73;
        }

        .main-count-number {
            font-family: Georgia, serif;
            font-size: 105px;
            font-weight: normal;
            line-height: 1.2;
            color: #263B32;
            margin-top: 15px;
        }

        .main-count-description {
            font-family: Georgia, serif;
            font-size: 19px;
            color: #777C73;
            margin-top: 5px;
        }

        .summary {
            border-left: 1px solid #DDDCD3;
            padding-left: 40px;
        }

        .summary-heading {
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: normal;
            color: #263B32;
            margin-bottom: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 19px 0;
            border-bottom: 1px solid #DDDCD3;
            gap: 15px;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row span {
            font-size: 15px;
            font-weight: 500;
            color: #263B32;
        }

        .summary-row strong {
            font-family: Georgia, serif;
            font-size: 23px;
            font-weight: normal;
            color: #263B32;
        }

        .summary-row .money {
            font-size: 15px;
        }

        .bottom-note {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 35px 0;
        }

        .bottom-note p {
            font-family: Georgia, serif;
            font-size: 20px;
            color: #263B32;
        }

        .bottom-note a {
            font-size: 12px;
            color: #A66E54;
            border-bottom: 1px solid #A66E54;
            padding-bottom: 4px;
        }

        @media (max-width: 850px) {

            .main-content {
                padding: 0 30px;
            }

            .hero {
                min-height: 330px;
                padding: 30px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .overview-area {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .summary {
                border-left: none;
                padding-left: 0;
            }

            .main-count-number {
                font-size: 85px;
            }

        }

        @media (max-width: 700px) {

            .dashboard-layout {
                flex-direction: column;
            }

            .main-content {
                padding: 0 20px;
            }

            .hero {
                min-height: 320px;
                padding: 22px;
                margin-top: 25px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .overview-heading {
                align-items: flex-start;
                gap: 10px;
                flex-direction: column;
            }

            .bottom-note {
                align-items: flex-start;
                gap: 20px;
                flex-direction: column;
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

        <p class="sidebar-label">MENU</p>

        <nav class="sidebar-nav">

            <a href="dashboard.php" class="nav-link active">Overview</a>
            <a href="homes/index.php" class="nav-link">My Homes</a>
            <a href="items/index.php" class="nav-link">My Items</a>
            <a href="condition_logs/index.php" class="nav-link">Condition History</a>
            <a href="insurance/index.php" class="nav-link">Insurance</a>
            <a href="warranty/index.php" class="nav-link">Warranty</a>

        </nav>

        <div class="sidebar-bottom">

            <p>YOUR PERSONAL HOME RECORD</p>

            <a href="auth/logout.php" class="logout-link">Log Out</a>

        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">

            <span>YOUR OVERVIEW</span>

            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </span>

        </header>

        <section class="hero">

            <div class="hero-text">

                <p class="hero-label">WELCOME TO RELOKA</p>

                <h1>A home is more than four walls.</h1>

                <p class="hero-description">
                    Keep track of the places you live, the things you own,
                    and the little details worth remembering.
                </p>

            </div>

        </section>

        <section>

            <div class="overview-heading">

                <h2>Your household at a glance.</h2>

                <span>THE HOUSEHOLD REGISTER / 01</span>

            </div>

            <div class="overview-area">

                <div class="main-count">

                    <p class="main-count-label">THINGS YOU'VE KEPT TRACK OF</p>

                    <h2 class="main-count-number">
                        <?php echo str_pad($total_items, 2, "0", STR_PAD_LEFT); ?>
                    </h2>

                    <p class="main-count-description">
                        Belongings in your inventory
                    </p>

                </div>

                <div class="summary">

                    <h2 class="summary-heading">A few details</h2>

                    <div class="summary-row">
                        <span>Your homes</span>
                        <strong><?php echo $total_homes; ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Active warranties</span>
                        <strong><?php echo $active_warranties; ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Value you've protected</span>
                        <strong class="money">
                            PKR <?php echo number_format($total_insured, 0); ?>
                        </strong>
                    </div>

                </div>

            </div>

        </section>

        <section class="bottom-note">

            <p>It's the little things that make a place yours.</p>

            <a href="items/index.php">EXPLORE YOUR BELONGINGS &nbsp; →</a>

        </section>

        <footer class="dashboard-footer">
            RELOKA &nbsp; · &nbsp; A RECORD OF YOUR HOME
        </footer>

    </main>

</div>

</body>
</html>