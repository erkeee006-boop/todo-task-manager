<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    $errors = [];

    if (empty($name)) {
        $errors[] = "Аты-жөніңізді енгізіңіз.";
    }

    if (empty($email)) {
        $errors[] = "Электрондық поштаңызды енгізіңіз.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Электрондық пошта дұрыс жазылмаған.";
    }

    if (empty($phone)) {
        $errors[] = "Телефон нөмірін енгізіңіз.";
    } elseif (!preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $phone)) {
        $errors[] = "Телефон нөмірі дұрыс емес.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Құпиясөз кемінде 6 таңбадан тұруы керек.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Құпиясөздер бірдей емес.";
    }

    $pageTitle = "Тіркелу нәтижесі";
    $basePath = "../";

    include "../includes/header.php";

    echo '<section class="result-card">';

    if (!empty($errors)) {

        echo '<h1>Қателер табылды</h1>';
        echo '<div class="error-message">';

        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }

        echo '</div>';

        echo '<a href="../login.php" class="btn-primary">Қайта көру</a>';

    } else {

        echo '<div class="success-icon">✓</div>';
        echo '<h1>Тексеру сәтті өтті!</h1>';

        echo '<p>Қош келдіңіз, ' .
            htmlspecialchars($name) . '!</p>';

        echo '<p>Электрондық пошта: ' .
            htmlspecialchars($email) . '</p>';

        echo '<p>Телефон: ' .
            htmlspecialchars($phone) . '</p>';

        echo '<p class="notice">
            Бұл 10-аптадағы демонстрациялық тіркелу.
            Мәліметтер дерекқорға әлі сақталмайды.
        </p>';

        echo '<a href="../tasks.php" class="btn-primary">
            Тапсырмаларға өту
        </a>';
    }

    echo '</section>';

    include "../includes/footer.php";

} else {

    header("Location: ../login.php");
    exit;

}

?>