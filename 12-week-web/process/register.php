<?php
session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

// Формадағы мәліметтер
$full_name = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

$errors = [];

// Мәліметтерді тексеру
if (empty($full_name)) {
    $errors[] = "Аты-жөніңізді енгізіңіз.";
}

if (empty($email)) {
    $errors[] = "Электрондық поштаңызды енгізіңіз.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email форматы дұрыс емес.";
}

if (strlen($password) < 6) {
    $errors[] = "Құпиясөз кемінде 6 таңбадан тұруы керек.";
}

if ($password !== $confirm_password) {
    $errors[] = "Құпиясөздер сәйкес келмейді.";
}

// Бұрын енгізілген мәліметтерді сақтау
$_SESSION["old"] = [
    "full_name" => $full_name,
    "email" => $email,
    "phone" => $phone
];

// Қателер болса, тіркелу бетіне қайтару
if (!empty($errors)) {
    $_SESSION["errors"] = $errors;
    header("Location: ../register.php");
    exit;
}

try {
    // Email бұрын тіркелгенін тексеру
    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION["errors"] = [
            "Бұл электрондық пошта бұрын тіркелген."
        ];

        header("Location: ../register.php");
        exit;
    }

    // Құпиясөзді шифрлау
    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Пайдаланушыны дерекқорға сақтау
    $stmt = $pdo->prepare(
        "INSERT INTO users
        (full_name, email, phone, password)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $full_name,
        $email,
        $phone,
        $hashed_password
    ]);

    unset($_SESSION["old"]);

    $_SESSION["success"] = "Тіркелу сәтті аяқталды! Енді жүйеге кіре аласыз.";

    header("Location: ../login.php");
    exit;

} catch (PDOException $e) {

    $_SESSION["errors"] = [
        "Дерекқор қатесі: " . $e->getMessage()
    ];

    header("Location: ../register.php");
    exit;
}