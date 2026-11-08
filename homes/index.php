<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT * FROM homes WHERE user_id = ?"
);

$stmt->execute([$user_id]);

$homes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Homes | Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .main-content {
            max-width: 1500px;
            padding: 0 60px;
        }

        .homes-hero {
            height: 300px;
            position: relative;
            overflow: hidden;
            margin: 35px 0 45px;
        }

        .homes-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .homes-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to right,
                rgba(25, 39, 30, 0.75),
                rgba(25, 39, 30, 0.05)
            );
        }

        .hero-text {
            position: absolute;
            z-index: 1;
            left: 40px;
            bottom: 38px;
            color: white;
            max-width: 560px;
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

        .homes-section {
            margin-bottom: 60px;
        }

        .homes-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .homes-heading h2 {
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            color: #263B32;
        }

        .homes-heading p {
            font-size: 15px;
            color: #5F645B;
            margin-top: 8px;
        }

        .homes-list {
            border-top: 1px solid #DDDCD3;
        }

        .home-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 12px;
            border-bottom: 1px solid #DDDCD3;
        }

        .home-info h3 {
            font-family: Georgia, serif;
            font-size: 24px;
            font-weight: bold;
            color: #263B32;
        }

        .home-info p {
            font-size: 15px;
            color: #5F645B;
            margin-top: 8px;
        }

        .home-actions {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .home-actions a,
        .home-actions button {
            background: none;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .home-actions a {
            color: #263B32;
            text-decoration: none;
        }

        .home-actions button {
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

        @media (max-width: 850px) {

            .main-content {
                padding: 0 30px;
            }

            .homes-hero {
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

            .homes-hero {
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

            .homes-heading {
                align-items: flex-start;
                gap: 20px;
                flex-direction: column;
            }

            .home-row {
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

        <p class="sidebar-label">WORKSPACE</p>

        <nav class="sidebar-nav">

            <a href="../dashboard.php" class="nav-link">
                Overview
            </a>

            <a href="index.php" class="nav-link active">
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


        <section class="homes-hero">

            <img
                src="../assets/images/homes-house.jpg"
                alt="A modern family home surrounded by greenery"
            >

            <div class="hero-text">

                <p class="hero-label">YOUR SPACE / HOMES</p>

                <h1>The places you call home.</h1>

                <p>
                    Every home has its own story.
                    Keep the details of yours together in one place.
                </p>

            </div>

        </section>


        <section class="homes-section">

            <div class="homes-heading">

                <div>

                    <h2>Your registered homes</h2>

                    <p>
                        <?php echo count($homes); ?>
                        <?php echo count($homes) === 1 ? "home in your register" : "homes in your register"; ?>
                    </p>

                </div>

                <a href="add.php" class="primary-button">
                    + Add Home
                </a>

            </div>


            <?php if (count($homes) > 0): ?>

                <div class="homes-list">

                    <?php foreach ($homes as $home): ?>

                        <div class="home-row">

                            <div class="home-info">

                                <h3>
                                    <?php echo htmlspecialchars($home["home_name"]); ?>
                                </h3>

                                <p>
                                    <?php echo htmlspecialchars($home["address"] ?? "No address provided"); ?>
                                </p>

                            </div>


                            <div class="home-actions">

                                <a href="edit.php?id=<?php echo $home['home_id']; ?>">
                                    Edit
                                </a>

                                <form action="delete.php" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this home?');">

                                    <input
                                        type="hidden"
                                        name="home_id"
                                        value="<?php echo $home['home_id']; ?>"
                                    >

                                    <button type="submit">
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <h3>No homes registered just yet.</h3>

                    <p>
                        Start with the place you call home.
                        You can add more spaces whenever you need.
                    </p>

                    <a href="add.php" class="primary-button">
                        + Add Your First Home
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