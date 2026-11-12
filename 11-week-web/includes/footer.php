</main>

<?php if (isset($_SESSION["user_id"])): ?>

    <footer class="dashboard-footer">
        © 2026 TaskFlow. Тапсырмаларыңызды тиімді басқарыңыз.
    </footer>

</div>

<?php else: ?>

    <footer class="public-footer">
        © 2026 TaskFlow. Барлық құқықтар қорғалған.
    </footer>

<?php endif; ?>

</body>
</html>