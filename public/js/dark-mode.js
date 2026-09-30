(function () {
    var KEY = 'fixit-theme';
    var root = document.documentElement;

    function preferred() {
        try {
            var saved = localStorage.getItem(KEY);
            if (saved === 'dark' || saved === 'light') return saved;
        } catch (e) {}
        var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return systemDark ? 'dark' : 'light';
    }

    function apply(theme) {
        root.setAttribute('data-theme', theme);

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            var label = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
            button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        });
    }

    apply(preferred()); // runs immediately, so there is no white flash on load

    document.addEventListener('DOMContentLoaded', function () {
        apply(root.getAttribute('data-theme')); // update the button now that it exists

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                apply(next);
                try { localStorage.setItem(KEY, next); } catch (e) {}
            });
        });
    });
})();