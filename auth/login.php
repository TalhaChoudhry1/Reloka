<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION["user_id"])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["name"];

        header("Location: ../dashboard.php");
        exit;

    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reloka - Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F5F3EC;
            font-family: Arial, sans-serif;
            color: #292C29;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 40px;
            border: 1px solid #E5E3DB;
        }

        .brand {
            color: #263B32;
            font-family: Georgia, serif;
            font-size: 30px;
            margin-bottom: 35px;
        }

        h1 {
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #777;
            font-size: 14px;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-size: 14px;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #DADAD3;
            font-size: 14px;
            margin-bottom: 22px;
            outline: none;
        }

        input:focus {
            border-color: #899783;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #263B32;
            color: white;
            border: none;
            font-size: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #354E42;
        }

        .error {
            background: #FCEDEA;
            color: #A34435;
            padding: 12px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .register-link {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 25px;
        }

        .register-link a {
            color: #263B32;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <div class="brand">Reloka.</div>

        <h1>Welcome back</h1>
        <p class="subtitle">Log in to manage your home inventory.</p>

        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Email Address</label>
            <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your email"
                required
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit">Login</button>

        </form>

        <p class="register-link">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </div>

</body>
</html>