(function () {
    const navigation = document.getElementById("mainNavigation");
    const navLinks = document.querySelectorAll("#mainNavigation a[href^=\"#\"]");

    if (navigation && window.bootstrap) {
        navLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                if (navigation.classList.contains("show")) {
                    window.bootstrap.Collapse.getOrCreateInstance(navigation).hide();
                }
            });
        });
    } else {
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (button) {
            button.addEventListener("click", function () {
                const target = document.querySelector(button.getAttribute("data-bs-target"));
                if (!target) return;
                const isOpen = target.classList.toggle("show");
                button.setAttribute("aria-expanded", String(isOpen));
            });
        });
        navLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                if (!navigation) return;
                navigation.classList.remove("show");
                const toggle = document.querySelector('[data-bs-target="#mainNavigation"]');
                if (toggle) toggle.setAttribute("aria-expanded", "false");
            });
        });
    }

    document.querySelectorAll("form[data-demo-form]").forEach(function (form) {
        if (form.method.toUpperCase() === "POST" && form.action.includes("/backend/")) return;
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            const status = form.querySelector(".form-status");
            if (status) {
                status.textContent = "Frontend preview only — nothing was saved or sent. Backend services are not connected yet.";
            }
        });
    });

    document.querySelectorAll("[data-preview-action]").forEach(function (button) {
        button.addEventListener("click", function () {
            let message = button.parentElement.querySelector("[data-preview-message]");
            if (!message) {
                message = document.createElement("span");
                message.dataset.previewMessage = "true";
                message.className = "form-status";
                message.setAttribute("aria-live", "polite");
                button.parentElement.appendChild(message);
            }
            message.textContent = "Preview only — no account or record was changed.";
        });
    });

    document.querySelectorAll("[data-preview-remove]").forEach(function (button) {
        button.addEventListener("click", function () {
            const card = button.closest(".saved-place-item");
            if (card) card.remove();
        });
    });
})();
