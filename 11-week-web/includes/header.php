<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? "TaskFlow";
$basePath = $basePath ?? "";
$isLoggedIn = isset($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | TaskFlow</title>
    <link rel="stylesheet" href="<?= $basePath ?>css/style.css">
</head>
<body>

<?php if ($isLoggedIn): ?>

<div class="app-layout">

    <aside class="sidebar">
        <a href="<?= $basePath ?>index.php" class="sidebar-logo">
            TaskFlow<span>.</span>
        </a>

        <div class="sidebar-label">WORKSPACE</div>

        <nav class="sidebar-nav">
            <a href="<?= $basePath ?>index.php" class="sidebar-link">
                <span class="sidebar-icon">⌂</span>
                Басты бет
            </a>

            <a href="<?= $basePath ?>tasks.php" class="sidebar-link active">
                <span class="sidebar-icon">▤</span>
                Менің тапсырмаларым
            </a>
        </nav>

        <div class="sidebar-label">ACCOUNT</div>

        <nav class="sidebar-nav">
            <a href="<?= $basePath ?>logout.php" class="sidebar-link logout-link">
                <span class="sidebar-icon">↪</span>
                Жүйеден шығу
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-avatar">
                <?= mb_strtoupper(mb_substr($_SESSION["user_name"] ?? "U", 0, 1)) ?>
            </div>
            <div class="sidebar-user">
                <strong><?= htmlspecialchars($_SESSION["user_name"] ?? "Пайдаланушы") ?></strong>
                <span>Жеке аккаунт</span>
            </div>
        </div>
    </aside>

    <div class="main-area">

        <header class="topbar">
            <div>
                <span class="topbar-caption">TASK MANAGEMENT SYSTEM</span>
                <h2><?= htmlspecialchars($pageTitle) ?></h2>
            </div>

            <div class="topbar-user">
                <span class="topbar-avatar">
                    <?= mb_strtoupper(mb_substr($_SESSION["user_name"] ?? "U", 0, 1)) ?>
                </span>
                <span><?= htmlspecialchars($_SESSION["user_name"] ?? "Пайдаланушы") ?></span>
            </div>
        </header>

        <main class="page-content">

<?php else: ?>

<header class="public-header">
    <div class="public-header-inner">
        <a href="<?= $basePath ?>index.php" class="sidebar-logo">
            TaskFlow<span>.</span>
        </a>

        <nav class="public-nav">
            <a href="<?= $basePath ?>index.php">Басты бет</a>
            <a href="<?= $basePath ?>login.php">Кіру</a>
            <a href="<?= $basePath ?>register.php" class="public-nav-button">Тіркелу</a>
        </nav>
    </div>
</header>

<main class="public-content">

<?php endif; ?>