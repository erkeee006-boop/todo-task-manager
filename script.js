const loginTab = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

if (loginTab && registerTab && loginForm && registerForm) {
    loginTab.addEventListener("click", function () {
        loginForm.hidden = false;
        registerForm.hidden = true;

        loginTab.classList.add("active");
        registerTab.classList.remove("active");
    });

    registerTab.addEventListener("click", function () {
        loginForm.hidden = true;
        registerForm.hidden = false;

        registerTab.classList.add("active");
        loginTab.classList.remove("active");
    });
}

if (loginForm) {
    loginForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("loginEmail").value.trim();
        const password = document.getElementById("loginPassword").value;
        const message = document.getElementById("loginMessage");

        if (!email || password.length < 6) {
            message.textContent = "Пошта мен құпиясөзді дұрыс енгіз.";
            message.style.color = "#A15D45";
            return;
        }

        message.textContent =
            "Демо-нұсқа: форма дұрыс толтырылды. Нақты кіру үшін кейін сервер қосылады.";
        message.style.color = "#606C38";
    });
}

if (registerForm) {
    registerForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("registerName").value.trim();
        const email = document.getElementById("registerEmail").value.trim();
        const password = document.getElementById("registerPassword").value;
        const message = document.getElementById("registerMessage");

        if (!name || !email || password.length < 6) {
            message.textContent =
                "Барлық жолды толтыр. Құпиясөз кемінде 6 таңба болуы керек.";
            message.style.color = "#A15D45";
            return;
        }

        message.textContent =
            "Форма дұрыс толтырылды! Демо-нұсқада аккаунт серверге сақталмайды.";
        message.style.color = "#606C38";
    });
}

// Тапсырмалар беті

const taskForm = document.getElementById("taskForm");

if (taskForm) {
    const taskInput = document.getElementById("taskInput");
    const taskList = document.getElementById("taskList");
    const searchInput = document.getElementById("searchInput");
    const filterSelect = document.getElementById("filterSelect");
    const emptyState = document.getElementById("emptyState");
    const totalCount = document.getElementById("totalCount");
    const completedCount = document.getElementById("completedCount");
    const pendingCount = document.getElementById("pendingCount");
    const taskSummary = document.getElementById("taskSummary");
    const clearCompleted = document.getElementById("clearCompleted");
    const todayDate = document.getElementById("todayDate");

    let tasks = [];

    try {
        const savedTasks = localStorage.getItem("taskflow_tasks");
        tasks = savedTasks ? JSON.parse(savedTasks) : [];

        if (!Array.isArray(tasks)) {
            tasks = [];
        }
    } catch (error) {
        tasks = [];
    }

    todayDate.textContent = new Date().toLocaleDateString("kk-KZ", {
        day: "numeric",
        month: "short"
    });

    function saveTasks() {
        try {
            localStorage.setItem("taskflow_tasks", JSON.stringify(tasks));
        } catch (error) {
            alert("Тапсырманы браузерде сақтау мүмкін болмады.");
        }
    }

    function updateStatistics() {
        const completed = tasks.filter(function (task) {
            return task.completed;
        }).length;

        const pending = tasks.length - completed;

        totalCount.textContent = tasks.length;
        completedCount.textContent = completed;
        pendingCount.textContent = pending;
        taskSummary.textContent = "Барлығы: " + tasks.length + " тапсырма";
    }

    function renderTasks() {
        taskList.replaceChildren();

        const searchText = searchInput.value.trim().toLowerCase();
        const filter = filterSelect.value;

        const filteredTasks = tasks.filter(function (task) {
            const matchesSearch = task.text.toLowerCase().includes(searchText);

            const matchesFilter =
                filter === "all" ||
                (filter === "completed" && task.completed) ||
                (filter === "pending" && !task.completed);

            return matchesSearch && matchesFilter;
        });

        filteredTasks.forEach(function (task) {
            const item = document.createElement("li");
            item.className = "task-item";

            if (task.completed) {
                item.classList.add("completed");
            }

            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.className = "task-check";
            checkbox.checked = task.completed;
            checkbox.setAttribute("aria-label", "Тапсырманы орындау");

            checkbox.addEventListener("change", function () {
                task.completed = checkbox.checked;
                saveTasks();
                renderTasks();
            });

            const text = document.createElement("span");
            text.className = "task-text";
            text.textContent = task.text;

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.className = "delete-button";
            deleteButton.textContent = "Жою";
            deleteButton.setAttribute("aria-label", "Тапсырманы жою");

            deleteButton.addEventListener("click", function () {
                tasks = tasks.filter(function (currentTask) {
                    return currentTask.id !== task.id;
                });

                saveTasks();
                renderTasks();
            });

            item.append(checkbox, text, deleteButton);
            taskList.appendChild(item);
        });

        emptyState.hidden = filteredTasks.length > 0;

        if (tasks.length > 0 && filteredTasks.length === 0) {
            emptyState.querySelector("h3").textContent =
                "Тапсырма табылмады";
            emptyState.querySelector("p").textContent =
                "Іздеу сөзін немесе сүзгіні өзгертіп көр.";
        } else {
            emptyState.querySelector("h3").textContent =
                "Әзірге тапсырма жоқ";
            emptyState.querySelector("p").textContent =
                "Алғашқы тапсырмаңды қосып, күніңді жоспарла.";
        }

        updateStatistics();
    }

    taskForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const text = taskInput.value.trim();

        if (!text) {
            return;
        }

        tasks.unshift({
            id: Date.now().toString() + Math.random().toString(16).slice(2),
            text: text,
            completed: false
        });

        saveTasks();
        taskInput.value = "";
        searchInput.value = "";
        filterSelect.value = "all";
        taskInput.focus();

        renderTasks();
    });

    searchInput.addEventListener("input", renderTasks);
    filterSelect.addEventListener("change", renderTasks);

    clearCompleted.addEventListener("click", function () {
        tasks = tasks.filter(function (task) {
            return !task.completed;
        });

        saveTasks();
        renderTasks();
    });

    renderTasks();
}