<?php
session_start();

/* Бастапқы тапсырмалар */
if (!isset($_SESSION["tasks"])) {
    $_SESSION["tasks"] = [
        [
            "id" => 1,
            "title" => "PHP тақырыбын оқу",
            "description" => "PHP негіздерін қайталау",
            "date" => date("Y-m-d"),
            "status" => "active"
        ],
        [
            "id" => 2,
            "title" => "Жоба жұмысын жасау",
            "description" => "TaskFlow жобасын жалғастыру",
            "date" => date("Y-m-d", strtotime("+1 day")),
            "status" => "active"
        ],
        [
            "id" => 3,
            "title" => "HTML тапсырмасын аяқтау",
            "description" => "Frontend жұмысын аяқтау",
            "date" => date("Y-m-d", strtotime("-1 day")),
            "status" => "completed"
        ],
        [
            "id" => 4,
            "title" => "Ағылшын тілін оқу",
            "description" => "Жаңа сөздерді жаттау",
            "date" => date("Y-m-d", strtotime("+2 days")),
            "status" => "reserve"
        ]
    ];
}

/* Хабарлама */
if (!isset($_SESSION["message"])) {
    $_SESSION["message"] = "";
}

/* POST сұраныстарын өңдеу */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* Жаңа тапсырма қосу */
    if ($action === "add") {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $date = $_POST["date"] ?? "";

        if ($title === "") {

            $_SESSION["message"] = "Тапсырма атауын енгізіңіз.";

        } elseif (mb_strlen($title) > 100) {

            $_SESSION["message"] = "Тапсырма атауы 100 таңбадан аспауы керек.";

        } elseif (
            $date === "" ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate(
                (int)substr($date, 5, 2),
                (int)substr($date, 8, 2),
                (int)substr($date, 0, 4)
            )
        ) {

            $_SESSION["message"] = "Дұрыс күнді таңдаңыз.";

        } else {

            $newId = 1;

            foreach ($_SESSION["tasks"] as $task) {
                if ($task["id"] >= $newId) {
                    $newId = $task["id"] + 1;
                }
            }

            $_SESSION["tasks"][] = [
                "id" => $newId,
                "title" => $title,
                "description" => $description,
                "date" => $date,
                "status" => "active"
            ];

            $_SESSION["message"] = "Жаңа тапсырма сәтті қосылды!";
        }
    }

    /* Мәртебені өзгерту */
    elseif ($action === "change_status") {

        $id = (int)($_POST["id"] ?? 0);
        $newStatus = $_POST["status"] ?? "";

        $allowedStatuses = ["active", "completed", "reserve"];

        if (in_array($newStatus, $allowedStatuses, true)) {

            foreach ($_SESSION["tasks"] as &$task) {

                if ($task["id"] === $id) {
                    $task["status"] = $newStatus;
                    break;
                }
            }

            unset($task);

            $_SESSION["message"] = "Тапсырма мәртебесі өзгертілді!";

        }
    }

    /* Тапсырманы жою */
    elseif ($action === "delete") {

        $id = (int)($_POST["id"] ?? 0);

        foreach ($_SESSION["tasks"] as $key => $task) {

            if ($task["id"] === $id) {
                unset($_SESSION["tasks"][$key]);
                break;
            }
        }

        $_SESSION["tasks"] = array_values($_SESSION["tasks"]);

        $_SESSION["message"] = "Тапсырма жойылды!";
    }

    header("Location: tasks.php");
    exit;
}

/* GET арқылы іздеу және сүзу */
$status = $_GET["status"] ?? "all";
$search = trim($_GET["search"] ?? "");

$allowedStatuses = ["all", "active", "completed", "reserve"];

if (!in_array($status, $allowedStatuses, true)) {
    $status = "all";
}

$filteredTasks = [];

foreach ($_SESSION["tasks"] as $task) {

    $matchesStatus = (
        $status === "all" ||
        $task["status"] === $status
    );

    $matchesSearch = (
        $search === "" ||
        mb_stripos($task["title"], $search) !== false ||
        mb_stripos($task["description"], $search) !== false
    );

    if ($matchesStatus && $matchesSearch) {
        $filteredTasks[] = $task;
    }
}

/* Тапсырмалар саны */
$totalCount = count($_SESSION["tasks"]);
$activeCount = 0;
$completedCount = 0;
$reserveCount = 0;

foreach ($_SESSION["tasks"] as $task) {

    if ($task["status"] === "active") {
        $activeCount++;
    } elseif ($task["status"] === "completed") {
        $completedCount++;
    } elseif ($task["status"] === "reserve") {
        $reserveCount++;
    }
}

$pageTitle = "Менің тапсырмаларым";
include "includes/header.php";

$message = $_SESSION["message"];
$_SESSION["message"] = "";
?>

<section class="tasks-section">

    <!-- Бет тақырыбы -->
    <div class="tasks-heading">
        <div>
            <span class="hero-label">MY TASKS</span>
            <h1>Менің тапсырмаларым</h1>
            <p>Күнделікті тапсырмаларыңды жоспарла және бақыла.</p>
        </div>
    </div>

    <!-- Хабарлама -->
    <?php if ($message !== ""): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="statistics">

        <div class="stat-card">
            <span>Барлығы</span>
            <h2><?= $totalCount ?></h2>
        </div>

        <div class="stat-card">
            <span>Орындалмаған</span>
            <h2><?= $activeCount ?></h2>
        </div>

        <div class="stat-card">
            <span>Орындалған</span>
            <h2><?= $completedCount ?></h2>
        </div>

        <div class="stat-card">
            <span>Резерв</span>
            <h2><?= $reserveCount ?></h2>
        </div>

    </div>

    <!-- Тапсырма қосу формасы -->
    <div class="add-task-card">

        <h2>+ Жаңа тапсырма қосу</h2>

        <form method="POST" action="tasks.php">

            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="title">Тапсырма атауы</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Мысалы: PHP тақырыбын оқу"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Сипаттамасы</label>

                <input
                    type="text"
                    id="description"
                    name="description"
                    placeholder="Тапсырма туралы қысқаша ақпарат"
                >
            </div>

            <div class="form-group">
                <label for="date">Орындау күні</label>

                <input
                    type="date"
                    id="date"
                    name="date"
                    value="<?= date("Y-m-d") ?>"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">
                + Тапсырма қосу
            </button>

        </form>

    </div>

    <!-- Сүзгілер -->
    <div class="filter-buttons">

        <a href="tasks.php?status=all"
           class="filter-btn <?= $status === "all" ? "active" : "" ?>">
            Барлығы
        </a>

        <a href="tasks.php?status=active"
           class="filter-btn <?= $status === "active" ? "active" : "" ?>">
            Орындалмаған
        </a>

        <a href="tasks.php?status=completed"
           class="filter-btn <?= $status === "completed" ? "active" : "" ?>">
            Орындалған
        </a>

        <a href="tasks.php?status=reserve"
           class="filter-btn <?= $status === "reserve" ? "active" : "" ?>">
            Резерв
        </a>

    </div>

    <!-- Іздеу -->
    <form method="GET" action="tasks.php" class="search-form">

        <input
            type="hidden"
            name="status"
            value="<?= htmlspecialchars($status) ?>"
        >

        <input
            type="text"
            name="search"
            placeholder="Тапсырманы іздеу..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <button type="submit" class="btn-primary">
            Іздеу
        </button>

    </form>

    <!-- Тапсырмалар тізімі -->
    <div class="task-list">

        <?php if (count($filteredTasks) > 0): ?>

            <?php foreach ($filteredTasks as $task): ?>

                <div class="task-card">

                    <div class="task-info">

                        <h3>
                            <?= htmlspecialchars($task["title"]) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($task["description"]) ?>
                        </p>

                        <p class="task-date">
                            <span>Күні:</span>
                            <?= date("d.m.Y", strtotime($task["date"])) ?>
                        </p>

                        <?php if ($task["status"] === "active"): ?>

                            <span class="status active-status">
                                Орындалмаған
                            </span>

                        <?php elseif ($task["status"] === "completed"): ?>

                            <span class="status completed-status">
                                Орындалды
                            </span>

                        <?php else: ?>

                            <span class="status reserve-status">
                                Резерв
                            </span>

                        <?php endif; ?>

                    </div>

                    <!-- Мәртебені өзгерту -->
                    <div class="task-actions">

                        <?php if ($task["status"] !== "completed"): ?>

                            <form method="POST" action="tasks.php">
                                <input type="hidden" name="action"
                                       value="change_status">

                                <input type="hidden" name="id"
                                       value="<?= $task["id"] ?>">

                                <input type="hidden" name="status"
                                       value="completed">

                                <button type="submit"
                                        class="action-btn complete-btn">
                                    ✓ Орындалды
                                </button>
                            </form>

                        <?php else: ?>

                            <form method="POST" action="tasks.php">
                                <input type="hidden" name="action"
                                       value="change_status">

                                <input type="hidden" name="id"
                                       value="<?= $task["id"] ?>">

                                <input type="hidden" name="status"
                                       value="active">

                                <button type="submit"
                                        class="action-btn undo-btn">
                                    ↩ Орындалмады
                                </button>
                            </form>

                        <?php endif; ?>

                        <?php if ($task["status"] !== "reserve"): ?>

                            <form method="POST" action="tasks.php">
                                <input type="hidden" name="action"
                                       value="change_status">

                                <input type="hidden" name="id"
                                       value="<?= $task["id"] ?>">

                                <input type="hidden" name="status"
                                       value="reserve">

                                <button type="submit"
                                        class="action-btn reserve-btn">
                                    Резервке қою
                                </button>
                            </form>

                        <?php else: ?>

                            <form method="POST" action="tasks.php">
                                <input type="hidden" name="action"
                                       value="change_status">

                                <input type="hidden" name="id"
                                       value="<?= $task["id"] ?>">

                                <input type="hidden" name="status"
                                       value="active">

                                <button type="submit"
                                        class="action-btn undo-btn">
                                    Қайтару
                                </button>
                            </form>

                        <?php endif; ?>

                        <!-- Жою -->
                        <form method="POST" action="tasks.php"
                              onsubmit="return confirm('Бұл тапсырманы жоясыз ба?');">

                            <input type="hidden" name="action"
                                   value="delete">

                            <input type="hidden" name="id"
                                   value="<?= $task["id"] ?>">

                            <button type="submit"
                                    class="action-btn delete-btn">
                                Жою
                            </button>

                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">
                <h3>Тапсырмалар табылмады</h3>
                <p>Басқа сүзгіні немесе іздеу сөзін қолданып көр.</p>
            </div>

        <?php endif; ?>

    </div>

</section>

<?php include "includes/footer.php"; ?>