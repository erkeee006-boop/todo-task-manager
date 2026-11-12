<?php
session_start();

$pageTitle = "Тіркелу";

$errors = $_SESSION["errors"] ?? [];
$success = $_SESSION["success"] ?? "";
$old = $_SESSION["old"] ?? [];

unset($_SESSION["errors"]);
unset($_SESSION["success"]);
unset($_SESSION["old"]);

include "includes/header.php";
?>

<section class="auth-section">
    <div class="auth-card">

        <div class="auth-heading">
            <span class="hero-label">CREATE ACCOUNT</span>
            <h1>Тіркелу</h1>
            <p>TaskFlow жүйесіне тіркеліп, тапсырмаларыңызды басқарыңыз.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Қателерді көрсету -->
        <?php foreach ($errors as $error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endforeach; ?>

        <!-- Тіркелу формасы -->
        <form action="process/register.php" method="POST">

            <div class="form-group">
                <label>Аты-жөніңіз</label>
                <input
                    type="text"
                    name="full_name"
                    placeholder="Аты-жөніңізді енгізіңіз"
                    value="<?= htmlspecialchars($old["full_name"] ?? "") ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Электрондық пошта</label>
                <input
                    type="email"
                    name="email"
                    placeholder="example@gmail.com"
                    value="<?= htmlspecialchars($old["email"] ?? "") ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Телефон нөмірі</label>
                <input
                    type="tel"
                    name="phone"
                    placeholder="+7 700 000 00 00"
                    value="<?= htmlspecialchars($old["phone"] ?? "") ?>"
                >
            </div>

            <div class="form-group">
                <label>Құпиясөз</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Құпиясөз ойлап табыңыз"
                    required
                >
            </div>

            <div class="form-group">
                <label>Құпиясөзді қайталаңыз</label>
                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Құпиясөзді қайта енгізіңіз"
                    required
                >
            </div>

            <button type="submit" class="btn-primary form-button">
                Тіркелу
            </button>

        </form>

        <p class="auth-bottom">
            Аккаунтыңыз бар ма?
            <a href="login.php">Жүйеге кіру</a>
        </p>

    </div>
</section>

<?php include "includes/footer.php"; ?>