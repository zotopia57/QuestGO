(function () {
    const root = document.querySelector(".dashboard-content");
    const csrfToken = root ? root.dataset.csrfToken : "";
    const baseUrl = root ? root.dataset.baseUrl : "";
    const endpoint = function (path) { return baseUrl + path; };

    async function request(url, options) {
        const response = await fetch(url, Object.assign({
            credentials: "same-origin",
            headers: { "Accept": "application/json" }
        }, options || {}));
        const contentType = response.headers.get("content-type") || "";
        if (!contentType.includes("application/json")) {
            throw new Error(response.status === 401 || response.status === 403
                ? "Your session may have expired. Log in again."
                : "The request could not be completed.");
        }
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || "The request could not be completed.");
        return result;
    }

    function escapeHtml(value) {
        return String(value == null ? "" : value).replace(/[&<>"']/g, function (character) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[character];
        });
    }

    function csrfPost(data) {
        const values = Object.assign({}, data, { csrf_token: csrfToken });
        const url = values.endpoint;
        delete values.endpoint;
        const body = new URLSearchParams(values);
        return request(url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8", "Accept": "application/json" },
            body: body.toString()
        });
    }

    function setStatus(element, message, isError) {
        if (!element) return;
        element.textContent = message;
        element.classList.toggle("text-danger", Boolean(isError));
    }

    function destinationImageUrl(imagePath) {
        if (!imagePath) return "";
        return /^(https?:)?\/\//i.test(imagePath)
            ? imagePath
            : endpoint("/" + imagePath.replace(/^\/+/, ""));
    }

    function destinationImageAlt(place) {
        return "Photograph of " + place.name + " in " + place.city + ", " + place.state_or_ut;
    }

    function renderPlaceCard(place, savedMode) {
        const imageUrl = destinationImageUrl(place.image_url);
        const image = imageUrl
            ? '<img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(destinationImageAlt(place)) + '" loading="lazy">'
            : '<span class="place-photo-unavailable">Photograph unavailable</span>';
        const category = escapeHtml(place.category_name);
        const name = escapeHtml(place.name);
        const cost = Number(place.estimated_cost).toLocaleString("en-IN");
        const placeData = encodeURIComponent(JSON.stringify(place));
        const gemBadge = Number(place.is_hidden_gem)
            ? '<span class="place-gem-badge">' + (place.discovery_status === "established" ? "Established destination" : "Lesser-known place") + '</span>'
            : "";
        const action = savedMode
            ? '<button type="button" class="btn btn-link-quest" data-place-remove="' + place.id + '">Remove</button>'
            : '<button type="button" class="btn btn-outline-quest" data-place-save="' + place.id + '"' +
                (Number(place.saved) ? " disabled" : "") + '>' + (Number(place.saved) ? "♡ Saved" : "♡ Save place") + '</button>';

        return '<div class="col-md-6 col-xl-4 place-result"><article class="place-card">' +
            '<div class="place-card-image">' + image + '<span class="place-region">' + escapeHtml(place.region) + ' India</span>' + gemBadge + '</div>' +
            '<div class="place-card-body"><p class="place-state">' + escapeHtml(place.state_or_ut) + ' · ' + category + '</p>' +
            '<h2>' + name + '</h2><p>' + escapeHtml(place.description) + '</p>' +
            '<div class="place-facts"><span><small>Suggested duration</small><strong>' + escapeHtml(place.suggested_duration) + '</strong></span>' +
            '<span><small>Estimated expenses</small><strong>₹' + cost + '</strong></span></div>' +
            '<div class="place-actions">' + action +
            '<button type="button" class="btn btn-link-quest" data-place-detail="' + placeData + '">View details <span aria-hidden="true">↗</span></button>' +
            '<button type="button" class="btn btn-link-quest" data-place-journey="' + place.id + '">Add to journey</button></div></div></article></div>';
    }

    function loadPlaces() {
        const grid = document.getElementById("placeGrid");
        if (!grid) return;
        const savedMode = document.body.classList.contains("user-layout") && /saved-places\.php$/i.test(location.pathname);
        const hiddenGemMode = /hidden-gems\.php$/i.test(location.pathname);
        const searchForm = document.getElementById("placeFilters");
        if (searchForm && !searchForm.dataset.bound) {
            searchForm.dataset.bound = "true";
            searchForm.addEventListener("submit", function (event) {
                event.preventDefault();
                loadPlaces();
            });
            searchForm.querySelectorAll("select").forEach(function (select) {
                select.addEventListener("change", loadPlaces);
            });
            const searchInput = searchForm.querySelector('input[type="search"]');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener("input", function () {
                    window.clearTimeout(searchTimeout);
                    searchTimeout = window.setTimeout(loadPlaces, 250);
                });
            }
        }
        const params = new URLSearchParams(searchForm ? new FormData(searchForm) : {});
        if (savedMode) params.set("saved", "1");
        if (hiddenGemMode) params.set("hidden_gems", "1");
        request(endpoint("/backend/user/places-process.php") + "?" + params.toString())
            .then(function (result) {
                grid.innerHTML = result.places.length
                    ? result.places.map(function (place) { return renderPlaceCard(place, savedMode); }).join("")
                    : '<div class="col-12"><div class="empty-state">' +
                        (savedMode ? 'No saved destinations yet. Explore India and save a place to keep it close.' :
                            hiddenGemMode ? 'No curated places match these filters.' : 'No destinations match these filters.') +
                        '</div></div>';
                grid.querySelectorAll(".place-card-image img").forEach(function (image) {
                    image.addEventListener("error", function () {
                        const unavailable = document.createElement("span");
                        unavailable.className = "place-photo-unavailable";
                        unavailable.textContent = "Photograph unavailable";
                        image.replaceWith(unavailable);
                    });
                });
                const count = document.getElementById("placeCount");
                if (count) count.textContent = result.places.length;
                grid.querySelectorAll("[data-place-save]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({ endpoint: endpoint("/backend/user/places-process.php"), action: "save", place_id: button.dataset.placeSave })
                            .then(function () { button.textContent = "♡ Saved"; button.disabled = true; })
                            .catch(function (error) { window.alert(error.message); });
                    });
                });
                grid.querySelectorAll("[data-place-remove]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({ endpoint: endpoint("/backend/user/places-process.php"), action: "remove", place_id: button.dataset.placeRemove })
                            .then(function () {
                                button.closest(".place-result").remove();
                                const remaining = grid.querySelectorAll(".place-result").length;
                                if (count) count.textContent = remaining;
                                if (savedMode && remaining === 0) {
                                    grid.innerHTML = '<div class="col-12"><div class="empty-state">No saved places yet. Explore India and save a place to keep it close.</div></div>';
                                }
                            })
                            .catch(function (error) { window.alert(error.message); });
                    });
                });
                grid.querySelectorAll("[data-place-detail]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        const place = JSON.parse(decodeURIComponent(button.dataset.placeDetail));
                        const placeImage = button.closest(".place-card").querySelector(".place-card-image img");
                        const imageMarkup = placeImage
                            ? '<img class="place-detail-image" src="' + escapeHtml(placeImage.src) + '" alt="' + escapeHtml(destinationImageAlt(place)) + '">'
                            : '<div class="place-detail-photo-unavailable">Photograph unavailable</div>';
                        const activities = Array.isArray(place.activities) ? place.activities : [];
                        const activityMarkup = activities.length
                            ? '<ul class="place-detail-activities">' + activities.map(function (activity) {
                                const minutes = Number(activity.duration_minutes);
                                const hours = Math.floor(minutes / 60);
                                const remainingMinutes = minutes % 60;
                                const duration = (hours ? hours + " hr" + (hours === 1 ? "" : "s") : "") +
                                    (hours && remainingMinutes ? " " : "") +
                                    (remainingMinutes ? remainingMinutes + " min" : "");
                                return '<li><strong>' + escapeHtml(activity.title) + '</strong> <span>' +
                                    escapeHtml(activity.category_name) + '</span><p>' + escapeHtml(activity.description) +
                                    '</p><small>Estimated duration: ' + escapeHtml(duration) +
                                    ' · Estimated cost: ₹' + Number(activity.estimated_cost).toLocaleString("en-IN") +
                                    '</small>' + (activity.best_time ? '<small><br>Suitable time: ' + escapeHtml(activity.best_time) + '</small>' : '') + '</li>';
                            }).join("") + '</ul>'
                            : '<p>No activity suggestions are stored for this destination yet.</p>';
                        const stops = Array.isArray(place.useful_stops) ? place.useful_stops : [];
                        const stopMarkup = stops.length
                            ? '<ul class="place-detail-activities">' + stops.map(function (stop) {
                                return '<li><strong>' + escapeHtml(stop.name) + '</strong> <span>' +
                                    escapeHtml(stop.category_name) + '</span><p>' + escapeHtml(stop.address) +
                                    '</p><small>' + escapeHtml(stop.city) + ', ' + escapeHtml(stop.state_or_ut) +
                                    '</small> <a href="' + escapeHtml(stop.source_url) +
                                    '" target="_blank" rel="noopener noreferrer">Source</a> <button type="button" class="btn btn-link-quest" data-modal-stop-save="' +
                                    stop.id + '"' + (Number(stop.saved) ? ' disabled' : '') + '>' +
                                    (Number(stop.saved) ? '♡ Saved' : '♡ Save stop') + '</button></li>';
                            }).join("") + '</ul>'
                            : '<p>No sourced useful stops are associated with this destination yet.</p>';
                        document.getElementById("placeDetailsTitle").textContent = place.name;
                        document.getElementById("placeDetailsBody").innerHTML =
                            imageMarkup + "<p><strong>" + escapeHtml(place.city) + ", " + escapeHtml(place.state_or_ut) + "</strong></p>" +
                            "<p>" + escapeHtml(place.description) + "</p><p>Category: " + escapeHtml(place.category_name) +
                            "<br>Estimated expenses: ₹" + Number(place.estimated_cost).toLocaleString("en-IN") +
                            "<br>Suggested duration: " + escapeHtml(place.suggested_duration) +
                            (place.best_visiting_season ? "<br>Best visiting season: " + escapeHtml(place.best_visiting_season) : "") +
                            (place.distance ? "<br>Recorded distance: " + escapeHtml(place.distance) + " km" : "") +
                            "<br><small>All costs are illustrative estimates, not guaranteed prices.</small></p>" +
                            (Number(place.is_hidden_gem) ? "<h3 class=\"place-detail-heading\">Things to do</h3><p>" + escapeHtml(place.things_to_do || "Details have not yet been added.") +
                                "</p>" + (place.travel_tips ? "<p><strong>Travel tips:</strong> " + escapeHtml(place.travel_tips) + "</p>" : "") +
                                (place.access_notes ? "<p><strong>Access and safety:</strong> " + escapeHtml(place.access_notes) + "</p>" : "") : "") +
                            "<h3 class=\"place-detail-heading\">Suggested activities</h3>" + activityMarkup +
                            "<h3 class=\"place-detail-heading\">Associated useful stops</h3>" + stopMarkup;
                        document.getElementById("placeDetailsBody").querySelectorAll("[data-modal-stop-save]").forEach(function (saveButton) {
                            saveButton.addEventListener("click", function () {
                                csrfPost({
                                    endpoint: endpoint("/backend/user/saved-discovery-process.php"),
                                    action: "save",
                                    item_type: "stop",
                                    item_id: saveButton.dataset.modalStopSave
                                }).then(function () {
                                    saveButton.textContent = "♡ Saved";
                                    saveButton.disabled = true;
                                }).catch(function (error) { window.alert(error.message); });
                            });
                        });
                        if (window.bootstrap) {
                            window.bootstrap.Modal.getOrCreateInstance(document.getElementById("placeDetailsModal")).show();
                        } else {
                            window.alert("The details dialog could not load. Reload the page and try again.");
                        }

                    });
                });
                grid.querySelectorAll("[data-place-journey]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        const select = document.getElementById("journeyForPlace");
                        const form = document.getElementById("addPlaceJourneyForm");
                        if (!select || !form || !window.bootstrap) {
                            window.alert("The journey dialog could not load. Reload the page and try again.");
                            return;
                        }
                        request(endpoint("/backend/user/journey-process.php")).then(function (result) {
                            if (!result.journeys.length) {
                                window.alert("Create a journey first, then add places as stops.");
                                return;
                            }
                            select.innerHTML = result.journeys.map(function (journey) {
                                return '<option value="' + journey.id + '">' + escapeHtml(journey.title) + '</option>';
                            }).join("");
                            form.dataset.placeId = button.dataset.placeJourney;
                            setStatus(form.querySelector(".form-status"), "", false);
                            window.bootstrap.Modal.getOrCreateInstance(document.getElementById("journeySelectModal")).show();
                        }).catch(function (error) { window.alert(error.message); });
                    });
                });
            })
            .catch(function (error) { grid.innerHTML = '<div class="col-12"><div class="empty-state">' + escapeHtml(error.message) + '</div></div>'; });
    }

    function loadSavedDiscoveries() {
        const activityGrid = document.getElementById("savedActivityGrid");
        const stopGrid = document.getElementById("savedStopGrid");
        if (!activityGrid && !stopGrid) return;
        request(endpoint("/backend/user/saved-discovery-process.php")).then(function (result) {
            if (activityGrid) {
                activityGrid.innerHTML = result.activities.length ? result.activities.map(function (activity) {
                    const imageUrl = destinationImageUrl(activity.image_url);
                    return '<div class="col-md-6"><article class="activity-card">' +
                        (imageUrl ? '<img class="activity-photo" src="' + escapeHtml(imageUrl) + '" alt="Photograph of ' +
                            escapeHtml(activity.city) + ', ' + escapeHtml(activity.state_or_ut) + '" loading="lazy">' :
                            '<div class="activity-photo-unavailable">Photograph unavailable</div>') +
                        '<div class="activity-content"><span class="activity-category">' + escapeHtml(activity.category_name) +
                        '</span><h2>' + escapeHtml(activity.title) + '</h2><p>' + escapeHtml(activity.description) +
                        '</p><div class="activity-facts"><span>' + escapeHtml(activity.duration_minutes) + ' min</span><span>₹' +
                        Number(activity.estimated_cost).toLocaleString("en-IN") + '</span><span>' + escapeHtml(activity.city) +
                        ', ' + escapeHtml(activity.state_or_ut) + '</span></div><button type="button" class="btn btn-link-quest text-danger" data-unsave-activity="' +
                        activity.id + '">Remove</button></div></article></div>';
                }).join("") : '<div class="col-12"><div class="empty-state">No saved activities yet.</div></div>';
                const count = document.getElementById("savedActivityCount");
                if (count) count.textContent = result.activities.length;
                activityGrid.querySelectorAll("[data-unsave-activity]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({ endpoint: endpoint("/backend/user/saved-discovery-process.php"), action: "remove", item_type: "activity", item_id: button.dataset.unsaveActivity })
                            .then(loadSavedDiscoveries).catch(function (error) { window.alert(error.message); });
                    });
                });
            }
            if (stopGrid) {
                stopGrid.innerHTML = result.stops.length ? result.stops.map(function (stop) {
                    return '<article class="stop-card"><div class="stop-card-top"><span>' + escapeHtml(stop.category_name) +
                        '</span><span aria-hidden="true">⌖</span></div><h2>' + escapeHtml(stop.name) + '</h2><p>' +
                        escapeHtml(stop.description) + '</p><div class="stop-sample"><strong>' + escapeHtml(stop.address) +
                        '</strong><small>' + escapeHtml(stop.city) + ', ' + escapeHtml(stop.state_or_ut) +
                        '</small></div><a href="' + escapeHtml(stop.source_url) +
                        '" target="_blank" rel="noopener noreferrer">Source: ' + escapeHtml(stop.source_name) +
                        '</a><div><button type="button" class="btn btn-link-quest text-danger" data-unsave-stop="' +
                        stop.id + '">Remove</button></div></article>';
                }).join("") : '<div class="empty-state">No saved useful stops yet.</div>';
                const count = document.getElementById("savedStopCount");
                if (count) count.textContent = result.stops.length;
                stopGrid.querySelectorAll("[data-unsave-stop]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({ endpoint: endpoint("/backend/user/saved-discovery-process.php"), action: "remove", item_type: "stop", item_id: button.dataset.unsaveStop })
                            .then(loadSavedDiscoveries).catch(function (error) { window.alert(error.message); });
                    });
                });
            }
        }).catch(function (error) {
            if (activityGrid) activityGrid.innerHTML = '<div class="col-12"><div class="empty-state">' + escapeHtml(error.message) + '</div></div>';
            if (stopGrid) stopGrid.innerHTML = '<div class="empty-state">' + escapeHtml(error.message) + '</div>';
        });
    }

    const journeyStopForm = document.getElementById("addPlaceJourneyForm");
    if (journeyStopForm) {
        journeyStopForm.addEventListener("submit", function (event) {
            event.preventDefault();
            csrfPost({
                endpoint: endpoint("/backend/user/journey-process.php"),
                action: "add_stop",
                journey_id: journeyStopForm.querySelector("#journeyForPlace").value,
                place_id: journeyStopForm.dataset.placeId,
                visit_date: journeyStopForm.querySelector("#journeyStopDate").value,
                notes: journeyStopForm.querySelector("#journeyStopNotes").value
            }).then(function (result) {
                setStatus(journeyStopForm.querySelector(".form-status"), result.message || "Place added to your journey.", false);
            }).catch(function (error) {
                setStatus(journeyStopForm.querySelector(".form-status"), error.message, true);
            });
        });
    }

    function loadActivities() {
        const form = document.getElementById("activityFilters");
        if (!form) return;
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            const data = new FormData(form);
            const minutes = Number(data.get("free_time") || 0);
            const params = new URLSearchParams({
                location: String(data.get("location") || ""),
                minutes: String(minutes),
                budget: String(data.get("budget") || 0),
                category: String(data.get("category") || "")
            });
            data.getAll("interests[]").forEach(function (interest) { params.append("interests[]", String(interest)); });
            const status = document.getElementById("matcherStatus");
            setStatus(status, "Finding matching activities…", false);
            request(form.action + "?" + params.toString()).then(function (result) {
                const container = document.getElementById("activityResults");
                container.innerHTML = result.activities.length ? result.activities.map(function (activity) {
                    const imageUrl = destinationImageUrl(activity.image_url);
                    const imageLocation = activity.place_name && activity.place_name !== activity.city
                        ? activity.place_name + " near " + activity.city
                        : activity.city;
                    const image = imageUrl
                        ? '<img class="activity-photo" src="' + escapeHtml(imageUrl) + '" alt="Photograph of ' +
                            escapeHtml(imageLocation) + ', ' + escapeHtml(activity.state_or_ut) + '" loading="lazy">'
                        : '<div class="activity-photo-unavailable">Photograph unavailable</div>';
                    return '<div class="col-md-6"><article class="activity-card">' + image + '<div class="activity-content">' +
                        '<span class="activity-category">' + escapeHtml(activity.category_name) + '</span><h2>' + escapeHtml(activity.title) + '</h2>' +
                        '<p>' + escapeHtml(activity.description) + '</p><div class="activity-facts"><span>◷ ' + escapeHtml(activity.duration_minutes) +
                        ' min</span><span>₹' + Number(activity.estimated_cost).toLocaleString("en-IN") + '</span><span>⌖ ' +
                        escapeHtml(activity.city) + ', ' + escapeHtml(activity.state_or_ut) + '</span></div>' +
                        (activity.best_time ? '<small class="activity-best-time">Suitable time: ' + escapeHtml(activity.best_time) + '</small>' : '') +
                        '<button type="button" class="btn btn-link-quest" data-activity-save="' + activity.id + '"' +
                        (Number(activity.saved) ? ' disabled' : '') + '>' + (Number(activity.saved) ? '♡ Saved' : '♡ Save activity') +
                        '</button></div></article></div>';
                }).join("") : "";
                container.querySelectorAll(".activity-photo").forEach(function (image) {
                    image.addEventListener("error", function () {
                        const unavailable = document.createElement("div");
                        unavailable.className = "activity-photo-unavailable";
                        unavailable.textContent = "Photograph unavailable";
                        image.replaceWith(unavailable);
                    });
                });
                container.querySelectorAll("[data-activity-save]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({
                            endpoint: endpoint("/backend/user/saved-discovery-process.php"),
                            action: "save",
                            item_type: "activity",
                            item_id: button.dataset.activitySave
                        }).then(function () {
                            button.textContent = "♡ Saved";
                            button.disabled = true;
                        }).catch(function (error) { window.alert(error.message); });
                    });
                });
                document.getElementById("activityCount").textContent = result.activities.length;
                document.getElementById("activitiesEmpty").classList.toggle("d-none", result.activities.length > 0);
                setStatus(status, result.activities.length + " matching activity suggestion" + (result.activities.length === 1 ? "" : "s") + ".", false);
            }).catch(function (error) { setStatus(status, error.message, true); });
        });
    }

    function loadUsefulStops() {
        const form = document.getElementById("usefulStopsFilter");
        const container = document.getElementById("usefulStopsResults");
        if (!form || !container) return;

        function search() {
            const params = new URLSearchParams(new FormData(form));
            setStatus(form.querySelector(".form-status"), "Searching sourced stop listings…", false);
            request(form.action + "?" + params.toString()).then(function (result) {
                const category = form.elements.namedItem("category");
                const selectedCategory = category.value;
                category.innerHTML = '<option value="">All stop categories</option>' + result.categories.map(function (name) {
                    return '<option value="' + escapeHtml(name) + '">' + escapeHtml(name) + '</option>';
                }).join("");
                category.value = selectedCategory;
                const cards = result.stops.map(function (stop) {
                    const imageUrl = destinationImageUrl(stop.image_url);
                    const image = imageUrl
                        ? '<img class="stop-photo" src="' + escapeHtml(imageUrl) + '" alt="Photograph of ' + escapeHtml(stop.name) +
                            ' in ' + escapeHtml(stop.city) + ', ' + escapeHtml(stop.state_or_ut) + '" loading="lazy">'
                        : '<div class="stop-photo-unavailable">Photograph not available</div>';
                    const contact = stop.contact_phone
                        ? '<p><strong>Contact:</strong> <a href="tel:' + encodeURIComponent(stop.contact_phone) + '">' + escapeHtml(stop.contact_phone) + '</a></p>'
                        : '';
                    const distance = stop.distance_km == null ? '' : '<small>Recorded distance: ' + escapeHtml(stop.distance_km) + ' km</small>';
                    return '<article class="stop-card">' + image +
                        '<div class="stop-card-top"><span>' + escapeHtml(stop.category_name) + '</span><span aria-hidden="true">⌖</span></div>' +
                        '<h2>' + escapeHtml(stop.name) + '</h2><p>' + escapeHtml(stop.description) + '</p>' +
                        '<div class="stop-sample"><strong>' + escapeHtml(stop.address) + '</strong><small>' +
                        escapeHtml(stop.city) + ', ' + escapeHtml(stop.state_or_ut) + '</small>' + distance + '</div>' +
                        contact + (stop.destination_name ? '<small>Associated destination: ' + escapeHtml(stop.destination_name) + '</small>' : '') +
                        '<div class="stop-card-actions"><a href="' + escapeHtml(stop.source_url) +
                        '" target="_blank" rel="noopener noreferrer">Source: ' + escapeHtml(stop.source_name) + '</a>' +
                        '<button type="button" class="btn btn-link-quest" data-stop-save="' + stop.id + '"' +
                        (Number(stop.saved) ? ' disabled' : '') + '>' + (Number(stop.saved) ? '♡ Saved' : '♡ Save stop') +
                        '</button></div></article>';
                });
                container.innerHTML = cards.join("") || '<div class="empty-state">No stored suggestions match that search. Try another Indian city or destination.</div>';
                container.querySelectorAll(".stop-photo").forEach(function (image) {
                    image.addEventListener("error", function () {
                        const unavailable = document.createElement("div");
                        unavailable.className = "stop-photo-unavailable";
                        unavailable.textContent = "Photograph not available";
                        image.replaceWith(unavailable);
                    });
                });
                container.querySelectorAll("[data-stop-save]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        csrfPost({
                            endpoint: endpoint("/backend/user/saved-discovery-process.php"),
                            action: "save",
                            item_type: "stop",
                            item_id: button.dataset.stopSave
                        }).then(function () {
                            button.textContent = "♡ Saved";
                            button.disabled = true;
                        }).catch(function (error) { window.alert(error.message); });
                    });
                });
                setStatus(form.querySelector(".form-status"), cards.length + " stop listing" + (cards.length === 1 ? "" : "s") + " found.", false);
            }).catch(function (error) {
                container.innerHTML = '<div class="empty-state">' + escapeHtml(error.message) + '</div>';
                setStatus(form.querySelector(".form-status"), error.message, true);
            });
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            search();
        });
        search();
    }

    function fillJourneys() {
        const selects = document.querySelectorAll("#expenseJourney, #noteJourney, #expenseFilterJourney, #stopJourney");
        if (!selects.length) return;
        request(endpoint("/backend/user/journey-process.php")).then(function (result) {
            selects.forEach(function (select) {
                const emptyLabel = select.id === "expenseJourney" ? "No journey" : select.id === "expenseFilterJourney" ? "All journeys" : select.id === "stopJourney" ? "No journey filter" : "No journey selected";
                select.innerHTML = '<option value="">' + emptyLabel + '</option>' + result.journeys.map(function (journey) {
                    return '<option value="' + journey.id + '">' + escapeHtml(journey.title) + '</option>';
                }).join("");
            });
        }).catch(function (error) {
            selects.forEach(function (select) {
                select.innerHTML = '<option value="">' + escapeHtml(error.message) + '</option>';
                select.setCustomValidity(error.message);
            });
        });
    }

    function loadExpenses() {
        const tableBody = document.querySelector(".app-table tbody");
        if (!tableBody || !/expenses\.php$/i.test(location.pathname)) return;
        const journeyFilter = document.getElementById("expenseFilterJourney");
        const params = new URLSearchParams();
        if (journeyFilter && journeyFilter.value) params.set("journey_id", journeyFilter.value);
        const query = params.toString();
        request(endpoint("/backend/user/expenses-process.php") + (query ? "?" + query : "")).then(function (result) {
            tableBody.innerHTML = result.expenses.map(function (expense) {
                const date = expense.expense_date.split("-").reverse().join("-");
                return '<tr><td><strong>' + escapeHtml(expense.title) + '</strong><small>' + escapeHtml(expense.journey_title || "No journey") +
                    '</small><div><button type="button" class="btn btn-link-quest" data-edit-expense="' + encodeURIComponent(JSON.stringify(expense)) +
                    '">Edit</button><button type="button" class="btn btn-link-quest text-danger" data-delete-expense="' + expense.id + '">Delete</button></div></td>' +
                    '<td>' + escapeHtml(expense.category) + '</td><td>' + date + '</td><td class="text-end">₹' +
                    Number(expense.amount).toLocaleString("en-IN") + '</td></tr>';
            }).join("") || '<tr><td colspan="4">No expenses recorded yet.</td></tr>';
            const total = document.querySelector(".expense-summary strong");
            if (total) total.textContent = "₹" + Number(result.total).toLocaleString("en-IN");
            const filterSummary = document.querySelectorAll(".expense-summary strong")[1];
            if (filterSummary && journeyFilter) {
                filterSummary.textContent = journeyFilter.selectedOptions[0].textContent;
            }
            const categoryTotals = {};
            result.expenses.forEach(function (expense) {
                categoryTotals[expense.category] = (categoryTotals[expense.category] || 0) + Number(expense.amount);
            });
            const breakdown = document.querySelector(".expense-breakdown");
            if (breakdown) {
                breakdown.innerHTML = Object.keys(categoryTotals).sort().map(function (category) {
                    return '<span>' + escapeHtml(category) + '<b>₹' + categoryTotals[category].toLocaleString("en-IN") + '</b></span>';
                }).join("") || '<span>No expenses yet<b>₹0</b></span>';
            }
            tableBody.querySelectorAll("[data-delete-expense]").forEach(function (button) {
                button.addEventListener("click", function () {
                    if (!window.confirm("Delete this expense?")) return;
                    csrfPost({ endpoint: endpoint("/backend/user/expenses-process.php"), action: "delete", expense_id: button.dataset.deleteExpense })
                        .then(loadExpenses).catch(function (error) { window.alert(error.message); });
                });
            });
            tableBody.querySelectorAll("[data-edit-expense]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const expense = JSON.parse(decodeURIComponent(button.dataset.editExpense));
                    const form = document.querySelector('form[action$="/backend/user/expenses-process.php"]');
                    ["title", "category", "amount", "date", "journey_id", "notes"].forEach(function (name) {
                        const value = name === "date" ? expense.expense_date : expense[name];
                        form.elements.namedItem(name).value = value == null ? "" : value;
                    });
                    form.querySelector('[name="action"]').value = "update";
                    let id = form.querySelector('[name="expense_id"]');
                    if (!id) { id = document.createElement("input"); id.type = "hidden"; id.name = "expense_id"; form.appendChild(id); }
                    id.value = expense.id;
                    form.querySelector('button[type="submit"]').textContent = "Update expense";
                    form.scrollIntoView({ behavior: "smooth" });
                });
            });
        }).catch(function (error) { tableBody.innerHTML = '<tr><td colspan="4">' + escapeHtml(error.message) + '</td></tr>'; });
        if (journeyFilter && !journeyFilter.dataset.bound) {
            journeyFilter.dataset.bound = "true";
            journeyFilter.addEventListener("change", loadExpenses);
        }
    }

    function loadNotes() {
        const list = document.querySelector(".notes-list");
        if (!list) return;
        request(endpoint("/backend/user/notes-process.php")).then(function (result) {
            list.innerHTML = result.notes.map(function (note) {
                const date = note.updated_at.split(" ")[0].split("-").reverse().join("-");
                return '<article class="note-card"><div><span class="note-date">' + date + '</span><span class="note-journey">' +
                    escapeHtml(note.journey_title || "PERSONAL NOTE") + '</span></div><h2>' + escapeHtml(note.title) +
                    '</h2><p>' + escapeHtml(note.content) + '</p><div><button class="btn btn-link-quest" type="button" data-edit-note="' +
                    note.id + '">Edit</button><button class="btn btn-link-quest text-danger" type="button" data-delete-note="' +
                    note.id + '">Delete</button></div></article>';
            }).join("") || '<div class="empty-state">No travel notes yet. Create your first note.</div>';
            list.querySelectorAll("[data-delete-note]").forEach(function (button) {
                button.addEventListener("click", function () {
                    if (!window.confirm("Delete this travel note?")) return;
                    csrfPost({ endpoint: endpoint("/backend/user/notes-process.php"), action: "delete", note_id: button.dataset.deleteNote })
                        .then(loadNotes).catch(function (error) { window.alert(error.message); });
                });
            });
            list.querySelectorAll("[data-edit-note]").forEach(function (button) {
                button.addEventListener("click", function () {
                    request(endpoint("/backend/user/notes-process.php") + "?id=" + button.dataset.editNote).then(function (record) {
                        const note = record.notes[0];
                        document.getElementById("noteTitle").value = note.title;
                        document.getElementById("noteBody").value = note.content;
                        document.getElementById("noteJourney").value = note.journey_id || "";
                        const form = document.querySelector(".app-form");
                        form.querySelector('[name="action"]').value = "update";
                        let noteId = form.querySelector('[name="note_id"]');
                        if (!noteId) { noteId = document.createElement("input"); noteId.type = "hidden"; noteId.name = "note_id"; form.appendChild(noteId); }
                        noteId.value = note.id;
                        form.querySelector('button[type="submit"]').textContent = "Update note";
                        form.scrollIntoView({ behavior: "smooth" });
                    }).catch(function (error) { window.alert(error.message); });
                });
            });
        }).catch(function (error) { list.innerHTML = '<div class="empty-state">' + escapeHtml(error.message) + '</div>'; });
    }

    function loadUserDashboard() {
        const table = document.querySelector(".dashboard-lower .journey-row");
        if (!table) return;
        request(endpoint("/backend/user/journey-process.php")).then(function (result) {
            const panel = document.querySelector(".dashboard-lower .panel-card");
            const rows = result.journeys.slice(0, 3).map(function (journey) {
                return '<div class="journey-row"><span class="journey-thumb thumb-kerala"></span><div class="journey-details"><strong>' +
                    escapeHtml(journey.title) + '</strong><span>' + escapeHtml(journey.start_location) + ' → ' + escapeHtml(journey.destination) +
                    ' · ' + journey.departure_date.split("-").reverse().join("-") + '</span></div><span class="journey-status">' +
                    escapeHtml(journey.status) + '</span></div>';
            }).join("");
            panel.querySelectorAll(".journey-row").forEach(function (row) { row.remove(); });
            panel.insertAdjacentHTML("beforeend", rows || '<p class="inline-note">No journeys yet. Start planning one.</p>');
        }).catch(function (error) {
            const panel = document.querySelector(".dashboard-lower .panel-card");
            if (panel) panel.insertAdjacentHTML("beforeend", '<p class="inline-note text-danger">' + escapeHtml(error.message) + '</p>');
        });
    }

    function renderJourneys(journeys) {
        const container = document.getElementById("journeyList");
        if (!container) return;
        if (!journeys.length) {
            container.innerHTML = '<div class="empty-state">No journeys yet. Create a journey to get started.</div>';
            return;
        }
        container.innerHTML = journeys.map(function (journey) {
            return '<article class="saved-journey-card"><span class="journey-thumb thumb-kerala"></span><div><strong>' +
                escapeHtml(journey.title) + '</strong><p>' + escapeHtml(journey.start_location) + ' → ' + escapeHtml(journey.destination) +
                '</p><small>' + journey.departure_date.split("-").reverse().join("-") + ' – ' + journey.return_date.split("-").reverse().join("-") +
                ' · ' + escapeHtml(journey.travellers) + ' travellers</small><b>Budget: ₹' +
                Number(journey.estimated_budget).toLocaleString("en-IN") + '</b><div class="journey-actions"><button type="button" class="btn btn-link-quest" data-edit-journey="' +
                journey.id + '">Edit</button><button type="button" class="btn btn-link-quest" data-show-stops="' + journey.id +
                '">View stops (' + escapeHtml(journey.stop_count) + ')</button><button type="button" class="btn btn-link-quest text-danger" data-delete-journey="' +
                journey.id + '">Delete journey</button></div></div></article>';
        }).join("");
        container.querySelectorAll("[data-edit-journey]").forEach(function (button) {
            button.addEventListener("click", function () {
                request(endpoint("/backend/user/journey-process.php") + "?id=" + button.dataset.editJourney).then(function (result) {
                    const journey = result.journeys[0];
                    if (!journey) throw new Error("Journey not found.");
                    const form = document.querySelector('form[action$="/backend/user/journey-process.php"]');
                    const fields = {
                        title: journey.title,
                        start_city: journey.start_location.split(",")[0].trim(),
                        start_state: journey.start_location.split(",").slice(1).join(",").trim(),
                        destination_city: journey.destination.split(",")[0].trim(),
                        destination_state: journey.destination.split(",").slice(1).join(",").trim(),
                        departure_date: journey.departure_date,
                        return_date: journey.return_date,
                        travellers: journey.travellers,
                        budget: journey.estimated_budget
                    };
                    Object.keys(fields).forEach(function (name) { form.elements.namedItem(name).value = fields[name]; });
                    form.querySelector('[name="action"]').value = "update";
                    let id = form.querySelector('[name="journey_id"]');
                    if (!id) { id = document.createElement("input"); id.type = "hidden"; id.name = "journey_id"; form.appendChild(id); }
                    id.value = journey.id;
                    form.querySelector('button[type="submit"]').textContent = "Update journey";
                    form.scrollIntoView({ behavior: "smooth" });
                }).catch(function (error) { window.alert(error.message); });
            });
        });
        container.querySelectorAll("[data-show-stops]").forEach(function (button) {
            button.addEventListener("click", function () {
                const card = button.closest(".saved-journey-card");
                request(endpoint("/backend/user/journey-process.php") + "?stops_for=" + button.dataset.showStops).then(function (result) {
                    let list = card.querySelector(".journey-stop-list");
                    if (!list) { list = document.createElement("div"); list.className = "journey-stop-list"; card.appendChild(list); }
                    list.innerHTML = result.stops.length ? result.stops.map(function (stop) {
                        return "<p>" + escapeHtml(stop.name) + " · " + escapeHtml(stop.city) + ", " + escapeHtml(stop.state_or_ut) +
                            (stop.visit_date ? " · " + stop.visit_date.split("-").reverse().join("-") : "") +
                            ' <button class="btn btn-link-quest text-danger" type="button" data-remove-stop="' + stop.id +
                            '" data-journey-id="' + button.dataset.showStops + '">Remove</button></p>';
                    }).join("") : "<p>No stops added yet. Use Explore places to add a destination.</p>";
                    list.querySelectorAll("[data-remove-stop]").forEach(function (removeButton) {
                        removeButton.addEventListener("click", function () {
                            csrfPost({ endpoint: endpoint("/backend/user/journey-process.php"), action: "remove_stop", journey_id: removeButton.dataset.journeyId, stop_id: removeButton.dataset.removeStop })
                                .then(function () { button.click(); }).catch(function (error) { window.alert(error.message); });
                        });
                    });
                }).catch(function (error) { window.alert(error.message); });
            });
        });
        container.querySelectorAll("[data-delete-journey]").forEach(function (button) {
            button.addEventListener("click", function () {
                if (!window.confirm("Delete this journey? Associated stops or records may prevent deletion.")) return;
                csrfPost({ endpoint: endpoint("/backend/user/journey-process.php"), action: "delete", journey_id: button.dataset.deleteJourney })
                    .then(function () { loadJourneyList(); }).catch(function (error) { window.alert(error.message); });
            });
        });
    }

    function loadJourneyList() {
        const container = document.getElementById("journeyList");
        if (!container) return;
        request(endpoint("/backend/user/journey-process.php"))
            .then(function (result) { renderJourneys(result.journeys); })
            .catch(function (error) { container.innerHTML = '<div class="empty-state">' + escapeHtml(error.message) + '</div>'; });
    }

    function loadUserSummary() {
        const cards = document.querySelectorAll(".stat-row .stat-card");
        if (cards.length < 4) return;
        Promise.all([
            request(endpoint("/backend/user/journey-process.php")),
            request(endpoint("/backend/user/places-process.php?saved=1")),
            request(endpoint("/backend/user/notes-process.php"))
        ]).then(function (results) {
            const totalBudget = results[0].journeys.reduce(function (sum, journey) {
                return sum + Number(journey.estimated_budget);
            }, 0);
            const values = [results[0].journeys.length, results[1].places.length, "₹" + totalBudget.toLocaleString("en-IN"), results[2].notes.length];
            values.forEach(function (value, index) {
                cards[index].querySelector("strong").textContent = value;
                const note = cards[index].querySelector("span:last-child");
                if (note) note.textContent = index === 2 ? "Across your journeys" : "Your account";
            });
        }).catch(function (error) {
            const note = cards[0].querySelector("span:last-child");
            if (note) note.textContent = error.message;
        });
    }

    function loadAdminUsers() {
        const tableBody = document.querySelector(".admin-user-table tbody");
        if (!tableBody) return;
        request(endpoint("/backend/admin/user-management.php"))
            .then(function (result) {
                tableBody.innerHTML = result.users.map(function (user) {
                    const joined = user.created_at.split(" ")[0].split("-").reverse().join("-");
                    const nextStatus = user.status === "active" ? "inactive" : "active";
                    return '<tr data-admin-user><td><span class="user-initial">' + escapeHtml(user.name.charAt(0).toUpperCase()) +
                        '</span><strong>' + escapeHtml(user.name) + '</strong></td><td>' + escapeHtml(user.email) +
                        '</td><td>' + joined + '</td><td>' + escapeHtml(user.journey_count) + '</td><td><span class="status-chip ' +
                        (user.status === "active" ? "" : "status-muted") + '">' + escapeHtml(user.status) +
                        '</span></td><td><form action="' + endpoint("/backend/admin/user-management.php") + '" method="post">' +
                        '<input type="hidden" name="csrf_token" value="' + escapeHtml(csrfToken) + '">' +
                        '<input type="hidden" name="action" value="status"><input type="hidden" name="user_id" value="' + user.id +
                        '"><input type="hidden" name="status" value="' + nextStatus + '">' +
                        '<button class="btn btn-link-quest" type="submit">' + (user.status === "active" ? "Deactivate" : "Activate") +
                        '</button></form></td></tr>';
                }).join("");
                bindAdminSearch("adminUserSearch", "[data-admin-user]");
            })
            .catch(function (error) { tableBody.innerHTML = '<tr><td colspan="6">' + escapeHtml(error.message) + '</td></tr>'; });
    }

    function loadAdminPlaces() {
        const tableBody = document.querySelector("[data-admin-places]");
        if (!tableBody) return;
        request(endpoint("/backend/admin/places-management.php"))
            .then(function (result) {
                tableBody.innerHTML = result.places.map(function (place) {
                    return '<tr data-admin-place data-hidden-gem="' + Number(place.is_hidden_gem) +
                        '"><td><strong>' + escapeHtml(place.name) + '</strong></td><td>' + escapeHtml(place.state_or_ut) +
                        '</td><td>' + escapeHtml(place.region) + '</td><td>' + escapeHtml(place.category_name) +
                        '</td><td><button class="btn btn-link-quest" type="button" data-edit-place="' + encodeURIComponent(JSON.stringify(place)) +
                        '">Edit</button><form class="d-inline" method="post" action="' + endpoint("/backend/admin/places-management.php") +
                        '"><input type="hidden" name="csrf_token" value="' + escapeHtml(csrfToken) +
                        '"><input type="hidden" name="action" value="delete"><input type="hidden" name="place_id" value="' +
                        place.id + '"><button class="btn btn-link-quest text-danger" type="submit">Delete</button></form></td></tr>';
                }).join("");
                tableBody.querySelectorAll("[data-edit-place]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        const place = JSON.parse(decodeURIComponent(button.dataset.editPlace));
                        const form = document.getElementById("placeAdminForm");
                        Object.keys({ name: 1, city: 1, state_or_ut: 1, region: 1, category_id: 1, description: 1, image_url: 1, estimated_cost: 1, suggested_duration: 1, distance: 1, best_visiting_season: 1, discovery_status: 1, things_to_do: 1, travel_tips: 1, access_notes: 1 }).forEach(function (key) {
                            const input = form.elements.namedItem(key);
                            if (input) input.value = place[key] == null ? "" : place[key];
                        });
                        const gemToggle = form.elements.namedItem("is_hidden_gem");
                        if (gemToggle) gemToggle.checked = Number(place.is_hidden_gem) === 1;
                        const categorySelect = form.elements.namedItem("category_id");
                        if (categorySelect && !Array.from(categorySelect.options).some(function (option) { return option.value === String(place.category_id); })) {
                            categorySelect.add(new Option(place.category_name, place.category_id));
                        }
                        if (categorySelect) categorySelect.value = place.category_id;
                        form.elements.namedItem("action").value = "update";
                        form.elements.namedItem("place_id").value = place.id;
                        form.scrollIntoView({ behavior: "smooth" });
                    });
                });
                const search = document.getElementById("adminPlaceSearch");
                const gemFilter = document.getElementById("adminGemFilter");
                const applyFilters = function () {
                    const query = search ? search.value.trim().toLowerCase() : "";
                    const gemValue = gemFilter ? gemFilter.value : "";
                    tableBody.querySelectorAll("[data-admin-place]").forEach(function (row) {
                        const textMatch = row.textContent.toLowerCase().includes(query);
                        const gemMatch = gemValue === "" || row.dataset.hiddenGem === gemValue;
                        row.classList.toggle("d-none", !textMatch || !gemMatch);
                    });
                };
                if (search) search.addEventListener("input", applyFilters);
                if (gemFilter) gemFilter.addEventListener("change", applyFilters);
            })
            .catch(function (error) { tableBody.innerHTML = '<tr><td colspan="5">' + escapeHtml(error.message) + '</td></tr>'; });
    }

    function loadAdminCategories() {
        const list = document.querySelector("[data-admin-categories]");
        const selectors = document.querySelectorAll("[data-category-options]");
        if (!list && !selectors.length) return;
        request(endpoint("/backend/admin/category-management.php")).then(function (result) {
            if (list) {
                list.innerHTML = result.categories.map(function (category) {
                    return '<div><span class="category-mark">' + escapeHtml(category.category_name.charAt(0).toUpperCase()) +
                        '</span><strong>' + escapeHtml(category.category_name) + '</strong><span class="category-type">' +
                        escapeHtml(category.category_type) + '</span><small>' + (Number(category.place_count) + Number(category.activity_count)) +
                        ' records</small><button type="button" class="btn btn-link-quest" data-edit-category="' +
                        encodeURIComponent(JSON.stringify(category)) + '">Edit</button></div>';
                }).join("");
                list.querySelectorAll("[data-edit-category]").forEach(function (button) {
                    const row = button.closest("div");
                    if (row && !row.querySelector("form")) {
                        const category = JSON.parse(decodeURIComponent(button.dataset.editCategory));
                        const form = document.createElement("form");
                        form.method = "post";
                        form.action = endpoint("/backend/admin/category-management.php");
                        form.dataset.deleteCategory = "true";
                        form.innerHTML = '<input type="hidden" name="csrf_token" value="' + escapeHtml(csrfToken) +
                            '"><input type="hidden" name="action" value="delete"><input type="hidden" name="category_id" value="' +
                            escapeHtml(category.id) + '"><button class="btn btn-link-quest text-danger" type="submit">Delete</button>';
                        row.appendChild(form);
                    }
                });
                list.querySelectorAll("[data-edit-category]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        const category = JSON.parse(decodeURIComponent(button.dataset.editCategory));
                        const form = document.getElementById("categoryAdminForm");
                        form.elements.namedItem("category_name").value = category.category_name;
                        form.elements.namedItem("category_type").value = category.category_type;
                        form.elements.namedItem("category_id").value = category.id;
                        form.elements.namedItem("action").value = "update";
                        form.scrollIntoView({ behavior: "smooth" });
                    });
                });
                list.querySelectorAll("[data-delete-category]").forEach(function (form) {
                    form.addEventListener("submit", function (event) {
                        if (!window.confirm("Delete this category? It cannot be removed while places or activities use it.")) event.preventDefault();
                    });
                });
            }
            selectors.forEach(function (select) {
                const type = select.dataset.categoryOptions;
                const items = result.categories.filter(function (category) { return category.category_type === type; });
                select.innerHTML = '<option value="">Choose category</option>' + items.map(function (category) {
                    return '<option value="' + category.id + '">' + escapeHtml(category.category_name) + '</option>';
                }).join("");
            });
        }).catch(function (error) {
            if (list) list.innerHTML = '<p class="inline-note">' + escapeHtml(error.message) + '</p>';
            selectors.forEach(function (select) {
                select.innerHTML = '<option value="">' + escapeHtml(error.message) + '</option>';
            });
        });
    }

    function loadAdminActivities() {
        const body = document.querySelector("[data-admin-activities]");
        if (!body) return;
        request(endpoint("/backend/admin/activities-management.php")).then(function (result) {
            body.innerHTML = result.activities.map(function (activity) {
                return '<tr data-admin-activity data-category="' + escapeHtml(activity.category_name) + '"><td><strong>' + escapeHtml(activity.title) + '</strong></td><td>' + escapeHtml(activity.place_name) +
                    '</td><td>' + escapeHtml(activity.category_name) + '</td><td>' + escapeHtml(activity.duration_minutes) +
                    ' min</td><td>₹' + Number(activity.estimated_cost).toLocaleString("en-IN") + '</td><td><button class="btn btn-link-quest" type="button" data-edit-activity="' +
                    encodeURIComponent(JSON.stringify(activity)) + '">Edit</button><form class="d-inline" method="post" action="' +
                    endpoint("/backend/admin/activities-management.php") + '"><input type="hidden" name="csrf_token" value="' +
                    escapeHtml(csrfToken) + '"><input type="hidden" name="action" value="delete"><input type="hidden" name="activity_id" value="' +
                    activity.id + '"><button class="btn btn-link-quest text-danger" type="submit">Delete</button></form></td></tr>';
            }).join("");
            body.querySelectorAll("[data-edit-activity]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const activity = JSON.parse(decodeURIComponent(button.dataset.editActivity));
                    const form = document.getElementById("activityAdminForm");
                    ["title", "place_id", "category_id", "description", "duration_minutes", "estimated_cost", "interests", "image_url", "best_time"].forEach(function (key) {
                        form.elements.namedItem(key).value = activity[key] == null ? "" : activity[key];
                    });
                    [["place_id", activity.place_name + " — " + activity.city], ["category_id", activity.category_name]].forEach(function (item) {
                        const select = form.elements.namedItem(item[0]);
                        const value = String(activity[item[0]]);
                        if (!Array.from(select.options).some(function (option) { return option.value === value; })) {
                            select.add(new Option(item[1], value));
                        }
                        select.value = value;
                    });
                    form.elements.namedItem("action").value = "update";
                    form.elements.namedItem("activity_id").value = activity.id;
                    form.scrollIntoView({ behavior: "smooth" });
                });
            });
            const categoryFilter = document.getElementById("adminActivityCategoryFilter");
            if (categoryFilter) {
                const categories = Array.from(new Set(result.activities.map(function (activity) { return activity.category_name; }))).sort();
                categoryFilter.innerHTML = '<option value="">All categories</option>' + categories.map(function (name) {
                    return '<option value="' + escapeHtml(name) + '">' + escapeHtml(name) + '</option>';
                }).join("");
            }
            const search = document.getElementById("adminActivitySearch");
            const applyFilters = function () {
                const query = search ? search.value.trim().toLowerCase() : "";
                const category = categoryFilter ? categoryFilter.value : "";
                body.querySelectorAll("[data-admin-activity]").forEach(function (row) {
                    row.classList.toggle("d-none",
                        !row.textContent.toLowerCase().includes(query) ||
                        (category !== "" && row.dataset.category !== category));
                });
            };
            if (search) search.addEventListener("input", applyFilters);
            if (categoryFilter) categoryFilter.addEventListener("change", applyFilters);
        }).catch(function (error) { body.innerHTML = '<tr><td colspan="6">' + escapeHtml(error.message) + '</td></tr>'; });
    }

    function loadAdminStops() {
        const body = document.querySelector("[data-admin-stops]");
        if (!body) return;
        request(endpoint("/backend/admin/useful-stops-management.php")).then(function (result) {
            const categoryOptions = '<option value="">Choose category</option>' + result.categories.map(function (category) {
                return '<option value="' + category.id + '">' + escapeHtml(category.category_name) + '</option>';
            }).join("");
            const formCategory = document.querySelector("[data-stop-category-options]");
            const filter = document.getElementById("adminStopCategoryFilter");
            if (formCategory) formCategory.innerHTML = categoryOptions;
            if (filter) {
                const selected = filter.value;
                filter.innerHTML = '<option value="">All categories</option>' + result.categories.map(function (category) {
                    return '<option value="' + escapeHtml(category.category_name) + '">' + escapeHtml(category.category_name) + '</option>';
                }).join("");
                filter.value = selected;
            }
            body.innerHTML = result.stops.map(function (stop) {
                return '<tr data-admin-stop data-category="' + escapeHtml(stop.category_name) + '"><td><strong>' +
                    escapeHtml(stop.name) + '</strong><small>' + escapeHtml(stop.address) + '</small></td><td>' +
                    escapeHtml(stop.city) + ', ' + escapeHtml(stop.state_or_ut) + '</td><td>' +
                    escapeHtml(stop.category_name) + '</td><td><button class="btn btn-link-quest" type="button" data-edit-stop="' +
                    encodeURIComponent(JSON.stringify(stop)) + '">Edit</button><form class="d-inline" method="post" action="' +
                    endpoint("/backend/admin/useful-stops-management.php") + '"><input type="hidden" name="csrf_token" value="' +
                    escapeHtml(csrfToken) + '"><input type="hidden" name="action" value="delete"><input type="hidden" name="stop_id" value="' +
                    stop.id + '"><button class="btn btn-link-quest text-danger" type="submit">Delete</button></form></td></tr>';
            }).join("") || '<tr><td colspan="4">No sourced useful stops have been added.</td></tr>';
            body.querySelectorAll("[data-edit-stop]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const stop = JSON.parse(decodeURIComponent(button.dataset.editStop));
                    const form = document.getElementById("usefulStopAdminForm");
                    ["name", "category_id", "address", "city", "state_or_ut", "place_id", "description",
                        "source_url", "source_name", "image_url", "contact_phone", "distance_km"].forEach(function (key) {
                        const field = form.elements.namedItem(key);
                        if (field) field.value = stop[key] == null ? "" : stop[key];
                    });
                    form.elements.namedItem("action").value = "update";
                    form.elements.namedItem("stop_id").value = stop.id;
                    form.scrollIntoView({ behavior: "smooth" });
                });
            });
            const search = document.getElementById("adminStopSearch");
            const applyFilters = function () {
                const query = search ? search.value.trim().toLowerCase() : "";
                const category = filter ? filter.value : "";
                body.querySelectorAll("[data-admin-stop]").forEach(function (row) {
                    row.classList.toggle("d-none",
                        !row.textContent.toLowerCase().includes(query) ||
                        (category !== "" && row.dataset.category !== category));
                });
            };
            if (search) search.addEventListener("input", applyFilters);
            if (filter) filter.addEventListener("change", applyFilters);
        }).catch(function (error) { body.innerHTML = '<tr><td colspan="4">' + escapeHtml(error.message) + '</td></tr>'; });
    }

    function loadAdminDashboard() {
        if (!document.querySelector(".admin-stats") && !document.querySelector("[data-admin-regions]")) return;
        request(endpoint("/backend/admin/dashboard-data.php")).then(function (result) {
            const counts = [result.counts.users, result.counts.places, result.counts.activities, result.counts.journeys];
            const stats = document.querySelectorAll(".admin-stats .stat-card");
            counts.forEach(function (count, index) {
                if (stats[index]) stats[index].querySelector("strong").textContent = count;
                if (stats[index]) stats[index].querySelector("span:last-child").textContent = "Current database";
            });
            const body = document.querySelector("[data-admin-recent-users]");
            if (body) body.innerHTML = result.recent_users.map(function (user) {
                return '<tr><td><strong>' + escapeHtml(user.name) + '</strong></td><td>' + escapeHtml(user.email) +
                    '</td><td>' + user.created_at.split(" ")[0].split("-").reverse().join("-") +
                    '</td><td><span class="status-chip">' + escapeHtml(user.status) + '</span></td></tr>';
            }).join("") || '<tr><td colspan="4">No accounts have registered yet.</td></tr>';
            renderAdminRegions(result.regions);
            renderAdminReportCategories(result.categories);
        }).catch(function (error) {
            document.querySelectorAll(".admin-stats .stat-card span:last-child").forEach(function (label) {
                label.textContent = error.message;
            });
        });
    }

    function bindAdminSearch(inputId, selector) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener("input", function () {
            const query = input.value.trim().toLowerCase();
            document.querySelectorAll(selector).forEach(function (row) {
                row.classList.toggle("d-none", !row.textContent.toLowerCase().includes(query));
            });
        });
    }

    function renderAdminRegions(regions) {
        const containers = document.querySelectorAll("[data-admin-regions]");
        if (!containers.length) return;
        const maximum = Math.max(1, ...regions.map(function (region) { return Number(region.place_count); }));
        const content = regions.map(function (region) {
            const count = Number(region.place_count);
            const percent = Math.round(count / maximum * 100);
            return '<div><span>' + escapeHtml(region.region) + '</span><b><i style="width:' + percent + '%"></i></b><strong>' + count + '</strong></div>';
        }).join("") || '<p class="inline-note">No destinations are stored yet.</p>';
        containers.forEach(function (container) { container.innerHTML = content; });
    }

    function renderAdminReportCategories(categories) {
        const list = document.querySelector("[data-admin-report-categories]");
        if (!list) return;
        list.innerHTML = categories.map(function (category) {
            return '<div><span class="report-dot dot-green"></span><span>' + escapeHtml(category.category_name) +
                '</span><strong>' + escapeHtml(category.place_count) + '</strong></div>';
        }).join("") || '<p class="inline-note">No destination categories have places.</p>';
    }

    function loadAdminJourneys() {
        const body = document.querySelector("[data-admin-journeys]");
        if (!body) return;
        request(endpoint("/backend/admin/journey-management.php")).then(function (result) {
            body.innerHTML = result.journeys.map(function (journey) {
                return '<tr data-admin-journey><td><strong>' + escapeHtml(journey.title) + '</strong></td><td>' +
                    escapeHtml(journey.user_name) + '</td><td>' + escapeHtml(journey.start_location) + ' → ' +
                    escapeHtml(journey.destination) + '</td><td>' + journey.departure_date.split("-").reverse().join("-") +
                    ' – ' + journey.return_date.split("-").reverse().join("-") + '</td><td>₹' +
                    Number(journey.estimated_budget).toLocaleString("en-IN") + '</td><td><span class="status-chip">' +
                    escapeHtml(journey.status) + '</span></td></tr>';
            }).join("") || '<tr><td colspan="6">No journeys have been created yet.</td></tr>';
            bindAdminSearch("adminJourneySearch", "[data-admin-journey]");
        }).catch(function (error) { body.innerHTML = '<tr><td colspan="6">' + escapeHtml(error.message) + '</td></tr>'; });
    }

    function loadAdminPlaceOptions() {
        const selectors = document.querySelectorAll("[data-place-options]");
        if (!selectors.length) return;
        request(endpoint("/backend/admin/places-management.php")).then(function (result) {
            selectors.forEach(function (select) {
                select.innerHTML = '<option value="">Choose destination</option>' + result.places.map(function (place) {
                    return '<option value="' + place.id + '">' + escapeHtml(place.name + " — " + place.city) + '</option>';
                }).join("");
            });
        }).catch(function (error) {
            selectors.forEach(function (select) { select.innerHTML = '<option value="">' + escapeHtml(error.message) + '</option>'; });
        });
    }

    if (document.getElementById("placeGrid")) loadPlaces();
    loadActivities();
    loadUsefulStops();
    fillJourneys();
    loadExpenses();
    loadNotes();
    loadUserDashboard();
    loadJourneyList();
    loadUserSummary();
    loadAdminUsers();
    loadAdminPlaces();
    loadAdminStops();
    loadAdminCategories();
    loadAdminActivities();
    loadAdminDashboard();
    loadAdminJourneys();
    loadAdminPlaceOptions();
    loadSavedDiscoveries();
})();
