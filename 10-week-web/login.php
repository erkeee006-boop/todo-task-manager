<?php
$pageTitle = 'Тіркелу';
include 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-card">

        <div class="auth-heading">
            <span class="hero-label">WELCOME TO TASKFLOW</span>
            <h1>Тіркелу</h1>
            <p>Жаңа аккаунт ашып, тапсырмаларыңды басқара баста.</p>
        </div>

        <form action="process/register.php" method="POST">

            <div class="form-group">
                <label for="name">Аты-жөні</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Аты-жөніңізді енгізіңіз"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Электрондық пошта</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="example@gmail.com"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone">Телефон нөмірі</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="+7 (700) 123-45-67"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Құпиясөз</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Құпиясөз енгізіңіз"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Құпиясөзді қайталау</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Құпиясөзді қайталаңыз"
                    required
                >
            </div>

            <button type="submit" class="btn-primary form-button">
                Тіркелу
            </button>

        </form>

        <p class="auth-bottom">
            Аккаунтыңыз бар ма?
            <a href="login.php">Тіркелу формасын пайдаланыңыз</a>
        </p>

    </div>
</section>

<?php include 'includes/footer.php'; ?>