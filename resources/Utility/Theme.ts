export class Theme {
	static value: 'light' | 'dark' = 'light';

	static set(theme: 'light' | 'dark') {
		Theme.value = theme;
		document.documentElement.classList.remove('light', 'dark');
		document.documentElement.classList.add(theme);
		localStorage.setItem('theme', theme);
	}

	static initialize() {
		let value = localStorage.getItem('theme') as 'light' | 'dark' | null;
		if (!value) {
			value = window?.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
		}
		Theme.set(value);
	}
}
