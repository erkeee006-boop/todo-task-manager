</main>

<?php if (isset($_SESSION["user_id"])): ?>

        <footer class="dashboard-footer">
            © 2026 TaskFlow. Барлық құқықтар қорғалған.
        </footer>

    </div>

</div>

<?php else: ?>

    <footer class="public-footer">
        © 2026 TaskFlow. Барлық құқықтар қорғалған.
    </footer>

<?php endif; ?>

</body>
</html>