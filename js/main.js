(function () {
    "use strict";

    const entryDuration = 500;
    const entryStagger = 60;
    const exitStartDelay = entryDuration + entryStagger * 4 + 100;
    const exitDuration = 380;
    const exitStagger = 30;
    const clearPause = 150;
    const dotSpacing = 10;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const buttonLinkSelector = [
        ".site-header a",
        ".admin-header a",
        ".search-button-link",
        ".go-button",
        ".browse-pictures-button",
        ".admin-button",
        ".cancel-button",
        ".back-button",
        ".back-link",
        ".call-button",
        ".whatsapp-button",
        ".admin-dashboard-button",
        ".admin-login-link",
        ".action-links a",
        ".delete-image-link",
        ".legal-return"
    ].join(",");
    const bypassedForms = new WeakSet();
    const bypassedLinks = new WeakSet();
    let navigationPending = false;
    let documentLeaving = false;
    let pendingDeleteLink = null;

    const header = document.querySelector(".site-header, .admin-header");
    const loadingPanel = document.createElement("div");
    loadingPanel.className = "page-loading-panel";
    loadingPanel.setAttribute("role", "status");
    loadingPanel.setAttribute("aria-live", "polite");
    loadingPanel.setAttribute("aria-hidden", "true");

    const dots = document.createElement("span");
    dots.className = "page-loading-dots";
    dots.setAttribute("aria-hidden", "true");

    for (let dot = 0; dot < 5; dot += 1) {
        const loadingDot = document.createElement("span");
        loadingDot.className = "page-loading-dot";
        dots.appendChild(loadingDot);
    }

    loadingPanel.appendChild(dots);
    document.body.appendChild(loadingPanel);

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
        const headerBottom = header ? header.getBoundingClientRect().bottom : 0;
        loadingPanel.style.setProperty("--page-loading-top", `${headerBottom}px`);
    }

    updateLoadingPosition();
    window.addEventListener("resize", updateLoadingPosition);
    window.addEventListener("scroll", updateLoadingPosition, {passive: true});
    window.addEventListener("pagehide", function () {
        documentLeaving = true;
    });

    function playDotWave() {
        const displayWidth = Math.min(window.innerWidth, 800);
        const halfWidth = displayWidth / 2;
        const exitEndTime = exitStartDelay + exitStagger * 4 + exitDuration;
        const loadingDots = Array.from(loadingPanel.querySelectorAll(".page-loading-dot"));

        if (reducedMotion) {
            return new Promise(function (resolve) {
                window.setTimeout(resolve, clearPause);
            });
        }

        return new Promise(function (resolve) {
            const startTime = performance.now();

            function renderFrame(now) {
                const elapsed = now - startTime;

                loadingDots.forEach(function (dot, index) {
                    const clusterOffset = (2 - index) * dotSpacing;
                    const entryStart = index * entryStagger;
                    const entryElapsed = elapsed - entryStart;
                    const entryProgress = Math.min(Math.max(entryElapsed / entryDuration, 0), 1);
                    const startX = -halfWidth - 3 - index * 18;
                    const clusterX = clusterOffset;
                    let x = startX;
                    let opacity = 0;

                    if (entryElapsed < 0) {
                        x = startX;
                    } else if (entryProgress < 1) {
                        const easing = (1 - Math.exp(-4 * entryProgress)) / (1 - Math.exp(-4));
                        x = startX + (clusterX - startX) * easing;
                        opacity = Math.min(entryProgress * 8, 1);
                    } else if (elapsed < exitStartDelay + index * exitStagger) {
                        const crawlElapsed = Math.max(entryElapsed - entryDuration, 0);
                        x = clusterX + crawlElapsed * 0.022;
                        opacity = 1;
                    } else {
                        const exitElapsed = Math.min(
                            elapsed - exitStartDelay - index * exitStagger,
                            exitDuration
                        );
                        const exitProgress = exitElapsed / exitDuration;
                        const exitFrom = clusterX +
                            Math.max(exitStartDelay + index * exitStagger - entryStart - entryDuration, 0) * 0.022;
                        const exitTo = halfWidth + 4;
                        const initialVelocity = 0.022;
                        const cubicFactor = (exitTo - exitFrom - initialVelocity * exitDuration) /
                            (exitDuration ** 3);

                        x = exitFrom + initialVelocity * exitElapsed + cubicFactor * (exitElapsed ** 3);
                        opacity = 1 - exitProgress;
                    }

                    dot.style.opacity = String(opacity);
                    dot.style.transform = `translate(calc(-50% + ${x}px), -50%)`;
                });

                if (elapsed < exitEndTime) {
                    window.requestAnimationFrame(renderFrame);
                } else {
                    loadingDots.forEach(function (dot) {
                        dot.style.opacity = "0";
                    });
                    window.setTimeout(resolve, clearPause);
                }
            }

            window.requestAnimationFrame(renderFrame);
        });
    }

    function hideLoading() {
        loadingPanel.classList.remove("is-open");
        navigationPending = false;
        loadingPanel.setAttribute("aria-hidden", "true");
    }

    window.addEventListener("pageshow", function () {
        documentLeaving = false;
        hideLoading();
    });

    function showLoading(continueNavigation) {
        if (navigationPending) {
            return false;
        }

        navigationPending = true;
        updateLoadingPosition();
        loadingPanel.removeAttribute("aria-hidden");
        loadingPanel.classList.add("is-open");

        (async function () {
            await playDotWave();

            if (!navigationPending || documentLeaving) {
                return;
            }

            if (!continueNavigation) {
                hideLoading();
                return;
            }

            continueNavigation();

            if (reducedMotion) {
                hideLoading();
                return;
            }

            while (navigationPending && !documentLeaving) {
                await playDotWave();
            }
        })();

        return true;
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

        if (bypassedLinks.has(link)) {
            bypassedLinks.delete(link);
            return;
        }

        if (!link.matches(buttonLinkSelector) || link.hasAttribute("download")) {
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
            destination.origin !== window.location.origin
        ) {
            return;
        }

        event.preventDefault();

        showLoading(function () {
            if (/^javascript:/i.test(href)) {
                bypassedLinks.add(link);
                link.click();
                return;
            }

            window.location.assign(link.href);
        });
    });
})();