export class Theme {
	static value = localStorage.getItem('theme') as 'light' | 'dark' | null;

	static set(theme: 'light' | 'dark') {
		Theme.value = theme;
		document.documentElement.classList.remove('light', 'dark');
		document.documentElement.classList.add(theme);
		localStorage.setItem('theme', theme);
	}

	static initialize() {
		Theme.set(window?.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
	}
}
