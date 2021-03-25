import { Notification } from '../UI/Notification';
import { DOM } from './DOM';

export class Validator {
	node: HTMLInputElement;
	validator: (value: string) => string | true;

	constructor(node: HTMLInputElement, validator: (value: string) => string | true) {
		this.node = node;
		this.validator = validator;
		DOM.validateInput(this.node, this.validator);
	}

	validate(): boolean {
		this.node.classList.remove('error');
		const errorMessage = this.validator(this.node.value);
		if (errorMessage !== true) {
			this.node.classList.add('error');
			Notification.show('danger', errorMessage);
			return false;
		}
		return true;
	}
}
