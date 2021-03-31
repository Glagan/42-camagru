import { Notification } from '../UI/Notification';
import { DOM } from './DOM';

export type ValidateFunction = (value: string) => string | true;

export class Validator {
	node: HTMLInputElement;
	validator: ValidateFunction;

	constructor(node: HTMLInputElement, validator: ValidateFunction) {
		this.node = node;
		this.validator = validator;
		DOM.validateInput(this.node, this.validator);
	}

	validate(): boolean {
		this.node.classList.remove('error');
		const errorMessage = this.validator(this.node.value.trim());
		if (errorMessage !== true) {
			this.node.classList.add('error');
			Notification.show('danger', errorMessage);
			return false;
		}
		return true;
	}

	static username(value: string): string | true {
		return value.length < 4 ? 'Username is too short.' : true;
	}

	static email(value: string): string | true {
		return value.match(
			/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/g
		) == null
			? 'Invalid email.'
			: true;
	}

	static password(value: string): string | true {
		return value.length < 8
			? 'Password is too short (at least 8 characters).'
			: value.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).*/) == null
			? 'Password must contains at least 1 lowercase character, 1 uppercase character, 1 number and 1 special character.'
			: true;
	}
}
