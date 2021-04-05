import { Component } from '../Component';

export class PageNotFound extends Component {
	create(): void {}

	render(): void {
		this.genericError('404', 'Page not Found', 'How did you get there ?');
	}
}
