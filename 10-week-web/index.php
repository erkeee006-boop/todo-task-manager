<?php
$pageTitle = 'Басты бет';
include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <span class="hero-label">TASK MANAGEMENT SYSTEM</span>

        <h1>Тапсырмаларыңды оңай <span>басқар</span></h1>

        <p>
            TaskFlow — күнделікті тапсырмаларды жоспарлауға,
            бақылауға және уақытты тиімді пайдалануға арналған жүйе.
        </p>

        <div class="hero-buttons">
            <a href="tasks.php" class="btn-primary">Тапсырмаларға өту</a>
            <a href="login.php" class="btn-secondary">Тіркелу</a>
        </div>
    </div>

    <div class="hero-card">
        <div class="hero-card-top">
            <span>Менің жоспарым</span>
            <span class="card-icon">✓</span>
        </div>

        <div class="mini-task">
            <span class="task-check">✓</span>
            <div>
                <strong>Сабаққа дайындалу</strong>
                <small>Орындалған</small>
            </div>
        </div>

        <div class="mini-task">
            <span class="task-empty"></span>
            <div>
                <strong>Жоба жұмысын жасау</strong>
                <small>Орындалмаған</small>
            </div>
        </div>

        <div class="mini-task">
            <span class="task-empty"></span>
            <div>
                <strong>Кітап оқу</strong>
                <small>Жоспарда</small>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <h2>TaskFlow мүмкіндіктері</h2>

    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">✓</div>
            <h3>Тапсырмаларды басқару</h3>
            <p>Тапсырмаларды бір жерден бақылауға болады.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">⌕</div>
            <h3>Іздеу және сүзу</h3>
            <p>Қажетті тапсырмаларды оңай табуға болады.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">◷</div>
            <h3>Уақытты жоспарлау</h3>
            <p>Күнделікті жұмыстарды ретімен орындауға көмектеседі.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>