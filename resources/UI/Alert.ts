import { DOM, HeroIcon } from '../Utility/DOM';

export type Type = 'info' | 'success' | 'danger' | 'warning';

/**
 * Enhanced https://v1.tailwindcss.com/components/alerts#top-accent-border
 */
export class Alert {
	static typeStyle: {
		[key in Type]: [background: string, border: string, text: string, iconName: HeroIcon, iconColor: string];
	} = {
		info: ['bg-blue-100', 'border-blue-500', 'text-blue-900', 'information-circle', 'text-blue-500'],
		success: ['bg-green-100', 'border-green-500', 'text-green-900', 'check-circle', 'text-green-500'],
		danger: ['bg-red-100', 'border-red-500', 'text-red-900', 'x-circle', 'text-red-500'],
		warning: ['bg-orange-100', 'border-orange-500', 'text-orange-900', 'exclamation-circle', 'text-orange-500'],
	};

	static make(type: Type, message: string): HTMLElement {
		const style = Alert.typeStyle[type];
		const icon = DOM.create('div', {
			className: 'py-1',
			childs: [DOM.icon(style[3], { classes: [style[4], 'mr-4'] })],
		});
		const alertMessage = DOM.create('div', {
			childs: [DOM.create('p', { textContent: message })],
		});
		const container = DOM.create('div', { className: 'flex items-center', childs: [icon, alertMessage] });
		const alert = DOM.create('div', { className: 'border-l-4 rounded-r px-4 py-3 shadow-md', childs: [container] });
		console.log(style[0]);
		alert.classList.add(style[0], style[1], style[2]);
		return alert;
	}
}
