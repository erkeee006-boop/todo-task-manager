<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    require_once "config/database.php";

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["full_name"];

        header("Location: tasks.php");
        exit;

    } else {

        $_SESSION["errors"] = [
            "Электрондық пошта немесе құпиясөз қате."
        ];

        header("Location: login.php");
        exit;
    }
}

$pageTitle = "Жүйеге кіру";

$errors = $_SESSION["errors"] ?? [];
$success = $_SESSION["success"] ?? "";

unset($_SESSION["errors"], $_SESSION["success"]);

include "includes/header.php";

?>

<section class="auth-section">

    <div class="auth-card">

        <div class="auth-heading">
            <span class="hero-label">WELCOME BACK</span>
            <h1>Жүйеге кіру</h1>
            <p>Аккаунтыңызға кіріп, тапсырмаларыңызды басқарыңыз.</p>
        </div>

        <?php foreach ($errors as $error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endforeach; ?>

        <?php if ($success !== ""): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">

            <div class="form-group">
                <label>Электрондық пошта</label>
                <input type="email" name="email"
                       placeholder="example@gmail.com" required>
            </div>

            <div class="form-group">
                <label>Құпиясөз</label>
                <input type="password" name="password"
                       placeholder="Құпиясөзіңізді енгізіңіз" required>
            </div>

            <button type="submit" class="btn-primary form-button">
                Кіру
            </button>

        </form>

        <p class="auth-bottom">
            Аккаунтыңыз жоқ па?
            <a href="register.php">Тіркелу</a>
        </p>

    </div>

</section>

<?php include "includes/footer.php"; ?>