import { DOM } from '../Utility/DOM';

export class Toggle {
	static make(name: string, checked: boolean = false): { label: HTMLLabelElement; checkbox: HTMLInputElement } {
		const id = `toggle-${name.toLocaleLowerCase()}`;
		const description = DOM.create('div', { className: 'px-2', textContent: name });
		const checkbox = DOM.create('input', { type: 'checkbox', id, name: 'theme', className: 'hidden', checked });
		const path = DOM.create('div', { className: 'path transition bg-gray-200 w-9 h-5 rounded-full shadow-inner' });
		const circle = DOM.create('div', {
			className: 'circle transition absolute w-3.5 h-3.5 bg-white rounded-full shadow inset-y-0 left-0',
		});
		const container = DOM.create('div', { className: 'relative', childs: [checkbox, path, circle] });
		const label = DOM.create('label', {
			htmlFor: id,
			className: 'flex items-center cursor-pointer toggle',
			childs: [description, container],
		});
		return { label, checkbox };
	}
}
