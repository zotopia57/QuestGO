(function () {
    const form = document.querySelector(".register-page .auth-form");
    const password = document.getElementById("registerPassword");
    const confirmation = document.getElementById("confirmPassword");

    if (form && password && confirmation) {
        form.addEventListener("submit", function (event) {
            if (password.value !== confirmation.value) {
                event.preventDefault();
                confirmation.setCustomValidity("Passwords do not match.");
                confirmation.reportValidity();
                confirmation.focus();
            } else {
                confirmation.setCustomValidity("");
            }
        });
        confirmation.addEventListener("input", function () {
            confirmation.setCustomValidity("");
        });
    }

    document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const reveal = input.type === "password";
            input.type = reveal ? "text" : "password";
            button.textContent = reveal ? "Hide" : "Show";
            button.setAttribute("aria-label", reveal ? "Hide password" : "Show password");
        });
    });
}());
