import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http } from '../Utility/Http';

export type Position = 'top-left' | 'top-right' | 'bottom-right' | 'bottom-left' | 'center';

export interface Decoration {
	id: number;
	name: string;
	category: 'still' | 'animated';
	position: Position;
}

export class Create extends Component {
	static auth = true;

	decorations: { still: Decoration[]; animated: Decoration[] } = { still: [], animated: [] };
	previewRow!: HTMLElement;
	preview!: HTMLElement;
	videoPreview!: HTMLVideoElement;
	imagePreview!: HTMLImageElement;
	allowCamera!: HTMLElement;
	noCamera!: HTMLElement;
	layers!: HTMLElement[];
	actions!: HTMLElement;
	sourceSelection!: HTMLElement;
	selectCamera!: HTMLButtonElement;
	selectUpload!: HTMLButtonElement;
	uploadInput!: HTMLInputElement;
	capture!: HTMLButtonElement;
	cancelCapture!: HTMLButtonElement;
	submit!: HTMLButtonElement;
	decorationSelector!: HTMLElement;
	visibleDecorations!: HTMLElement;
	currentDecorations: Decoration[] = [];

	create(): void {
		this.layers = [];
		this.videoPreview = DOM.create('video', { className: 'hidden preview', loop: true, autoplay: true, volume: 0 });
		this.imagePreview = DOM.create('img', { className: 'hidden preview' });
		this.allowCamera = Alert.make('info', 'Allow the Camera permission on the top left to be able to use it.');
		this.noCamera = Alert.make('danger', 'Error while accessing your Camera.');
		this.visibleDecorations = DOM.create('div', { className: 'preview-decorations' });
		this.preview = DOM.create('div', {
			className: 'relative',
			childs: [this.videoPreview, this.imagePreview, this.allowCamera, this.noCamera, this.visibleDecorations],
		});
		// sticky top-4 z-30 && bg-gray-200 dark:bg-gray-600
		this.previewRow = DOM.create('div', { className: 'flex items-center justify-center', childs: [this.preview] });
		this.selectCamera = DOM.button('primary', 'camera', 'Camera');
		this.selectCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.selectUpload = DOM.button('secondary', 'upload', 'Upload');
		this.selectUpload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.uploadInput = DOM.create('input', {
			className: 'hidden',
			id: 'create-upload',
			name: 'create-upload',
			type: 'file',
			accept: '.png,.jpg,.jpeg,.gif,.mp4',
		});
		this.uploadInput.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.sourceSelection = DOM.create('div', {
			className: 'flex',
			childs: [this.selectCamera, this.selectUpload, this.uploadInput],
		});
		this.capture = DOM.button('primary', 'camera', 'Capture');
		this.capture.classList.add('mr-2', 'hidden');
		this.cancelCapture = DOM.button('error', 'x-circle', 'Cancel');
		this.cancelCapture.classList.add('hidden');
		this.submit = DOM.button('success', 'plus-circle', 'Submit');
		this.actions = DOM.create('div', {
			className: 'flex flex-row flex-nowrap justify-between mt-2',
			childs: [
				this.sourceSelection,
				DOM.create('div', { childs: [this.capture, this.cancelCapture] }),
				this.submit,
			],
		});
		this.decorationSelector = DOM.create('div', { className: 'decorations' });
	}

	async data(): Promise<void> {
		const response = await Http.get<{ list: Decoration[] }>('/api/decorations');
		if (response.ok) {
			this.decorations = { still: [], animated: [] };
			for (const decoration of response.body.list) {
				if (decoration.category == 'still') {
					this.decorations.still.push(decoration);
				} else {
					this.decorations.animated.push(decoration);
				}
			}
		} else {
			Notification.show('danger', `Failed to load Decorations: ${response.body.error}`);
		}
	}

	private enableCapture(): void {
		this.capture.classList.remove('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private disableCapture(): void {
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.add('hidden');
	}

	private hidePreviews(): void {
		this.imagePreview.classList.add('hidden');
		this.videoPreview.classList.add('hidden');
		this.allowCamera.classList.add('hidden');
		this.noCamera.classList.add('hidden');
	}

	private cameraMode(): void {
		this.hidePreviews();
		this.enableCapture();
		this.submit.disabled = true;
		this.allowCamera.classList.remove('hidden');
		this.noCamera.classList.add('hidden');
		navigator.mediaDevices
			.getUserMedia({ audio: false, video: { width: { min: 1280 }, height: { min: 720 }, frameRate: 30 } })
			.then((stream) => {
				this.videoPreview.classList.remove('hidden');
				this.videoPreview.src = '';
				this.videoPreview.srcObject = stream;
				this.enableCapture();
				this.submit.disabled = false;
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.add('hidden');
			})
			.catch((error) => {
				this.hidePreviews();
				this.disableCapture();
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.remove('hidden');
			});
	}

	private uploadMode(file: string): void {
		this.hidePreviews();
		this.disableCapture();
		this.submit.disabled = false;
		const isVideo = /^data:video/.test(file);
		if (isVideo) {
			this.videoPreview.srcObject = null;
			this.videoPreview.src = file;
			this.videoPreview.classList.remove('hidden');
		} else {
			this.imagePreview.src = file;
			this.imagePreview.classList.remove('hidden');
		}
	}

	private setCapturePreview(image: string): void {
		this.imagePreview.src = image;
		this.imagePreview.classList.remove('hidden');
		this.videoPreview.classList.add('hidden');
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.remove('hidden');
		this.submit.disabled = false;
	}

	bind(): void {
		this.videoPreview.addEventListener('loadedmetadata', (event) => {
			this.videoPreview.play();
		});
		this.selectCamera.addEventListener('click', (event) => {
			event.preventDefault();
			this.cameraMode();
		});
		this.selectUpload.addEventListener('click', (event) => {
			event.preventDefault();
			this.uploadInput.click();
		});
		this.uploadInput.addEventListener('change', (event) => {
			if (this.uploadInput.value && this.uploadInput.files?.length) {
				const reader = new FileReader();
				reader.readAsDataURL(this.uploadInput.files[0]);
				reader.addEventListener('load', () => {
					this.uploadMode(reader.result as string);
				});
			}
		});
		this.capture.addEventListener('click', (event) => {
			event.preventDefault();
			const canvas = DOM.create('canvas');
			canvas.width = this.videoPreview.videoWidth;
			canvas.height = this.videoPreview.videoHeight;
			const context = canvas.getContext('2d')!;
			context.drawImage(this.videoPreview, 0, 0, canvas.width, canvas.height);
			const image = canvas.toDataURL('image/png');
			this.setCapturePreview(image);
		});
		this.cancelCapture.addEventListener('click', (event) => {
			event.preventDefault();
			this.imagePreview.src = '';
			this.imagePreview.classList.add('hidden');
			this.videoPreview.classList.remove('hidden');
			this.videoPreview.play();
			this.capture.classList.remove('hidden');
			this.cancelCapture.classList.add('hidden');
			this.submit.disabled = true;
		});
	}

	private createDecoration(decoration: Decoration): HTMLElement {
		return DOM.create(decoration.category == 'still' ? 'img' : 'video', {
			src: `/decorations/${decoration.name}`,
			autoplay: true,
			loop: true,
			volume: 0,
		});
	}

	private positionClasses(position: Position): string[] {
		switch (position) {
			case 'top-left':
				return ['top-0', 'left-0'];
			case 'top-right':
				return ['top-0', 'right-0'];
			case 'bottom-left':
				return ['bottom-0', 'left-0'];
			case 'bottom-right':
				return ['bottom-0', 'right-0'];
			case 'center':
				return ['top-1/2', 'left-1/2', 'transform-center'];
		}
	}

	private displayDecoration(decoration: Decoration): void {
		const visual = this.createDecoration(decoration);
		const card = DOM.create('div', {
			className: 'card',
			childs: [visual],
		});
		card.addEventListener('click', (event) => {
			event.preventDefault();
			this.currentDecorations.push(decoration);
			const layer = this.createDecoration(decoration);
			layer.classList.add(...this.positionClasses(decoration.position));
			this.visibleDecorations.appendChild(layer);
		});
		this.decorationSelector.appendChild(card);
	}

	render(): void {
		for (const decoration of this.decorations.still) {
			this.displayDecoration(decoration);
		}
		for (const decoration of this.decorations.animated) {
			this.displayDecoration(decoration);
		}
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = true;
		DOM.append(this.parent, this.previewRow, this.actions, this.decorationSelector);
		this.cameraMode();
	}
}
