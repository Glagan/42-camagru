import { Component } from '../Component';
import { Toggle } from '../UI/Toggle';
import { DOM } from '../Utility/DOM';
import { Theme } from '../Utility/Theme';

export class Preferences extends Component {
	header!: HTMLElement;
	form!: HTMLFormElement;
	themeLabel!: HTMLLabelElement;
	themeToggle!: HTMLInputElement;

	create(): void {
		this.header = DOM.create('h1', { className: 'header', textContent: 'Preferences' });
		const checked = Theme.value == 'dark';
		const { label, checkbox } = Toggle.make('Theme', {
			checked,
			prefix: DOM.icon('sun'),
			suffix: DOM.icon('moon'),
		});
		this.themeLabel = label;
		this.themeToggle = checkbox;
		this.themeToggle.addEventListener('change', (event) => {
			event.preventDefault();
			if (this.themeToggle.checked) {
				Theme.set('dark');
			} else {
				Theme.set('light');
			}
		});
		this.form = DOM.create('form', { childs: [this.themeLabel] });
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.header, this.form);
	}
}
