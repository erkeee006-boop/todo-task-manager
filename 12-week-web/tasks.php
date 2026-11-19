<?php
require_once "includes/auth.php";
require_once "config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = (int)($_SESSION["user_id"] ?? 0);

if ($userId <= 0) {
    header("Location: welcome.php");
    exit;
}

$pageTitle = "Менің тапсырмаларым";
$basePath = "";

function w12e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function w12ValidDate($date) {
    if (!is_string($date) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode("-", $date);

    return checkdate(
        (int)$parts[1],
        (int)$parts[2],
        (int)$parts[0]
    );
}

function w12Message($message, $type = "success") {
    $_SESSION["w12_message"] = [
        "text" => $message,
        "type" => $type
    ];
}

function w12Redirect($url = "tasks.php") {
    header("Location: " . $url);
    exit;
}

if (empty($_SESSION["w12_csrf"])) {
    $_SESSION["w12_csrf"] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION["w12_csrf"];

$statuses = [
    "active" => "Орындалмаған",
    "completed" => "Орындалды",
    "reserve" => "Резерв"
];

// ========================================
// CREATE / UPDATE / DELETE / CHANGE STATUS
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) ||
        !hash_equals($csrf, $token)) {
        http_response_code(403);
        exit("Қауіпсіздік қатесі. Бетті жаңартыңыз.");
    }

    $action = $_POST["action"] ?? "";
    $id = (int)($_POST["id"] ?? 0);

    try {

        // CREATE
        if ($action === "create") {

            $title = trim($_POST["title"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $taskDate = $_POST["task_date"] ?? "";

            if ($title === "" || mb_strlen($title) > 100) {
                w12Message(
                    "Тапсырма атауы 1–100 таңба болуы керек.",
                    "error"
                );
            } elseif (!w12ValidDate($taskDate)) {
                w12Message("Дұрыс күнді таңдаңыз.", "error");
            } else {

                $stmt = $pdo->prepare("
                    INSERT INTO tasks
                    (user_id, title, description, task_date, status)
                    VALUES (?, ?, ?, ?, 'active')
                ");

                $stmt->execute([
                    $userId,
                    $title,
                    $description,
                    $taskDate
                ]);

                w12Message("Жаңа тапсырма сәтті қосылды!");
            }

            w12Redirect();
        }

        // UPDATE
        if ($action === "update") {

            $title = trim($_POST["title"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $taskDate = $_POST["task_date"] ?? "";
            $status = $_POST["status"] ?? "";

            if ($id <= 0) {
                w12Message("Тапсырма ID дұрыс емес.", "error");
            } elseif ($title === "" || mb_strlen($title) > 100) {
                w12Message(
                    "Тапсырма атауы 1–100 таңба болуы керек.",
                    "error"
                );
            } elseif (!w12ValidDate($taskDate)) {
                w12Message("Күні дұрыс емес.", "error");
            } elseif (!array_key_exists($status, $statuses)) {
                w12Message("Мәртебе дұрыс емес.", "error");
            } else {

                $stmt = $pdo->prepare("
                    UPDATE tasks
                    SET title = ?,
                        description = ?,
                        task_date = ?,
                        status = ?
                    WHERE id = ? AND user_id = ?
                ");

                $stmt->execute([
                    $title,
                    $description,
                    $taskDate,
                    $status,
                    $id,
                    $userId
                ]);

                w12Message("Тапсырма мәліметтері сақталды!");
            }

            w12Redirect();
        }

        // CHANGE STATUS
        if ($action === "change_status") {

            $newStatus = $_POST["status"] ?? "";

            if ($id > 0 && array_key_exists($newStatus, $statuses)) {

                $stmt = $pdo->prepare("
                    UPDATE tasks
                    SET status = ?
                    WHERE id = ? AND user_id = ?
                ");

                $stmt->execute([
                    $newStatus,
                    $id,
                    $userId
                ]);

                w12Message("Тапсырма мәртебесі өзгертілді!");
            } else {
                w12Message("Мәртебені өзгерту мүмкін емес.", "error");
            }

            w12Redirect();
        }

        // DELETE
        if ($action === "delete") {

            if ($id > 0) {

                $stmt = $pdo->prepare("
                    DELETE FROM tasks
                    WHERE id = ? AND user_id = ?
                ");

                $stmt->execute([$id, $userId]);

                if ($stmt->rowCount() > 0) {
                    w12Message("Тапсырма сәтті жойылды!");
                } else {
                    w12Message("Тапсырма табылмады.", "error");
                }

            } else {
                w12Message("Тапсырма ID қате.", "error");
            }

            w12Redirect();
        }

        w12Message("Белгісіз әрекет.", "error");
        w12Redirect();

    } catch (PDOException $e) {
        error_log($e->getMessage());
        w12Message("Дерекқор операциясында қате шықты.", "error");
        w12Redirect();
    }
}

// ========================================
// READ + SEARCH + FILTER
// ========================================

$filter = $_GET["status"] ?? "all";
$search = trim($_GET["search"] ?? "");

$allowedFilters = ["all", "active", "completed", "reserve"];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = "all";
}

$sql = "
    SELECT id, title, description, task_date, status
    FROM tasks
    WHERE user_id = ?
";

$params = [$userId];

if ($filter !== "all") {
    $sql .= " AND status = ?";
    $params[] = $filter;
}

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
}

$sql .= " ORDER BY task_date ASC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================================
// STATISTICS
// ========================================

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'active'), 0) AS active_count,
        COALESCE(SUM(status = 'completed'), 0) AS completed_count,
        COALESCE(SUM(status = 'reserve'), 0) AS reserve_count
    FROM tasks
    WHERE user_id = ?
");

$stmt->execute([$userId]);

$counts = $stmt->fetch(PDO::FETCH_ASSOC);

$totalCount = (int)($counts["total"] ?? 0);
$activeCount = (int)($counts["active_count"] ?? 0);
$completedCount = (int)($counts["completed_count"] ?? 0);
$reserveCount = (int)($counts["reserve_count"] ?? 0);

$progress = $totalCount > 0
    ? round($completedCount / $totalCount * 100)
    : 0;

// ========================================
// EDIT FORM
// ========================================

$editTask = null;

if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];

    if ($editId > 0) {

        $stmt = $pdo->prepare("
            SELECT id, title, description, task_date, status
            FROM tasks
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$editId, $userId]);

        $editTask = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$message = $_SESSION["w12_message"] ?? null;
unset($_SESSION["w12_message"]);

$userName = $_SESSION["user_name"]
    ?? $_SESSION["full_name"]
    ?? "Пайдаланушы";

include "includes/header.php";
?>

<div class="w12">

    <div class="w12-heading">
        <div>
            <span class="w12-eyebrow">TASKFLOW / TASK MANAGEMENT</span>
            <h1>Менің тапсырмаларым</h1>
            <p>
                Сәлем, <?= w12e($userName) ?>!
                Тапсырмаларыңызды жоспарлаңыз және басқарыңыз.
            </p>
        </div>

        <a href="#task-form" class="w12-new-btn">
            + Жаңа тапсырма
        </a>
    </div>

    <?php if ($message): ?>
        <div class="w12-alert <?= $message["type"] === "error" ? "w12-alert-error" : "" ?>">
            <?= w12e($message["text"]) ?>
        </div>
    <?php endif; ?>

    <!-- STATISTICS -->

    <div class="w12-stats">

        <div class="w12-stat">
            <div class="w12-stat-top">
                <span>Барлық тапсырма</span>
                <div class="w12-stat-icon">▤</div>
            </div>
            <strong><?= $totalCount ?></strong>
            <small>Жалпы тапсырмалар</small>
        </div>

        <div class="w12-stat">
            <div class="w12-stat-top">
                <span>Орындалмаған</span>
                <div class="w12-stat-icon w12-orange">◷</div>
            </div>
            <strong><?= $activeCount ?></strong>
            <small>Аяқталмаған жұмыстар</small>
        </div>

        <div class="w12-stat">
            <div class="w12-stat-top">
                <span>Орындалған</span>
                <div class="w12-stat-icon w12-green">✓</div>
            </div>
            <strong><?= $completedCount ?></strong>
            <small>Аяқталған жұмыстар</small>
        </div>

        <div class="w12-stat">
            <div class="w12-stat-top">
                <span>Резерв</span>
                <div class="w12-stat-icon w12-purple">◇</div>
            </div>
            <strong><?= $reserveCount ?></strong>
            <small>Кейінге қалдырылған</small>
        </div>

    </div>

    <div class="w12-layout">

        <!-- LEFT: TASK LIST -->

        <section class="w12-panel w12-list-panel">

            <div class="w12-panel-heading">
                <div>
                    <span class="w12-eyebrow">TASK OVERVIEW</span>
                    <h2>Тапсырмалар тізімі</h2>
                </div>

                <span class="w12-count">
                    <?= count($tasks) ?> тапсырма
                </span>
            </div>

            <!-- SEARCH -->

            <form method="GET" action="tasks.php" class="w12-search">

                <input
                    type="hidden"
                    name="status"
                    value="<?= w12e($filter) ?>"
                >

                <input
                    type="search"
                    name="search"
                    placeholder="Тапсырма атауы немесе сипаттамасы..."
                    value="<?= w12e($search) ?>"
                >

                <button type="submit">Іздеу</button>

                <?php if ($search !== ""): ?>
                    <a href="tasks.php?status=<?= w12e($filter) ?>" class="w12-clear">
                        Тазалау
                    </a>
                <?php endif; ?>

            </form>

            <!-- FILTERS -->

            <div class="w12-filters">

                <?php
                $filterLabels = [
                    "all" => "Барлығы",
                    "active" => "Орындалмаған",
                    "completed" => "Орындалған",
                    "reserve" => "Резерв"
                ];
                ?>

                <?php foreach ($filterLabels as $key => $label): ?>

                    <a
                        href="tasks.php?<?= w12e(http_build_query([
                            "status" => $key,
                            "search" => $search
                        ])) ?>"
                        class="w12-filter <?= $filter === $key ? "selected" : "" ?>"
                    >
                        <?= w12e($label) ?>
                    </a>

                <?php endforeach; ?>

            </div>

            <!-- TASK TABLE -->

            <?php if (count($tasks) > 0): ?>

                <div class="w12-table-scroll">
                    <table class="w12-table">

                        <thead>
                            <tr>
                                <th>ТАПСЫРМА</th>
                                <th>КҮНІ</th>
                                <th>МӘРТЕБЕСІ</th>
                                <th>БАСҚАРУ</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($tasks as $task): ?>

                            <tr>

                                <td>
                                    <div class="w12-task-name">
                                        <?= w12e($task["title"]) ?>
                                    </div>

                                    <div class="w12-task-description">
                                        <?= w12e($task["description"] ?? "") ?>
                                    </div>
                                </td>

                                <td class="w12-date">
                                    <?= date("d.m.Y", strtotime($task["task_date"])) ?>
                                </td>

                                <td>
                                    <span class="w12-status w12-status-<?= w12e($task["status"]) ?>">
                                        <?= w12e($statuses[$task["status"]] ?? "Белгісіз") ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="w12-actions">

                                        <!-- STATUS BUTTONS -->

                                        <?php
                                        $statusButtons = [
                                            "completed" => "✓ Орындалды",
                                            "active" => "↶ Орындалмады",
                                            "reserve" => "◷ Резерв"
                                        ];
                                        ?>

                                        <?php foreach ($statusButtons as $key => $label): ?>

                                            <?php if ($task["status"] !== $key): ?>

                                                <form method="POST" action="tasks.php">

                                                    <input type="hidden"
                                                           name="csrf_token"
                                                           value="<?= w12e($csrf) ?>">

                                                    <input type="hidden"
                                                           name="action"
                                                           value="change_status">

                                                    <input type="hidden"
                                                           name="id"
                                                           value="<?= (int)$task["id"] ?>">

                                                    <input type="hidden"
                                                           name="status"
                                                           value="<?= w12e($key) ?>">

                                                    <button type="submit"
                                                            class="w12-action w12-action-<?= w12e($key) ?>">
                                                        <?= w12e($label) ?>
                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        <?php endforeach; ?>

                                        <!-- EDIT -->

                                        <a
                                            href="tasks.php?edit=<?= (int)$task["id"] ?>#task-form"
                                            class="w12-action w12-edit"
                                        >
                                            Өңдеу
                                        </a>

                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            action="tasks.php"
                                            onsubmit="return confirm('Бұл тапсырманы жоясыз ба?');"
                                        >

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= w12e($csrf) ?>">

                                            <input type="hidden"
                                                   name="action"
                                                   value="delete">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= (int)$task["id"] ?>">

                                            <button type="submit"
                                                    class="w12-action w12-delete">
                                                Жою
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            <?php else: ?>

                <div class="w12-empty">
                    <div class="w12-empty-icon">▤</div>
                    <h3>Тапсырмалар табылмады</h3>
                    <p>Жаңа тапсырма қосыңыз немесе іздеу шартын өзгертіңіз.</p>
                </div>

            <?php endif; ?>

        </section>

        <!-- RIGHT: ADD / EDIT FORM -->

        <aside class="w12-right">

            <section class="w12-panel" id="task-form">

                <span class="w12-eyebrow">QUICK ACTION</span>

                <h2>
                    <?= $editTask ? "Тапсырманы өңдеу" : "Жаңа тапсырма" ?>
                </h2>

                <p class="w12-panel-text">
                    <?= $editTask
                        ? "Тапсырма мәліметтерін өзгертіңіз."
                        : "Жаңа тапсырманы жоспарға қосыңыз." ?>
                </p>

                <form method="POST" action="tasks.php" class="w12-form">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= w12e($csrf) ?>">

                    <input type="hidden"
                           name="action"
                           value="<?= $editTask ? "update" : "create" ?>">

                    <?php if ($editTask): ?>
                        <input type="hidden"
                               name="id"
                               value="<?= (int)$editTask["id"] ?>">
                    <?php endif; ?>

                    <div class="w12-field">
                        <label>Тапсырма атауы</label>

                        <input
                            type="text"
                            name="title"
                            maxlength="100"
                            placeholder="Мысалы: PHP жобасын аяқтау"
                            value="<?= w12e($editTask["title"] ?? "") ?>"
                            required
                        >
                    </div>

                    <div class="w12-field">
                        <label>Сипаттамасы</label>

                        <textarea
                            name="description"
                            rows="4"
                            placeholder="Тапсырма туралы қысқаша..."
                        ><?= w12e($editTask["description"] ?? "") ?></textarea>
                    </div>

                    <div class="w12-field">
                        <label>Орындау күні</label>

                        <input
                            type="date"
                            name="task_date"
                            value="<?= w12e($editTask["task_date"] ?? date("Y-m-d")) ?>"
                            required
                        >
                    </div>

                    <?php if ($editTask): ?>

                        <div class="w12-field">
                            <label>Мәртебесі</label>

                            <select name="status">

                                <?php foreach ($statuses as $key => $label): ?>

                                    <option
                                        value="<?= w12e($key) ?>"
                                        <?= $editTask["status"] === $key ? "selected" : "" ?>
                                    >
                                        <?= w12e($label) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                    <?php endif; ?>

                    <button type="submit" class="w12-save">
                        <?= $editTask
                            ? "✓ Өзгерістерді сақтау"
                            : "+ Тапсырма қосу" ?>
                    </button>

                    <?php if ($editTask): ?>

                        <a href="tasks.php" class="w12-cancel">
                            Болдырмау
                        </a>

                    <?php endif; ?>

                </form>

            </section>

            <!-- PROGRESS -->

            <section class="w12-progress-panel">

                <span class="w12-eyebrow">PRODUCTIVITY</span>

                <h2>Менің нәтижем</h2>

                <div class="w12-progress-number">
                    <?= $progress ?>%
                </div>

                <div class="w12-progress-track">
                    <div
                        class="w12-progress-fill"
                        style="width: <?= $progress ?>%"
                    ></div>
                </div>

                <p>
                    <?= $completedCount ?> тапсырма орындалды.
                    <?= $activeCount + $reserveCount ?> тапсырма қалды.
                </p>

            </section>

        </aside>

    </div>

</div>

<?php include "includes/footer.php"; ?>