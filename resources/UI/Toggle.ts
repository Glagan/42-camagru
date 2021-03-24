import { AppendableElement, DOM } from '../Utility/DOM';

/**
 * Enhanced https://tailwindcomponents.com/component/toggle-button-1
 */
export class Toggle {
	static make(
		name: string,
		options?: {
			checked?: boolean;
			name?: string;
			prefix?: AppendableElement;
			suffix?: AppendableElement;
		}
	): { label: HTMLLabelElement; checkbox: HTMLInputElement } {
		const id = `toggle-${name.replace(' ', '-').toLocaleLowerCase()}`;
		const description = DOM.create('div', { className: 'pr-2', textContent: name });
		const checkbox = DOM.create('input', {
			type: 'checkbox',
			id,
			name: options?.name ?? id,
			className: 'hidden',
			checked: options?.checked,
		});
		const path = DOM.create('div', { className: 'path transition bg-gray-200 w-9 h-5 rounded-full shadow-inner' });
		const circle = DOM.create('div', {
			className: 'circle transition absolute w-3.5 h-3.5 bg-white rounded-full shadow inset-y-0 left-0',
		});
		const container = DOM.create('div', { className: 'relative', childs: [checkbox, path, circle] });
		const label = DOM.create('label', {
			htmlFor: id,
			className: 'flex items-center cursor-pointer toggle',
			childs: [description],
		});
		if (options?.prefix) label.appendChild(options.prefix);
		label.appendChild(container);
		if (options?.suffix) label.appendChild(options.suffix);
		return { label, checkbox };
	}
}
