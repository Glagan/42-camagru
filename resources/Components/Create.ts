import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';

export type XYPosition = { x: number; y: number };

const MIN_WIDTH = 854;
const MAX_WIDTH = 2560;
const OPTIMAL_WIDTH = 1920;
const MIN_HEIGHT = 480;
const MAX_HEIGHT = 1440;
const OPTIMAL_HEIGHT = 1080;
const LOADING_IMG =
	'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKAQMAAAC3/F3+AAAAA1BMVEUzzMx0ynDKAAAACklEQVR4XmPACwAAHgAB5s72BgAAAABJRU5ErkJggg==';
const LOADING_VIDEO = `data:image/webm;base64,GkXfo59ChoEBQveBAULygQRC84EIQoKEd2VibUKHgQJChYECGFOAZwEAAAAAAAJ+EU2bdLtNu4tTq4QVSalmU6yB5U27jFOrhBZUrmtTrIIBHE27jFOrhBJUw2dTrIIBXE27jFOrhBxTu2tTrIICaOwBAAAAAAAAnAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABVJqWayKtexgw9CQE2AjUxhdmY1OC4yOS4xMDBXQY1MYXZmNTguMjkuMTAwRImIQEQAAAAAAAAWVK5ru64BAAAAAAAAMteBAXPFgQGcgQAitZyDdW5khoVWX1ZQOIOBASPjg4QCYloA4AEAAAAAAAAGsIEKuoEKElTDZ0C/c3MBAAAAAAAALmPAAQAAAAAAAABnyAEAAAAAAAAaRaOHRU5DT0RFUkSHjUxhdmY1OC4yOS4xMDBzcwEAAAAAAAA5Y8ABAAAAAAAABGPFgQFnyAEAAAAAAAAhRaOHRU5DT0RFUkSHlExhdmM1OC41NC4xMDAgbGlidnB4c3MBAAAAAAAAOmPAAQAAAAAAAARjxYEBZ8gBAAAAAAAAIkWjiERVUkFUSU9ORIeUMDA6MDA6MDAuMDQwMDAwMDAwAAAfQ7Z1wueBAKO9gQAAgLACAJ0BKgoACgAARwiFhYiFhIgCAgJ1qgP4Agz9KAD+90av/rgPzgPzgP5lv/8D9/gfv8D9/+BPABxTu2uRu4+zgQC3iveBAfGCAiHwgQM=`;

export class Create extends Component {
	static auth = true;

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

	decorations: { still: Decoration[]; animated: Decoration[] } = { still: [], animated: [] };
	dragState!: { node: HTMLElement; initial: XYPosition; active: boolean; current: XYPosition };
	currentDecoration: Decoration | undefined;
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	create(): void {
		this.layers = [];
		this.videoPreview = DOM.create('video', { className: 'hidden preview', loop: true, autoplay: true, volume: 0 });
		this.imagePreview = DOM.create('img', { className: 'hidden preview' });
		this.allowCamera = Alert.make('info', 'Allow the Camera permission on the top left to be able to use it.');
		this.noCamera = Alert.make('danger', 'Error while accessing your Camera.');
		this.visibleDecorations = DOM.create('div', { className: 'preview-decorations' });
		this.preview = DOM.create('div', {
			className: 'relative preview-container',
			childs: [this.videoPreview, this.imagePreview, this.allowCamera, this.noCamera, this.visibleDecorations],
		});
		// sticky top-4 z-30 && bg-gray-200 dark:bg-gray-600
		this.previewRow = DOM.create('div', {
			className: 'flex items-center justify-center mt-2',
			childs: [this.preview],
		});
		this.selectCamera = DOM.button('primary', 'camera', 'Camera');
		this.selectCamera.classList.add('rounded-tr-none', 'rounded-br-none');
		this.selectUpload = DOM.button('secondary', 'upload', 'Upload');
		this.selectUpload.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.uploadInput = DOM.create('input', {
			className: 'hidden',
			id: 'create-upload',
			name: 'create-upload',
			type: 'file',
			accept: '.png,.jpg,.jpeg,.webp,.bmp',
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
			childs: [DOM.create('div', { childs: [this.capture, this.cancelCapture] }), this.submit],
		});
		this.decorationSelector = DOM.create('div', { className: 'decorations' });
	}

	async data(): Promise<void> {
		const response = await Http.get<{ list: Decoration[] }>('/api/decorations');
		if (response.ok) {
			this.decorations = { still: [], animated: [] };
			for (const decoration of response.body.list) {
				if (decoration.animated) {
					this.decorations.still.push(decoration);
				} else {
					this.decorations.animated.push(decoration);
				}
			}
		} else {
			Notification.show('danger', `Failed to load Decorations: ${response.body.error}`);
			this.dataError = response;
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
		this.decorationSelector.classList.remove('active');
		navigator.mediaDevices
			.getUserMedia({
				audio: false,
				video: {
					width: { min: MIN_WIDTH, ideal: MAX_WIDTH, max: MAX_WIDTH },
					height: { min: MIN_HEIGHT, ideal: MAX_HEIGHT, max: MAX_HEIGHT },
					frameRate: 30,
				},
			})
			.then((stream) => {
				this.videoPreview.classList.remove('hidden');
				this.videoPreview.src = '';
				this.videoPreview.srcObject = stream;
				this.enableCapture();
				this.submit.disabled = true;
				this.decorationSelector.classList.add('active');
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.add('hidden');
			})
			.catch((error) => {
				this.hidePreviews();
				this.disableCapture();
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.remove('hidden');
				this.decorationSelector.classList.remove('active');
			});
	}

	private uploadMode(file: string): void {
		this.hidePreviews();
		this.disableCapture();
		this.decorationSelector.classList.add('active');
		this.submit.disabled = false;
		this.imagePreview.src = file;
		this.imagePreview.classList.remove('hidden');
	}

	private setCapturePreview(image: string): void {
		this.imagePreview.src = image;
		this.imagePreview.classList.remove('hidden');
		this.videoPreview.classList.add('hidden');
		this.capture.classList.add('hidden');
		this.cancelCapture.classList.remove('hidden');
		this.decorationSelector.classList.add('active');
		this.submit.disabled = false;
	}

	private getScale(): number {
		return Math.max(this.preview.offsetWidth / OPTIMAL_WIDTH, this.preview.offsetHeight / OPTIMAL_HEIGHT);
	}

	private resize(): void {
		if (this.dragState) {
			const scale = this.getScale();
			this.dragState.node.style.setProperty('--tw-scale-x', `${scale}`);
			this.dragState.node.style.setProperty('--tw-scale-y', `${scale}`);
		}
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
					const result = reader.result as string;
					// Check size, approximation
					if (3 * (result.length / 4) >= 10_000_000) {
						Notification.show('danger', `Size limit is 10 MB.`);
						return;
					}
					// Check extension
					const mime = /data:(image|video)\/([a-zA-Z]{2,5});base64,/.exec(result);
					if (mime === null || mime[1] == 'video') {
						Notification.show('danger', `No valid file type found.`);
						return;
					}
					const type = mime[1].toLocaleLowerCase();
					const extension = mime[2].toLocaleLowerCase();
					if (type !== 'image' || ['png', 'jpeg', 'jpg', 'webp', 'bmp'].indexOf(extension) < 0) {
						Notification.show('danger', `You can only add png, jpeg, jpg, webp or bmp files.`);
						return;
					}
					// Check dimensions
					const image = new Image();
					image.src = result;
					// On next frame to wait for image to load
					requestAnimationFrame(() => {
						const [width, height] = [image.naturalWidth, image.naturalHeight];
						console.log('found dimensions', width, height);
						if (width < MIN_WIDTH || height < MIN_HEIGHT) {
							Notification.show('warning', `Minimum dimensions are ${MIN_WIDTH}x${MIN_HEIGHT}px.`);
							return;
						}
						if (width > MAX_WIDTH || height > MAX_HEIGHT) {
							Notification.show('warning', `Maximum dimensions are ${MAX_WIDTH}x${MAX_HEIGHT}px.`);
							return;
						}
						// Display
						this.uploadMode(result);
					});
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
		this.submit.addEventListener('click', async (event) => {
			event.preventDefault();
			this.decorationSelector.classList.remove('active');
			await this.runOnce(
				this.submit,
				async () => {
					if (this.currentDecoration === undefined) {
						Notification.show('danger', `You need to add one Decoration before submitting your creation.`);
						return;
					}
					const response = await Http.post<{ success: string; id: number }>('/api/upload', {
						upload: this.imagePreview.src,
						decoration: {
							id: this.currentDecoration.id,
							position: this.dragState.current,
						},
						scale: this.getScale(),
					});
					if (response.ok) {
						Notification.show('success', `Image created.`);
						//this.application.navigate(`/${response.body.id}`);
					} else {
						Notification.show('danger', `Could not upload creation: ${response.body.error}`);
					}
				},
				[this.selectCamera, this.selectUpload, this.capture, this.cancelCapture, this.submit]
			);
			this.decorationSelector.classList.add('active');
		});
		window.addEventListener('resize', this.resize, true);
	}

	private createDecoration(decoration: Decoration, observer?: IntersectionObserver): HTMLElement {
		const isImage = !decoration.animated;
		let src = `/decorations/${decoration.name}`;
		let dataSrc = '';
		if (observer) {
			dataSrc = src;
			src = isImage ? LOADING_IMG : LOADING_VIDEO;
		}
		const node = DOM.create(isImage ? 'img' : 'video', {
			src: src,
			dataset: { src: dataSrc },
			autoplay: true,
			loop: true,
			volume: 0,
		});
		if (observer) observer.observe(node);
		return node;
	}

	// TODO: Fix
	private defaultPosition(decoration: Decoration, layer: HTMLElement): XYPosition {
		const box = this.preview.getBoundingClientRect();
		const dimensions = layer.getBoundingClientRect();
		console.log('position for', box, dimensions);
		const position = { x: 0, y: 0 };
		switch (decoration.position) {
			case 'top-right':
				position.x = box.width - dimensions.width;
				break;
			case 'bottom-left':
				position.y = box.height - dimensions.height;
				break;
			case 'bottom-right':
				position.x = box.width - dimensions.width;
				position.y = box.height - dimensions.height;
				break;
		}
		return position;
	}

	private translate(node: HTMLElement, position: XYPosition): void {
		node.style.setProperty('--tw-translate-x', `${position.x}px`);
		node.style.setProperty('--tw-translate-y', `${position.y}px`);
	}

	private dragStart(event: TouchEvent | MouseEvent): void {
		event.preventDefault();

		// Calculate
		const currentState: XYPosition = { x: 0, y: 0 };
		if (event.type === 'touchstart') {
			currentState.x = (event as TouchEvent).touches[0].clientX - this.dragState.current.x;
			currentState.y = (event as TouchEvent).touches[0].clientY - this.dragState.current.y;
		} else {
			currentState.x = (event as MouseEvent).clientX - this.dragState.current.x;
			currentState.y = (event as MouseEvent).clientY - this.dragState.current.y;
		}

		// Save
		this.dragState.initial = currentState;
		if (event.target === this.dragState.node) {
			this.dragState.active = true;
		}
	}

	private drag(event: TouchEvent | MouseEvent): void {
		if (this.dragState.active) {
			event.preventDefault();
			if (event.type === 'touchmove') {
				this.dragState.current.x = (event as TouchEvent).touches[0].clientX - this.dragState.initial.x;
				this.dragState.current.y = (event as TouchEvent).touches[0].clientY - this.dragState.initial.y;
			} else {
				this.dragState.current.x = (event as MouseEvent).clientX - this.dragState.initial.x;
				this.dragState.current.y = (event as MouseEvent).clientY - this.dragState.initial.y;
			}
			this.translate(this.dragState.node, this.dragState.current);
		}
	}

	private dragEnd(_event: TouchEvent | MouseEvent): void {
		this.dragState.initial.x = this.dragState.current.x;
		this.dragState.initial.y = this.dragState.current.y;
		this.dragState.active = false;
	}

	private displayDecoration(decoration: Decoration, observer: IntersectionObserver): void {
		const visual = this.createDecoration(decoration, observer);
		const card = DOM.create('div', {
			className: 'card',
			childs: [visual],
		});
		card.addEventListener('click', (event) => {
			event.preventDefault();
			if (!this.decorationSelector.classList.contains('active')) return;

			// Initialize
			this.currentDecoration = decoration;
			const layer = this.createDecoration(decoration);
			this.dragState = { node: layer, initial: { x: 0, y: 0 }, active: false, current: { x: 0, y: 0 } };
			DOM.clear(this.visibleDecorations);
			this.visibleDecorations.appendChild(layer); // Append first

			// Default position
			requestAnimationFrame(() => {
				const position = this.defaultPosition(decoration, layer);
				this.dragState.initial.x = position.x;
				this.dragState.current.x = position.x;
				this.dragState.initial.y = position.y;
				this.dragState.current.y = position.y;
				// TODO: Scale fit to container instead of optimal width
				this.resize();
				this.translate(layer, position);
			});

			// Adapted from https://www.kirupa.com/html5/drag.htm
			layer.addEventListener('touchstart', (e) => this.dragStart(e), false);
			layer.addEventListener('touchend', (e) => this.dragEnd(e), false);
			layer.addEventListener('touchmove', (e) => this.drag(e), false);
			layer.addEventListener('mousedown', (e) => this.dragStart(e), false);
			layer.addEventListener('mouseup', (e) => this.dragEnd(e), false);
			layer.addEventListener('mousemove', (e) => this.drag(e), false);
		});
		this.decorationSelector.appendChild(card);
	}

	render(): void {
		if (!this.application.auth.user.verified) {
			this.genericError('401', 'Not verified', 'You need to verify your account to create Images.');
			return;
		}
		if (this.dataError) {
			this.genericError(`${this.dataError.status}`, 'Error', this.dataError.body.error);
			return;
		}
		// https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
		const observer = new IntersectionObserver((entries, observer) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) return;
				const img = entry.target as HTMLImageElement;
				img.src = img.dataset.src!;
				observer.unobserve(entry.target);
			}
		});
		for (const decoration of this.decorations.still) {
			this.displayDecoration(decoration, observer);
		}
		for (const decoration of this.decorations.animated) {
			this.displayDecoration(decoration, observer);
		}
		this.cancelCapture.classList.add('hidden');
		this.submit.disabled = true;
		DOM.append(this.parent, this.sourceSelection, this.previewRow, this.actions, this.decorationSelector);
		this.cameraMode();
	}

	destroy(): void {
		window.removeEventListener('resize', this.resize);
	}
}
