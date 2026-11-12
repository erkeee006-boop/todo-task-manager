<?php
require_once "includes/auth.php";
require_once "config/database.php";

$userId = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "add") {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $date = $_POST["date"] ?? "";

        if ($title === "") {
            $_SESSION["message"] = "Тапсырма атауын енгізіңіз.";

        } elseif (mb_strlen($title) > 100) {
            $_SESSION["message"] = "Тапсырма атауы 100 таңбадан аспауы керек.";

        } elseif (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate(
                (int)substr($date, 5, 2),
                (int)substr($date, 8, 2),
                (int)substr($date, 0, 4)
            )
        ) {
            $_SESSION["message"] = "Дұрыс күнді таңдаңыз.";

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO tasks
                (user_id, title, description, task_date, status)
                VALUES (?, ?, ?, ?, 'active')"
            );

            $stmt->execute([$userId, $title, $description, $date]);

            $_SESSION["message"] = "Жаңа тапсырма сәтті қосылды!";
        }
    }

    elseif ($action === "change_status") {

        $id = (int)($_POST["id"] ?? 0);
        $newStatus = $_POST["status"] ?? "";

        $allowedStatuses = ["active", "completed", "reserve"];

        if (in_array($newStatus, $allowedStatuses, true)) {

            $stmt = $pdo->prepare(
                "UPDATE tasks SET status = ?
                 WHERE id = ? AND user_id = ?"
            );

            $stmt->execute([$newStatus, $id, $userId]);

            $_SESSION["message"] = "Тапсырма мәртебесі өзгертілді!";
        }
    }

    elseif ($action === "delete") {

        $id = (int)($_POST["id"] ?? 0);

        $stmt = $pdo->prepare(
            "DELETE FROM tasks WHERE id = ? AND user_id = ?"
        );

        $stmt->execute([$id, $userId]);

        $_SESSION["message"] = "Тапсырма жойылды!";
    }

    header("Location: tasks.php");
    exit;
}

$status = $_GET["status"] ?? "all";
$search = trim($_GET["search"] ?? "");

$allowedStatuses = ["all", "active", "completed", "reserve"];

if (!in_array($status, $allowedStatuses, true)) {
    $status = "all";
}

$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$userId];

if ($status !== "all") {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

$sql .= " ORDER BY task_date ASC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filteredTasks = $stmt->fetchAll();

$countStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'active') AS active_count,
        SUM(status = 'completed') AS completed_count,
        SUM(status = 'reserve') AS reserve_count
     FROM tasks
     WHERE user_id = ?"
);

$countStmt->execute([$userId]);
$counts = $countStmt->fetch();

$totalCount = (int)($counts["total"] ?? 0);
$activeCount = (int)($counts["active_count"] ?? 0);
$completedCount = (int)($counts["completed_count"] ?? 0);
$reserveCount = (int)($counts["reserve_count"] ?? 0);

$pageTitle = "Тапсырмалар";

include "includes/header.php";

$message = $_SESSION["message"] ?? "";
unset($_SESSION["message"]);

?>

<div class="dashboard-heading">
    <div>
        <span class="dashboard-eyebrow">YOUR WORKSPACE</span>
        <h1>Тапсырмалар тақтасы</h1>
        <p>
            Сәлем, <?= htmlspecialchars($_SESSION["user_name"] ?? "Пайдаланушы") ?>!
            Бүгінгі жұмыстарыңды жоспарла.
        </p>
    </div>

    <a href="#add-task" class="add-main-button">
        + Жаңа тапсырма
    </a>
</div>

<?php if ($message !== ""): ?>
    <div class="dashboard-message">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="stats-grid">

    <div class="dashboard-stat">
        <div class="stat-top">
            <span>Барлық тапсырма</span>
            <span class="stat-icon icon-total">▤</span>
        </div>
        <strong><?= $totalCount ?></strong>
        <small>Жалпы тапсырмалар саны</small>
    </div>

    <div class="dashboard-stat">
        <div class="stat-top">
            <span>Орындалмаған</span>
            <span class="stat-icon icon-active">◷</span>
        </div>
        <strong><?= $activeCount ?></strong>
        <small>Орындалуы күтілуде</small>
    </div>

    <div class="dashboard-stat">
        <div class="stat-top">
            <span>Орындалған</span>
            <span class="stat-icon icon-completed">✓</span>
        </div>
        <strong><?= $completedCount ?></strong>
        <small>Сәтті аяқталған</small>
    </div>

    <div class="dashboard-stat">
        <div class="stat-top">
            <span>Резерв</span>
            <span class="stat-icon icon-reserve">◇</span>
        </div>
        <strong><?= $reserveCount ?></strong>
        <small>Кейінге қалдырылған</small>
    </div>

</div>

<div class="dashboard-columns">

    <section class="content-panel task-panel">

        <div class="panel-heading">
            <div>
                <span class="panel-overline">TASK OVERVIEW</span>
                <h2>Менің тапсырмаларым</h2>
            </div>
            <span class="task-total"><?= count($filteredTasks) ?> тапсырма</span>
        </div>

        <div class="task-filters">
            <a href="tasks.php?status=all"
               class="<?= $status === "all" ? "selected" : "" ?>">
                Барлығы
            </a>

            <a href="tasks.php?status=active"
               class="<?= $status === "active" ? "selected" : "" ?>">
                Орындалмаған
            </a>

            <a href="tasks.php?status=completed"
               class="<?= $status === "completed" ? "selected" : "" ?>">
                Орындалған
            </a>

            <a href="tasks.php?status=reserve"
               class="<?= $status === "reserve" ? "selected" : "" ?>">
                Резерв
            </a>
        </div>

        <form method="GET" action="tasks.php" class="dashboard-search">
            <input type="hidden" name="status"
                   value="<?= htmlspecialchars($status) ?>">

            <input
                type="search"
                name="search"
                placeholder="Тапсырма атауы бойынша іздеу..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <button type="submit">Іздеу</button>
        </form>

        <?php if (count($filteredTasks) > 0): ?>

            <div class="table-wrapper">
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>ТАПСЫРМА</th>
                            <th>ОРЫНДАУ КҮНІ</th>
                            <th>СТАТУС</th>
                            <th>ӘРЕКЕТТЕР</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($filteredTasks as $task): ?>

                        <tr>
                            <td>
                                <div class="table-task-title">
                                    <?= htmlspecialchars($task["title"]) ?>
                                </div>

                                <div class="table-task-description">
                                    <?= htmlspecialchars($task["description"] ?? "") ?>
                                </div>
                            </td>

                            <td>
                                <?= date("d.m.Y", strtotime($task["task_date"])) ?>
                            </td>

                            <td>
                                <?php if ($task["status"] === "active"): ?>
                                    <span class="status-badge badge-active">
                                        Орындалмаған
                                    </span>
                                <?php elseif ($task["status"] === "completed"): ?>
                                    <span class="status-badge badge-completed">
                                        Орындалды
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge badge-reserve">
                                        Резерв
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="table-actions">

                                    <?php if ($task["status"] !== "completed"): ?>
                                        <form method="POST" action="tasks.php">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="id" value="<?= (int)$task["id"] ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button class="table-action complete-action"
                                                    title="Орындалды" type="submit">✓</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="tasks.php">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="id" value="<?= (int)$task["id"] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button class="table-action undo-action"
                                                    title="Қайтару" type="submit">↩</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($task["status"] !== "reserve"): ?>
                                        <form method="POST" action="tasks.php">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="id" value="<?= (int)$task["id"] ?>">
                                            <input type="hidden" name="status" value="reserve">
                                            <button class="table-action reserve-action"
                                                    title="Резервке қою" type="submit">◇</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="tasks.php">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="id" value="<?= (int)$task["id"] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button class="table-action undo-action"
                                                    title="Қайтару" type="submit">↩</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" action="tasks.php"
                                          onsubmit="return confirm('Бұл тапсырманы жоясыз ба?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$task["id"] ?>">
                                        <button class="table-action delete-action"
                                                title="Жою" type="submit">×</button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-dashboard">
                <div class="empty-symbol">▤</div>
                <h3>Тапсырмалар табылмады</h3>
                <p>Іздеу сөзін немесе сүзгіні өзгертіп көріңіз.</p>
            </div>

        <?php endif; ?>

    </section>

    <aside class="right-column">

        <section class="content-panel add-task-panel" id="add-task">

            <span class="panel-overline">QUICK ACTION</span>
            <h2>Жаңа тапсырма</h2>
            <p class="panel-description">
                Жоспарыңа жаңа тапсырма қос.
            </p>

            <form method="POST" action="tasks.php" class="new-task-form">

                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label>Тапсырма атауы</label>
                    <input
                        type="text"
                        name="title"
                        maxlength="100"
                        placeholder="Мысалы: PHP оқу"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Сипаттамасы</label>
                    <textarea
                        name="description"
                        placeholder="Қысқаша сипаттама..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label>Орындау күні</label>
                    <input
                        type="date"
                        name="date"
                        value="<?= date("Y-m-d") ?>"
                        required
                    >
                </div>

                <button type="submit" class="add-submit-button">
                    + Тапсырманы сақтау
                </button>

            </form>

        </section>

        <section class="content-panel progress-panel">

            <span class="panel-overline">YOUR PROGRESS</span>
            <h2>Жалпы нәтиже</h2>

            <?php
            $progress = $totalCount > 0
                ? round(($completedCount / $totalCount) * 100)
                : 0;
            ?>

            <div class="progress-number"><?= $progress ?>%</div>

            <div class="progress-track">
                <div class="progress-fill"
                     style="width: <?= $progress ?>%"></div>
            </div>

            <p>
                <?= $completedCount ?> тапсырма орындалды,
                <?= $activeCount + $reserveCount ?> тапсырма қалды.
            </p>

        </section>

    </aside>

</div>

<?php include "includes/footer.php"; ?>