<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Check login

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$home_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$home_id) {
    die("Invalid home ID.");
}

// Fetch selected home

$stmt = $pdo->prepare(
    "SELECT * FROM homes
     WHERE home_id = ? AND user_id = ?"
);

$stmt->execute([$home_id, $user_id]);

$home = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$home) {
    die("Home not found.");
}

$error = "";

// Handle form submission

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $home_name = trim($_POST["home_name"]);
    $address = trim($_POST["address"]);

    if (empty($home_name)) {

        $error = "Home name is required.";

    } elseif (strlen($home_name) > 100 || strlen($address) > 200) {

        $error = "One or more fields exceed the allowed length.";

    } else {

        $stmt = $pdo->prepare(
            "UPDATE homes
             SET home_name = ?, address = ?
             WHERE home_id = ? AND user_id = ?"
        );

        $stmt->execute([
            $home_name,
            $address,
            $home_id,
            $user_id
        ]);

        header("Location: index.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Home | Reloka</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .form-container {
            max-width: 550px;
        }

        .home-form label {
            display: block;
            font-size: 13px;
            color: #263B32;
            margin-bottom: 8px;
        }

        .home-form input {
            width: 100%;
            padding: 13px;
            border: 1px solid #DDDCD3;
            background: #FFFFFF;
            font-size: 13px;
            margin-bottom: 25px;
            outline: none;
        }

        .home-form input:focus {
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

            <a href="index.php" class="nav-link active">My Homes</a>

            <a href="../items/index.php" class="nav-link">My Items</a>

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

            <p class="eyebrow">YOUR SPACE / HOMES / EDIT</p>

            <h1>Edit your home.</h1>

            <p>Update the details of your living space.</p>

        </section>


        <section class="form-container">

            <?php if (!empty($error)): ?>

                <p class="form-error">
                    <?php echo htmlspecialchars($error); ?>
                </p>

            <?php endif; ?>


            <form method="POST" class="home-form">

                <label for="home_name">Home Name</label>

                <input
                    type="text"
                    id="home_name"
                    name="home_name"
                    value="<?php echo htmlspecialchars($_POST["home_name"] ?? $home["home_name"]); ?>"
                    maxlength="100"
                    required
                >


                <label for="address">Address</label>

                <input
                    type="text"
                    id="address"
                    name="address"
                    value="<?php echo htmlspecialchars($_POST["address"] ?? $home["address"] ?? ""); ?>"
                    maxlength="200"
                >


                <div class="form-links">

                    <button type="submit" class="primary-button">
                        Update Home
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