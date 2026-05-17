(function () {
    var loadingDelay = 80;
    var submitterByForm = new WeakMap();

    function ensureActionLoading() {
        var overlay = document.getElementById("actionLoading");

        if (overlay || !document.body) {
            return overlay;
        }

        overlay = document.createElement("div");
        overlay.className = "action-loading-overlay";
        overlay.id = "actionLoading";
        overlay.setAttribute("aria-hidden", "true");

        var loader = document.createElement("div");
        loader.className = "action-loader";
        overlay.appendChild(loader);
        document.body.appendChild(overlay);

        return overlay;
    }

    function showActionLoading() {
        var overlay = ensureActionLoading();

        if (!overlay) {
            return;
        }

        overlay.classList.add("is-active");
        overlay.setAttribute("aria-hidden", "false");
    }

    function hideActionLoading() {
        var overlay = document.getElementById("actionLoading");

        if (!overlay) {
            return;
        }

        overlay.classList.remove("is-active");
        overlay.setAttribute("aria-hidden", "true");
    }

    function hasActionLoading(element) {
        return (
            element.classList.contains("js-action-loading") ||
            element.getAttribute("data-action-loading") === "true"
        );
    }

    function rememberSubmitter(event) {
        var button = event.target.closest("button, input");

        if (!button || !button.form) {
            return;
        }

        var type = (button.getAttribute("type") || "submit").toLowerCase();

        if (type === "submit" || type === "image") {
            submitterByForm.set(button.form, button);
        }
    }

    function appendSubmitterValue(form, submitter) {
        if (!submitter || !submitter.name || submitter.disabled) {
            return;
        }

        var input = document.createElement("input");
        input.type = "hidden";
        input.name = submitter.name;
        input.value = submitter.value || "1";
        input.setAttribute("data-action-loading-submit", "true");
        form.appendChild(input);
    }

    function findDefaultSubmitter(form) {
        return form.querySelector(
            "button[type='submit'][name], input[type='submit'][name], button:not([type])[name]"
        );
    }

    function submitWithLoading(event) {
        var form = event.target;

        if (!hasActionLoading(form)) {
            return;
        }

        if (form.getAttribute("data-action-loading-submitting") === "true") {
            return;
        }

        if (typeof form.checkValidity === "function" && !form.checkValidity()) {
            return;
        }

        event.preventDefault();

        var submitter = event.submitter || submitterByForm.get(form) || findDefaultSubmitter(form);

        appendSubmitterValue(form, submitter);
        form.setAttribute("data-action-loading-submitting", "true");
        showActionLoading();

        setTimeout(function () {
            HTMLFormElement.prototype.submit.call(form);
        }, loadingDelay);
    }

    function linkWithLoading(event) {
        var link = event.target.closest("a.js-link-loading, a[data-action-loading='true']");

        if (!link) {
            return;
        }

        var href = link.getAttribute("href");

        if (!href || href === "#" || href.indexOf("javascript:") === 0 || link.target === "_blank" || link.hasAttribute("download")) {
            return;
        }

        event.preventDefault();
        goWithActionLoading(link.href);
    }

    function goWithActionLoading(href) {
        if (!href) {
            return;
        }

        showActionLoading();

        setTimeout(function () {
            window.location.href = href;
        }, loadingDelay);
    }

    document.addEventListener("click", rememberSubmitter, true);
    document.addEventListener("submit", submitWithLoading, true);
    document.addEventListener("click", linkWithLoading, true);
    window.addEventListener("pageshow", hideActionLoading);

    window.showActionLoading = showActionLoading;
    window.hideActionLoading = hideActionLoading;
    window.goWithActionLoading = goWithActionLoading;
})();
