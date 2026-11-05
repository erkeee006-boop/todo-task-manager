<?php
$pageTitle = $pageTitle ?? 'TaskFlow';
$basePath = $basePath ?? '';
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

<header class="header">
    <div class="header-container">
        <a href="<?= $basePath ?>index.php" class="logo">TaskFlow<span>.</span></a>

        <nav class="navigation">
            <a href="<?= $basePath ?>index.php">Басты бет</a>
            <a href="<?= $basePath ?>tasks.php">Тапсырмалар</a>
            <a href="<?= $basePath ?>login.php" class="nav-button">Тіркелу</a>
        </nav>
    </div>
</header>

<main class="main-container">