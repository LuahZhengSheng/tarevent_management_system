/**
 * Theme Toggle Script (jQuery version)
 * Handles dark mode switching with local storage persistence
 */

$(function () {
    const $darkModeToggle = $('#darkModeToggle');
    const $darkModeIcon = $('#darkModeIcon');
    const $html = $('html');

    // 读取本地保存的主题（默认 light）
    let currentTheme = localStorage.getItem('theme') || 'light';
    $html.attr('data-theme', currentTheme);
    updateIcon(currentTheme);

    // 点击按钮切换主题
    if ($darkModeToggle.length) {
        $darkModeToggle.on('click', function () {
            const current = $html.attr('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';

            $html.attr('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);

            // 动画效果
            $darkModeToggle.css('transform', 'rotate(360deg)');
            setTimeout(() => {
                $darkModeToggle.css('transform', '');
            }, 300);
        });
    }

    function updateIcon(theme) {
        if ($darkModeIcon.length) {
            if (theme === 'dark') {
                $darkModeIcon.attr('class', 'bi bi-sun-fill');
            } else {
                $darkModeIcon.attr('class', 'bi bi-moon-stars');
            }
        }
    }

    // 监听系统主题变化（仅在没有手动选择主题时生效）
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', function (e) {
        if (!localStorage.getItem('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            $html.attr('data-theme', newTheme);
            updateIcon(newTheme);
        }
    });
});
