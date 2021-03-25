import { DOM, HeroIcon } from '../Utility/DOM';

export type Type = 'info' | 'success' | 'danger' | 'warning';

/**
 * Enhanced https://v1.tailwindcss.com/components/alerts#top-accent-border
 */
export class Alert {
	static iconDefinition: {
		[key in Type]: [name: HeroIcon, color: string];
	} = {
		info: ['information-circle', 'text-blue-500'],
		success: ['check-circle', 'text-green-500'],
		danger: ['x-circle', 'text-red-500'],
		warning: ['exclamation-circle', 'text-yellow-500'],
	};

	static make(type: Type, message: string): HTMLElement {
		const style = Alert.iconDefinition[type];
		const icon = DOM.create('div', {
			className: 'py-1',
			childs: [DOM.icon(style[0], { classes: [style[1], 'mr-4'] })],
		});
		const alertMessage = DOM.create('div', {
			childs: [DOM.create('p', { textContent: message })],
		});
		const container = DOM.create('div', { className: 'flex items-center', childs: [icon, alertMessage] });
		const alert = DOM.create('div', { className: `alert ${type}`, childs: [container] });
		return alert;
	}
}
