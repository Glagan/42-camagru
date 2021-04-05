import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class Unauthorized extends Component {
	create(): void {}

	render(): void {
		this.genericError('401', 'Unauthorized', "You shouldn't be here !");
	}
}
