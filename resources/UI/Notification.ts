import { DOM } from '../Utility/DOM';
import { Alert, Type } from './Alert';

export class Notification {
	static wrapper: HTMLElement;

	static initialize(): void {
		Notification.wrapper = DOM.create('div', { className: 'notifications' });
		document.body.appendChild(Notification.wrapper);
	}

	static show(type: Type, message: string): HTMLElement {
		const style = Alert.iconDefinition[type];
		const icon = DOM.create('div', {
			className: 'py-1',
			childs: [DOM.icon(style[0], { classes: [style[1], 'mr-4'] })],
		});
		const alertMessage = DOM.create('div', {
			childs: [DOM.create('p', { textContent: message })],
		});
		const notification = DOM.create('div', { className: `notification ${type}`, childs: [icon, alertMessage] });
		const notificationRemove = setTimeout(() => {
			notification.remove();
		}, 5000);
		notification.addEventListener('click', (event) => {
			clearTimeout(notificationRemove);
			notification.remove();
		});
		Notification.wrapper.appendChild(notification);
		return notification;
	}
}
