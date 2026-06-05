import './bootstrap';

document.documentElement.classList.remove('dark');
document.documentElement.style.colorScheme = 'light';
localStorage.setItem('theme', 'light');

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.warn('Service worker registration failed:', error);
        });
    });
}
