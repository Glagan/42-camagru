import { DOM } from '../Utility/DOM';

export class Badge {
	static make(type: boolean, small: boolean = false): HTMLElement {
		const node = DOM.create('span');
		return Badge.set(node, type, small);
	}

	static set(node: HTMLElement, type: boolean, small: boolean = false): HTMLElement {
		DOM.clear(node);
		if (type) {
			node.className = 'badge success';
			node.appendChild(DOM.icon('check', { width: '', height: '' }));
			node.title = 'Verified';
		} else {
			node.className = 'badge warning';
			node.appendChild(DOM.icon('exclamation', { width: '', height: '' }));
			node.title = 'Not Verified';
		}
		if (small) node.classList.add('small');
		return node;
	}
}
