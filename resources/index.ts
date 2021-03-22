// Set Dark theme
function setTheme(theme: 'light' | 'dark') {
	document.documentElement.classList.remove('light', 'dark');
	document.documentElement.classList.add(theme);
	localStorage.setItem('theme', theme);
}
let theme = localStorage.getItem('theme') as 'light' | 'dark' | null;
if (!theme) {
	theme = window?.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}
setTheme(theme);
