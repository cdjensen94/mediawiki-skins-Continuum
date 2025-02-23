(function ($) {
    $(document).ready(function () {
        // Check for stored theme preference
        var currentTheme = localStorage.getItem('continuum-theme') || 'light';

        // Apply the stored theme
        applyTheme(currentTheme);

        // Event listener for the theme switcher
        $('#theme-switcher').on('change', function () {
            var selectedTheme = $(this).val();
            applyTheme(selectedTheme);
            localStorage.setItem('continuum-theme', selectedTheme); // Save preference
        });

        function applyTheme(theme) {
            // Remove existing theme classes
            $('body').removeClass('light-mode dark-mode medium-mode');
            // Add new theme class
            $('body').addClass(theme + '-mode');
        }
    });
})(jQuery);
