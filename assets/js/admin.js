(function () {
    const userSearch = document.getElementById("adminUserSearch");
    if (userSearch) {
        const rows = Array.from(document.querySelectorAll("[data-admin-user]"));
        userSearch.addEventListener("input", function () {
            const query = userSearch.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.classList.toggle("d-none", !row.textContent.toLowerCase().includes(query));
            });
        });
    }

    const journeySearch = document.getElementById("adminJourneySearch");
    if (journeySearch) {
        const rows = Array.from(document.querySelectorAll("[data-admin-journey]"));
        journeySearch.addEventListener("input", function () {
            const query = journeySearch.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.classList.toggle("d-none", !row.textContent.toLowerCase().includes(query));
            });
        });
    }
}());
