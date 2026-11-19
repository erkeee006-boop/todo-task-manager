<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Қош келдіңіз";
$basePath = "";
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow | Қош келдіңіз</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="welcome-page">
    <div class="welcome-box">

        <div class="welcome-brand">TaskFlow<span>.</span></div>

        <div class="welcome-symbol">✓</div>

        <span class="welcome-small-title">TASK MANAGEMENT SYSTEM</span>

        <h1>Жоспарла. Орында. Жетістікке жет.</h1>

        <p>
            TaskFlow — күнделікті тапсырмаларыңызды басқаруға,
            уақытты тиімді пайдалануға және мақсаттарыңызға
            жетуге көмектесетін жүйе.
        </p>

        <div class="welcome-page-actions">
            <a href="register.php" class="welcome-register">
                Тіркелу
            </a>

            <a href="login.php" class="welcome-login">
                Жүйеге кіру
            </a>
        </div>

        <div class="welcome-bottom-text">
            © 2026 TaskFlow. Барлық құқықтар қорғалған.
        </div>

    </div>
</div>

</body>
</html>