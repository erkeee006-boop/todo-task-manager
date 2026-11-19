<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Аккаунтқа кірмеген болса, welcome.php бетіне өтеді
if (!isset($_SESSION["user_id"])) {
    header("Location: welcome.php");
    exit;
}

require_once "config/database.php";

$pageTitle = "Басты бет";
$basePath = "";

$userId = $_SESSION["user_id"];

// Пайдаланушы ақпаратын алу
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$fullName = $user["full_name"] ?? "Қолданушы";

// Тапсырмалар статистикасы
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'active') AS active,
        SUM(status = 'completed') AS completed,
        SUM(status = 'reserve') AS reserve
    FROM tasks
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total = $stats["total"] ?? 0;
$active = $stats["active"] ?? 0;
$completed = $stats["completed"] ?? 0;
$reserve = $stats["reserve"] ?? 0;

require_once "includes/header.php";
?>

<div class="home-dashboard">

    <div class="home-heading">
        <div>
            <span class="home-eyebrow">ЖЕКЕ КАБИНЕТ</span>
            <h1>Басты бет</h1>
            <p>Тапсырмаларыңызды бір жерден басқарыңыз.</p>
        </div>
        <div class="home-date">
            <?= date("d.m.Y") ?>
        </div>
    </div>

    <section class="welcome-section">
        <div class="welcome-content">
            <span class="welcome-label">TASKFLOW WORKSPACE</span>
            <h2>Қош келдіңіз, <?= htmlspecialchars($fullName) ?>!</h2>
            <p>
                Бүгінгі жоспарларыңызды реттеп, маңызды тапсырмаларды
                уақытында орындаңыз.
            </p>
            <a href="tasks.php" class="welcome-action">
                Тапсырмаларға өту →
            </a>
        </div>

        <div class="welcome-visual">
            <div class="visual-circle">
                <span>✓</span>
            </div>
        </div>
    </section>

    <div class="section-heading">
        <div>
            <h2>Жалпы көрсеткіштер</h2>
            <p>Тапсырмаларыңыздың қазіргі жағдайы</p>
        </div>
    </div>

    <div class="home-stats-grid">

        <div class="home-stat-card">
            <div class="home-stat-top">
                <span>Барлық тапсырма</span>
                <div class="stat-icon">▤</div>
            </div>
            <strong><?= $total ?></strong>
            <small>Жалпы тапсырмалар саны</small>
        </div>

        <div class="home-stat-card">
            <div class="home-stat-top">
                <span>Орындалуда</span>
                <div class="stat-icon">◷</div>
            </div>
            <strong><?= $active ?></strong>
            <small>Белсенді тапсырмалар</small>
        </div>

        <div class="home-stat-card">
            <div class="home-stat-top">
                <span>Орындалды</span>
                <div class="stat-icon">✓</div>
            </div>
            <strong><?= $completed ?></strong>
            <small>Аяқталған тапсырмалар</small>
        </div>

        <div class="home-stat-card">
            <div class="home-stat-top">
                <span>Резерв</span>
                <div class="stat-icon">☆</div>
            </div>
            <strong><?= $reserve ?></strong>
            <small>Кейінге қалдырылған</small>
        </div>

    </div>

    <div class="home-bottom-grid">

        <div class="home-info-card">
            <div class="info-card-icon">✦</div>
            <h3>Уақытыңызды тиімді пайдаланыңыз</h3>
            <p>
                Тапсырмаларды жоспарлап, олардың орындалу барысын бақылаңыз.
            </p>
        </div>

        <div class="home-info-card">
            <div class="info-card-icon">↗</div>
            <h3>Мақсаттарыңызға жақындаңыз</h3>
            <p>
                Әрбір орындалған тапсырма сізді алға жетелейді.
            </p>
        </div>

    </div>

</div>

<?php require_once "includes/footer.php"; ?>