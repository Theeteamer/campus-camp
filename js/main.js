(function () {
    "use strict";

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const bypassedForms = new WeakSet();
    let navigationPending = false;
    let documentLeaving = false;
    let pendingDeleteLink = null;
    let fragmentNavigationUsed = false;
    let houseTypeRequest = 0;
    let loadingCloseTimeout = null;

    const header = document.querySelector(".site-header, .admin-header");
    const footer = document.querySelector("body > footer");
    const loadingPanel = document.createElement("div");
    loadingPanel.className = "page-loading-panel";
    loadingPanel.setAttribute("role", "status");
    loadingPanel.setAttribute("aria-label", "Loading");
    loadingPanel.setAttribute("aria-live", "polite");
    loadingPanel.setAttribute("aria-hidden", "true");

    const spinner = document.createElement("span");
    spinner.className = "page-loading-spinner";
    spinner.setAttribute("aria-hidden", "true");

    for (let segment = 0; segment < 9; segment += 1) {
        const loadingSegment = document.createElement("span");
        loadingSegment.className = "page-loading-segment";
        loadingSegment.style.setProperty("--segment-index", String(segment));
        spinner.appendChild(loadingSegment);
    }

    loadingPanel.appendChild(spinner);
    const initialMain = document.querySelector("main");
    (initialMain || document.body).appendChild(loadingPanel);

    const deleteDialog = document.createElement("dialog");
    deleteDialog.className = "admin-confirm-dialog";
    deleteDialog.setAttribute("aria-labelledby", "admin-confirm-title");
    deleteDialog.setAttribute("aria-describedby", "admin-confirm-message");

    const deleteForm = document.createElement("form");
    deleteForm.method = "dialog";
    deleteForm.className = "admin-confirm-form";

    const deleteTitle = document.createElement("h2");
    deleteTitle.id = "admin-confirm-title";
    deleteTitle.textContent = "Confirm deletion";

    const deleteMessage = document.createElement("p");
    deleteMessage.id = "admin-confirm-message";

    const deleteActions = document.createElement("div");
    deleteActions.className = "admin-confirm-actions";

    const cancelDelete = document.createElement("button");
    cancelDelete.type = "submit";
    cancelDelete.value = "cancel";
    cancelDelete.className = "cancel-button";
    cancelDelete.textContent = "Cancel";

    const confirmDelete = document.createElement("button");
    confirmDelete.type = "submit";
    confirmDelete.value = "confirm";
    confirmDelete.className = "admin-confirm-delete";
    confirmDelete.textContent = "Delete";

    deleteActions.append(cancelDelete, confirmDelete);
    deleteForm.append(deleteTitle, deleteMessage, deleteActions);
    deleteDialog.appendChild(deleteForm);
    document.body.appendChild(deleteDialog);

    deleteDialog.addEventListener("close", function () {
        const deleteLink = pendingDeleteLink;
        pendingDeleteLink = null;

        if (deleteDialog.returnValue !== "confirm" || !deleteLink) {
            return;
        }

        showLoading(function () {
            window.location.assign(deleteLink.href);
        });
    });

    function collectPageTargets() {
        const targets = [];
        const addTarget = function (element) {
            if (!targets.includes(element)) {
                targets.push(element);
            }
        };

        function collectContent(element) {
            Array.from(element.children).forEach(function (child) {
                if (child.classList.contains("page-loading-panel")) {
                    return;
                }

                if (child.classList.contains("container") ||
                    child.classList.contains("property-section") ||
                    child.classList.contains("legal-container") ||
                    child.tagName === "SECTION") {
                    collectContent(child);
                } else if (child.classList.contains("property-list")) {
                    Array.from(child.children).forEach(addTarget);
                } else if (child.classList.contains("admin-table-container")) {
                    const rows = child.querySelectorAll("tbody tr");
                    if (rows.length) {
                        rows.forEach(addTarget);
                    } else {
                        addTarget(child);
                    }
                } else if (child.classList.contains("admin-dashboard-table")) {
                    child.querySelectorAll(".admin-dashboard-row").forEach(addTarget);
                } else if (child.classList.contains("admin-form")) {
                    Array.from(child.children).forEach(addTarget);
                } else {
                    addTarget(child);
                }
            });
        }

        const headerContent = header && header.querySelector(".site-header-inner, .admin-header-inner");
        if (headerContent) {
            addTarget(headerContent);
        }

        const main = document.querySelector("main");
        if (main) {
            collectContent(main);
        }

        const loginBox = document.querySelector(".admin-login-box");
        if (loginBox) {
            addTarget(loginBox);
        }

        const footerContent = document.querySelector(".site-footer > .container");
        if (footerContent) {
            addTarget(footerContent);
        }

        return targets;
    }

    function animatePageIn() {
        if (reducedMotion) {
            return;
        }

        collectPageTargets().forEach(function (element, index) {
            element.animate(
                [
                    {opacity: 0, transform: "translateY(12px)"},
                    {opacity: 1, transform: "translateY(0)"}
                ],
                {
                    duration: 380,
                    delay: Math.min(index * 55, 660),
                    easing: "cubic-bezier(0.2, 0.7, 0.2, 1)",
                    fill: "both"
                }
            );
        });
    }

    animatePageIn();

    function updateLoadingPosition() {
        const viewportHeight = window.innerHeight;
        const headerBottom = header ? Math.max(0, header.getBoundingClientRect().bottom) : 0;
        let footerSpace = 0;

        if (footer) {
            const footerTop = footer.getBoundingClientRect().top;
            footerSpace = Math.min(
                Math.max(viewportHeight - footerTop, 0),
                Math.max(viewportHeight - headerBottom, 0)
            );
        }

        loadingPanel.style.setProperty("--loader-top", `${headerBottom}px`);
        loadingPanel.style.setProperty("--loader-bottom", `${footerSpace}px`);
    }

    updateLoadingPosition();
    window.addEventListener("resize", updateLoadingPosition);
    window.addEventListener("scroll", updateLoadingPosition, {passive: true});

    window.addEventListener("pagehide", function () {
        documentLeaving = true;
    });

    function hideLoading(animate) {
        navigationPending = false;

        if (loadingCloseTimeout !== null) {
            window.clearTimeout(loadingCloseTimeout);
            loadingCloseTimeout = null;
        }

        if (!animate) {
            loadingPanel.classList.remove("is-open");
            loadingPanel.setAttribute("aria-hidden", "true");
            return;
        }

        loadingPanel.classList.remove("is-open");
        loadingCloseTimeout = window.setTimeout(function () {
            loadingPanel.setAttribute("aria-hidden", "true");
            loadingCloseTimeout = null;
        }, 200);
    }

    window.addEventListener("pageshow", function () {
        documentLeaving = false;
        hideLoading(false);
    });

    function showLoading(continueNavigation) {
        if (navigationPending) {
            return false;
        }

        const loadingStartedAt = performance.now();
        navigationPending = true;
        updateLoadingPosition();
        if (loadingCloseTimeout !== null) {
            window.clearTimeout(loadingCloseTimeout);
            loadingCloseTimeout = null;
        }
        loadingPanel.removeAttribute("aria-hidden");
        loadingPanel.classList.add("is-open");

        (async function () {
            await new Promise(function (resolve) {
                window.requestAnimationFrame(resolve);
            });

            if (!navigationPending || documentLeaving) {
                return;
            }

            if (!continueNavigation) {
                hideLoading(true);
                return;
            }

            const navigation = continueNavigation();
            if (navigation && typeof navigation.then === "function") {
                function finishAfterMinimumDuration() {
                    const remainingDuration = Math.max(
                        0,
                        1000 - (performance.now() - loadingStartedAt)
                    );

                    window.setTimeout(function () {
                        if (navigationPending && !documentLeaving) {
                            hideLoading(true);
                        }
                    }, remainingDuration);
                }

                navigation.then(
                    finishAfterMinimumDuration,
                    function (error) {
                        console.error("Navigation did not complete.", error);
                        finishAfterMinimumDuration();
                    }
                );
            }
        })();

        return true;
    }

    function isAdminUrl(url) {
        return /\/admin(?:\/|$)/.test(url.pathname);
    }

    function canNavigatePartially(url) {
        return url.origin === window.location.origin &&
            isAdminUrl(url) === Boolean(document.querySelector(".admin-header"));
    }

    async function loadPartial(url, updateHistory, scrollY) {
        const destination = new URL(url, window.location.href);
        const currentMain = document.querySelector("main");

        if (!currentMain || !canNavigatePartially(destination)) {
            window.location.assign(destination.href);
            return;
        }

        try {
            const response = await fetch(destination.href, {
                headers: {
                    "X-Partial-Request": "1",
                    "Accept": "text/html"
                },
                credentials: "same-origin"
            });

            if (
                !response.ok ||
                response.redirected ||
                response.headers.get("X-Partial-Response") !== "main"
            ) {
                window.location.assign(destination.href);
                return;
            }

            const html = await response.text();
            const template = document.createElement("template");
            template.innerHTML = html;
            const nextMain = template.content.querySelector("main");

            if (!nextMain) {
                window.location.assign(destination.href);
                return;
            }

            if (updateHistory) {
                history.replaceState(
                    {...history.state, scrollY: window.scrollY},
                    "",
                    window.location.href
                );
                history.pushState({scrollY: 0}, "", destination.href);
            }

            nextMain.classList.add("page-content-loading");
            nextMain.appendChild(loadingPanel);
            currentMain.replaceWith(nextMain);
            updateLoadingPosition();
            fragmentNavigationUsed = true;

            const encodedTitle = response.headers.get("X-Partial-Title");
            if (encodedTitle) {
                document.title = decodeURIComponent(encodedTitle);
            }

            window.scrollTo(0, updateHistory ? 0 : Math.max(0, scrollY || 0));

            await Promise.all(Array.from(nextMain.querySelectorAll("img")).map(function (image) {
                if (typeof image.decode !== "function") {
                    return Promise.resolve();
                }

                return image.decode().catch(function (error) {
                    console.error("Unable to decode page image.", error);
                });
            }));

            if (document.fonts && document.fonts.ready) {
                await document.fonts.ready;
            }

            nextMain.classList.add("page-content-ready");
            await new Promise(function (resolve) {
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(resolve);
                });
            });
        } catch (error) {
            console.error("Partial page navigation failed; loading the full page instead.", error);
            window.location.assign(destination.href);
        }
    }

    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.method.toLowerCase() === "dialog") {
            return;
        }

        if (bypassedForms.has(form)) {
            bypassedForms.delete(form);
            return;
        }

        const target = event.submitter && event.submitter.formTarget
            ? event.submitter.formTarget
            : form.target;

        if (target && target.toLowerCase() !== "_self") {
            return;
        }

        if (form.method.toLowerCase() === "get" &&
            form.enctype.toLowerCase() !== "multipart/form-data") {
            event.preventDefault();

            const submitter = event.submitter;
            const action = submitter && submitter.hasAttribute("formaction")
                ? submitter.formAction
                : form.action;
            const destination = new URL(action, window.location.href);
            const formData = new FormData(form);

            if (submitter && submitter.name) {
                formData.append(submitter.name, submitter.value);
            }

            new URLSearchParams(formData).forEach(function (value, name) {
                destination.searchParams.append(name, value);
            });

            if (!canNavigatePartially(destination)) {
                window.location.assign(destination.href);
                return;
            }

            showLoading(function () {
                return loadPartial(destination.href, true, 0);
            });
            return;
        }

        event.preventDefault();

        showLoading(function () {
            bypassedForms.add(form);

            if (event.submitter) {
                form.requestSubmit(event.submitter);
            } else {
                form.requestSubmit();
            }
        });
    });

    document.addEventListener("click", function (event) {
        if (
            event.defaultPrevented ||
            event.button !== 0
        ) {
            return;
        }

        if (!(event.target instanceof Element)) {
            return;
        }

        const link = event.target.closest("a[href]");

        if (!link) {
            return;
        }

        if (link.hasAttribute("data-confirm-message")) {
            event.preventDefault();
            pendingDeleteLink = link;
            deleteMessage.textContent = link.dataset.confirmMessage;
            deleteDialog.returnValue = "";
            deleteDialog.showModal();
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        if (link.hasAttribute("download")) {
            return;
        }

        if (link.target && link.target.toLowerCase() !== "_self") {
            return;
        }

        const href = link.getAttribute("href");

        if (!href || href === "#" || href.startsWith("#")) {
            return;
        }

        const destination = new URL(link.href, window.location.href);

        if (
            (destination.protocol !== "http:" && destination.protocol !== "https:") ||
            !canNavigatePartially(destination) ||
            /\/(?:logout|delete_[^/]+)\.php$/i.test(destination.pathname)
        ) {
            return;
        }

        event.preventDefault();

        showLoading(function () {
            return loadPartial(destination.href, true, 0);
        });
    });

    window.addEventListener("popstate", function (event) {
        const destination = new URL(window.location.href);

        if (!canNavigatePartially(destination)) {
            window.location.reload();
            return;
        }

        showLoading(function () {
            return loadPartial(destination.href, false, event.state && event.state.scrollY);
        });
    });

    document.addEventListener("change", function (event) {
        if (!fragmentNavigationUsed || !(event.target instanceof HTMLSelectElement)) {
            return;
        }

        if (event.target.id !== "location") {
            return;
        }

        const houseTypeSelect = document.getElementById("house_type");
        if (!houseTypeSelect) {
            return;
        }

        const allTypes = JSON.parse(houseTypeSelect.dataset.allTypes || "[]");
        const location = event.target.value;
        const requestId = ++houseTypeRequest;

        function setOptions(types) {
            houseTypeSelect.replaceChildren(new Option("Any type", ""));
            types.forEach(function (type) {
                houseTypeSelect.add(new Option(type, type));
            });
        }

        if (!location) {
            setOptions(allTypes);
            return;
        }

        houseTypeSelect.replaceChildren(new Option("Loading...", "", true, true));
        fetch("get_house_types.php?location=" + encodeURIComponent(location))
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Unable to load house types (" + response.status + ").");
                }
                return response.json();
            })
            .then(function (types) {
                if (requestId === houseTypeRequest) {
                    setOptions(types);
                }
            })
            .catch(function (error) {
                if (requestId !== houseTypeRequest) {
                    return;
                }

                console.error("House type loading failed.", error);
                houseTypeSelect.replaceChildren(
                    new Option("Unable to load types; try again.", "", true, true)
                );
            });
    });

    document.addEventListener("click", function (event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        const galleryButton = event.target.closest("[data-gallery-action]");
        if (!galleryButton) {
            return;
        }

        const gallery = galleryButton.closest("[data-gallery-images]");
        if (!gallery) {
            return;
        }

        const images = JSON.parse(gallery.dataset.galleryImages || "[]");
        const image = gallery.querySelector(".gallery-image");
        const counter = document.getElementById("currentNumber");

        if (!image || images.length < 2) {
            return;
        }

        let index = Number(gallery.dataset.galleryIndex || "0");
        index = galleryButton.dataset.galleryAction === "next"
            ? (index + 1) % images.length
            : (index - 1 + images.length) % images.length;
        gallery.dataset.galleryIndex = String(index);
        image.src = images[index];

        if (counter) {
            counter.textContent = String(index + 1);
        }
    });

    document.addEventListener("keydown", function (event) {
        if (!fragmentNavigationUsed) {
            return;
        }

        const gallery = document.querySelector("[data-gallery-images]");
        if (!gallery) {
            return;
        }

        if (event.key === "ArrowLeft" || event.key === "ArrowRight") {
            const action = event.key === "ArrowRight" ? "next" : "previous";
            const button = gallery.querySelector('[data-gallery-action="' + action + '"]');
            if (button) {
                button.click();
            }
        } else if (event.key === "Escape") {
            const returnLink = gallery.querySelector(".gallery-return");
            if (returnLink) {
                returnLink.click();
            }
        }
    });
})();