<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? "TaskFlow";
$basePath = $basePath ?? "";

$isLoggedIn = isset($_SESSION["user_id"]);

$userName = $_SESSION["user_name"]
    ?? $_SESSION["full_name"]
    ?? "Пайдаланушы";

$userInitial = mb_strtoupper(
    mb_substr($userName, 0, 1)
);

$currentPage = basename($_SERVER["SCRIPT_NAME"]);

function headerEscape($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="kk">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= headerEscape($pageTitle) ?> | TaskFlow
    </title>

    <link
        rel="stylesheet"
        href="<?= headerEscape($basePath) ?>css/style.css"
    >

    <link
        rel="stylesheet"
        href="<?= headerEscape($basePath) ?>css/week12.css?v=1"
    >
</head>

<body>

<?php if ($isLoggedIn): ?>

<div class="app-layout">

    <aside class="sidebar">

        <a href="<?= $basePath ?>index.php"
           class="sidebar-logo">
            TaskFlow<span>.</span>
        </a>

        <div class="sidebar-label">
            WORKSPACE
        </div>

        <nav class="sidebar-nav">

            <a
                href="<?= $basePath ?>index.php"
                class="sidebar-link <?= $currentPage === "index.php" ? "active" : "" ?>"
            >
                <span class="sidebar-icon">⌂</span>
                Басты бет
            </a>

            <a
                href="<?= $basePath ?>tasks.php"
                class="sidebar-link <?= $currentPage === "tasks.php" ? "active" : "" ?>"
            >
                <span class="sidebar-icon">▤</span>
                Менің тапсырмаларым
            </a>

        </nav>

        <div class="sidebar-label">
            ACCOUNT
        </div>

        <nav class="sidebar-nav">

            <a
                href="<?= $basePath ?>logout.php"
                class="sidebar-link logout-link"
            >
                <span class="sidebar-icon">↪</span>
                Жүйеден шығу
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="sidebar-avatar">
                <?= headerEscape($userInitial) ?>
            </div>

            <div class="sidebar-user">

                <strong>
                    <?= headerEscape($userName) ?>
                </strong>

                <span>Жеке аккаунт</span>

            </div>

        </div>

    </aside>

    <div class="main-area">

        <header class="topbar">

            <div>
                <span class="topbar-caption">
                    TASK MANAGEMENT SYSTEM
                </span>

                <h2>
                    <?= headerEscape($pageTitle) ?>
                </h2>
            </div>

            <div class="topbar-user">

                <span class="topbar-avatar">
                    <?= headerEscape($userInitial) ?>
                </span>

                <span>
                    <?= headerEscape($userName) ?>
                </span>

            </div>

        </header>

        <main class="page-content">

<?php else: ?>

<header class="public-header">

    <div class="public-header-inner">

        <a
            href="<?= $basePath ?>welcome.php"
            class="sidebar-logo"
        >
            TaskFlow<span>.</span>
        </a>

        <nav class="public-nav">

            <a href="<?= $basePath ?>welcome.php">
                Басты бет
            </a>

            <a href="<?= $basePath ?>login.php">
                Жүйеге кіру
            </a>

            <a
                href="<?= $basePath ?>register.php"
                class="public-nav-button"
            >
                Тіркелу
            </a>

        </nav>

    </div>

</header>

<main class="public-content">

<?php endif; ?>