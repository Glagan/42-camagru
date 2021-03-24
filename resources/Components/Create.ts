import { Component } from '../Component';
import { DOM } from '../Utility/DOM';

export class Create extends Component {
	static auth = true;

	container!: HTMLElement;
	preview!: HTMLImageElement;
	actions!: HTMLElement;
	sourceSelection!: HTMLElement;
	enableCamera!: HTMLButtonElement;
	upload!: HTMLButtonElement;
	capture!: HTMLButtonElement;
	submit!: HTMLButtonElement;
	decorations!: HTMLElement;

	create(): void {
		this.preview = DOM.create('img', {
			className: 'shadow-md',
			src: 'https://via.placeholder.com/900x450?text=Preview',
			width: 900,
			height: 450,
		});
		this.container = DOM.create('div', {
			className: 'sticky top-4 z-30 bg-gray-100 dark:bg-gray-600',
			childs: [this.preview],
		});
		this.enableCamera = DOM.button('primary', 'camera', 'Camera');
		this.enableCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.upload = DOM.button('secondary', 'upload', 'Upload');
		this.upload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.sourceSelection = DOM.create('div', { childs: [this.enableCamera, this.upload] });
		this.capture = DOM.button('primary', 'camera', 'Capture');
		this.submit = DOM.button('success', 'plus-circle', 'Submit');
		this.actions = DOM.create('div', {
			className: 'flex flex-row flex-nowrap justify-between mt-2',
			childs: [this.sourceSelection, this.capture, this.submit],
		});
		this.decorations = DOM.create('div', {
			className:
				'mt-2 grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 auto-rows-min justify-items-center p-4',
		});
	}

	bind(): void {}

	render(): void {
		DOM.append(this.parent, this.container, this.actions, this.decorations);
	}
}
