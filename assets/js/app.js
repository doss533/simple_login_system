/* ============================================================
   Login System - front-end interactions
   3D tilt + glare, sign-in / sign-up flip, password toggle,
   toast notifications, demo autofill and submit feedback.
   ============================================================ */
(function () {
    "use strict";

    /* ---------- Icons (kept in sync with the PHP icon() helper) ---------- */
    var EYE_SVG =
        '<svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
        '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>' +
        '</svg>';
    var EYE_OFF_SVG =
        '<svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' +
        '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27z"/>' +
        '</svg>';

    /* ---------- Toast ---------- */
    var toast = document.getElementById("toast");
    var toastTitle = document.getElementById("toast-title");
    var toastBody = document.getElementById("toast-body");
    var toastTimer = null;

    window.showToast = function (title, body, type) {
        if (!toast) return;
        type = type || "info";
        toast.className = "toast toast-" + type;
        if (toastTitle) toastTitle.textContent = title;
        if (toastBody) toastBody.textContent = body || "";

        // force reflow so the transition replays
        void toast.offsetWidth;
        toast.classList.add("show");

        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.remove("show");
        }, 3400);
    };

    // Surface any server-side message as a toast too.
    var serverAlert = document.querySelector("[data-toast-message]");
    if (serverAlert) {
        showToast(
            serverAlert.getAttribute("data-toast-title") || "Notice",
            serverAlert.getAttribute("data-toast-message"),
            serverAlert.getAttribute("data-toast-type") || "info"
        );
    }

    /* ---------- 3D tilt + glare ---------- */
    var card = document.getElementById("tilt-card");
    var glare = document.getElementById("card-glare");
    var wrapper = document.querySelector(".card-perspective");
    var spinning = false;

    if (wrapper && card) {
        wrapper.addEventListener("mousemove", function (e) {
            if (spinning) return;
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var cx = rect.width / 2;
            var cy = rect.height / 2;
            var rotX = ((y - cy) / cy) * -9.5;
            var rotY = ((x - cx) / cx) * 9.5;

            card.style.transform =
                "rotateX(" + rotX + "deg) rotateY(" + rotY + "deg) scale3d(1.015,1.015,1.015)";

            if (glare) {
                var gx = (x / rect.width) * 100;
                var gy = (y / rect.height) * 100;
                glare.style.background =
                    "radial-gradient(circle at " + gx + "% " + gy +
                    "%, rgba(255,255,255,0.2) 0%, transparent 65%)";
            }
        });

        wrapper.addEventListener("mouseleave", function () {
            if (spinning) return;
            card.style.transform = "rotateX(0deg) rotateY(0deg) scale3d(1,1,1)";
            if (glare) {
                glare.style.background =
                    "radial-gradient(circle at 50% 50%, rgba(255,255,255,0.12) 0%, transparent 60%)";
            }
        });
    }

    /* ---------- Flip between sign in / sign up ---------- */
    var flipInner = document.querySelector(".flip-inner");

    window.flipCard = function (toSignUp) {
        if (!flipInner) return;
        if (card) card.style.transform = "rotateX(0deg) rotateY(0deg)";
        flipInner.classList.toggle("is-flipped", !!toSignUp);

        var email = document.getElementById("login-username");
        var signup = document.getElementById("reg-username");
        if (toSignUp && email && signup && !signup.value) signup.value = email.value;
    };

    document.querySelectorAll("[data-flip]").forEach(function (el) {
        el.addEventListener("click", function () {
            flipCard(el.getAttribute("data-flip") === "true");
        });
    });

    /* ---------- Password visibility ---------- */
    document.querySelectorAll("[data-toggle-password]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var input = document.getElementById(btn.getAttribute("data-toggle-password"));
            if (!input) return;
            var show = input.type === "password";
            input.type = show ? "text" : "password";
            btn.innerHTML = show ? EYE_OFF_SVG : EYE_SVG;
        });
    });

    /* ---------- Demo autofill ---------- */
    /* ---------- Mock OAuth + forgot password ---------- */
    document.querySelectorAll("[data-oauth]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            showToast(
                "OAuth handshake",
                "Simulating " + btn.getAttribute("data-oauth") + " sign-in (demo only).",
                "info"
            );
        });
    });

    document.querySelectorAll("[data-forgot]").forEach(function (el) {
        el.addEventListener("click", function (e) {
            e.preventDefault();
            showToast("Demo credentials", "admin / admin123  or  juan / user123", "info");
        });
    });

    document.querySelectorAll("[data-demo-fill]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var user = document.getElementById("login-username");
            var pass = document.getElementById("login-password");
            if (user) user.value = btn.getAttribute("data-username") || "admin";
            if (pass) pass.value = btn.getAttribute("data-password") || "admin123";
            clearError(user);
            clearError(pass);
            showToast("Demo credentials loaded", "Ready for one-click sign in.", "info");
        });
    });

    /* ---------- Inline validation ---------- */
    function clearError(input) {
        if (!input) return;
        var field = input.closest(".field");
        if (field) field.classList.remove("invalid");
    }

    document.querySelectorAll(".form input[required]").forEach(function (input) {
        input.addEventListener("input", function () {
            clearError(input);
        });
    });

    /* ---------- Submit feedback (spin + loading state) ---------- */
    document.querySelectorAll("form[data-animate-submit]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            var invalid = false;
            form.querySelectorAll("input[required]").forEach(function (input) {
                if (!input.value.trim()) {
                    var field = input.closest(".field");
                    if (field) field.classList.add("invalid");
                    invalid = true;
                }
            });
            if (invalid) {
                e.preventDefault();
                showToast("Missing information", "Please complete all required fields.", "error");
                return;
            }

            if (spinning) {
                e.preventDefault();
                return;
            }
            spinning = true;

            var btn = form.querySelector("button[type=submit]");
            if (btn) {
                btn.disabled = true;
                var label = btn.querySelector("[data-label]");
                var iconEl = btn.querySelector(".icon");
                if (label) label.textContent = "Verifying…";
                if (iconEl) iconEl.classList.add("spin");
            }
            if (card) {
                card.classList.remove("animate-spin-3d");
                void card.offsetWidth;
                card.classList.add("animate-spin-3d");
            }
            // let the animation play, then submit for real
            e.preventDefault();
            setTimeout(function () {
                form.submit();
            }, 700);
        });
    });
})();
