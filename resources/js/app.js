import './bootstrap';

window.sikamoApplyTheme = window.sikamoApplyTheme || function (theme) {
    const selectedTheme = theme || localStorage.getItem('theme') || 'system';
    const shouldUseDark = selectedTheme === 'dark'
        || (selectedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', shouldUseDark);
    document.documentElement.style.colorScheme = shouldUseDark ? 'dark' : 'light';
};

window.sikamoApplyTheme();

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if ((localStorage.getItem('theme') || 'system') === 'system') {
        window.sikamoApplyTheme('system');
    }
});

window.addEventListener('theme-changed', (event) => {
    const theme = event.detail || 'system';
    localStorage.setItem('theme', theme);
    window.sikamoApplyTheme(theme);
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.warn('Service worker registration failed:', error);
        });
    });
}
