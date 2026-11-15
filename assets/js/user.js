(function () {
    const form = document.querySelector(".password-panel form");
    const password = document.getElementById("newPassword");
    const confirmation = document.getElementById("confirmNewPassword");

    if (!form || !password || !confirmation) return;

    confirmation.addEventListener("input", function () {
        confirmation.setCustomValidity("");
    });

    form.addEventListener("submit", function (event) {
        if (password.value !== confirmation.value) {
            event.preventDefault();
            confirmation.setCustomValidity("The new passwords do not match.");
            confirmation.reportValidity();
        }
    });
})();
