import { Component } from '../Component';
import { Alert } from '../UI/Alert';
import { LOADING_IMG, LOADING_VIDEO } from '../UI/Loading';
import { Notification } from '../UI/Notification';
import { DOM } from '../Utility/DOM';
import { Http, InvalidHttpResponse } from '../Utility/Http';
import { Observer } from '../Utility/Observer';

type ActiveDecoration = {
	decoration: Decoration;
	node: HTMLElement;
	initial: XYPosition;
	active: boolean;
	current: XYPosition;
	percent: XYPosition;
};
type DecorationState = { decoration: Decoration; node: HTMLElement; position: XYPosition };
type CaptureState = {
	header: HTMLElement;
	node: HTMLElement;
	preview: HTMLImageElement;
	wrapper: HTMLElement;
	background: string;
	decorations: DecorationState[];
};
export type XYPosition = { x: number; y: number };

const MIN_WIDTH = 854;
const MAX_WIDTH = 2560;
const MIN_HEIGHT = 480;
const MAX_HEIGHT = 1440;

export class Create extends Component {
	static auth = true;

	mode: 'camera' | 'upload' | 'none' = 'none';
	locked: boolean = false;
	currentUpload?: string;
	pendingCamera: boolean = false;
	decorations: { still: Decoration[]; animated: Decoration[] } = { still: [], animated: [] };
	dataError: InvalidHttpResponse<{ error: string }> | undefined;

	cameraModeButton!: HTMLButtonElement;
	uploadInput!: HTMLInputElement;
	uploadModeButton!: HTMLButtonElement;
	switchModeWrapper!: HTMLElement;
	captureButton!: HTMLButtonElement;
	cancelCaptureButton!: HTMLButtonElement;
	submitButton!: HTMLButtonElement;
	actionsWrapper!: HTMLElement;
	header!: HTMLElement;

	allowCamera!: HTMLElement;
	noCamera!: HTMLElement;
	videoPreview!: HTMLVideoElement;
	imagePreview!: HTMLImageElement;
	visibleDecorations!: HTMLElement;
	previewContainer!: HTMLElement;
	previousCaptures: CaptureState[] = [];
	previousCapturesWrapper!: HTMLElement;
	previewWrapper!: HTMLElement;

	activeDecorations: ActiveDecoration[] = [];
	activeDecorationsWrapper!: HTMLElement;
	decorationSelector!: HTMLElement;

	create(): void {
		// Header
		// Switch mode
		this.cameraModeButton = DOM.button('primary', 'camera', 'Camera');
		this.cameraModeButton.classList.add('rounded-tr-none', 'rounded-br-none');
		this.uploadModeButton = DOM.button('secondary', 'upload', 'Upload');
		this.uploadModeButton.classList.add('border-l-0', 'rounded-tl-none', 'rounded-bl-none');
		this.uploadInput = DOM.create('input', {
			className: 'hidden',
			id: 'create-upload',
			name: 'create-upload',
			type: 'file',
			accept: '.png,.jpg,.jpeg,.webp,.bmp',
		});
		this.switchModeWrapper = DOM.create('div', {
			className: 'flex',
			childs: [this.cameraModeButton, this.uploadModeButton, this.uploadInput],
		});
		// Capture and Submit
		this.captureButton = DOM.button('primary', 'camera', 'Capture');
		this.captureButton.classList.add('hidden');
		this.cancelCaptureButton = DOM.button('error', 'x-circle', 'Cancel');
		this.cancelCaptureButton.classList.add('hidden');
		this.submitButton = DOM.button('success', 'plus-circle', 'Submit');
		this.actionsWrapper = DOM.create('div', {
			className: 'flex flex-row flex-nowrap space-x-2',
			childs: [this.captureButton, this.cancelCaptureButton, this.submitButton],
		});
		this.header = DOM.create('div', {
			className: 'flex justify-between',
			childs: [this.switchModeWrapper, this.actionsWrapper],
		});
		// Preview
		this.videoPreview = DOM.create('video', {
			className: 'hidden preview',
			loop: true,
			autoplay: true,
			volume: 0,
			playsInline: true,
			muted: true,
		});
		this.imagePreview = DOM.create('img', { className: 'hidden preview' });
		this.allowCamera = Alert.make('info', 'You need to Allow the Camera on the top left to be able to use it.');
		this.noCamera = Alert.make('danger', 'Error while accessing your Camera.');
		this.visibleDecorations = DOM.create('div', { className: 'preview-decorations' });
		this.previewContainer = DOM.create('div', {
			className: 'relative preview-container',
			childs: [this.allowCamera, this.noCamera, this.videoPreview, this.imagePreview, this.visibleDecorations],
		});
		this.previousCapturesWrapper = DOM.create('div', {
			className: 'hidden previous-capture',
		});
		this.previewWrapper = DOM.create('div', {
			className: 'flex items-start justify-center mt-2',
			childs: [this.previewContainer, this.previousCapturesWrapper],
		});
		// Decorations
		this.activeDecorationsWrapper = DOM.create('div', {
			className: 'hidden active-decorations',
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

	get hasDecorations(): boolean {
		return this.activeDecorations.length > 0;
	}

	get hasAnimatedDecoration(): boolean {
		return this.activeDecorations.findIndex((d) => d.decoration.animated) >= 0;
	}

	get scale(): number {
		if (!this.locked && this.mode == 'camera' && this.videoPreview.readyState == 4) {
			return this.previewContainer.offsetWidth / this.videoPreview.videoWidth;
		}
		return this.previewContainer.offsetWidth / this.imagePreview.naturalWidth;
	}

	reset() {
		this.locked = false;
		// Static preview
		this.imagePreview.src = '';
		this.imagePreview.classList.add('hidden');
		// Remove video preview if there was one
		this.videoPreview.pause();
		this.videoPreview.removeAttribute('src');
		this.videoPreview.load();
		this.videoPreview.classList.add('hidden');
		// Alerts
		this.allowCamera.classList.add('hidden');
		this.noCamera.classList.add('hidden');
		// Actions
		this.submitButton.classList.add('hidden');
		this.cancelCaptureButton.classList.add('hidden');
		this.captureButton.classList.remove('hidden');
		this.previousCapturesWrapper.classList.add('active');
		this.captureButton.disabled = true;
		this.uploadModeButton.disabled = false;
		// Decorations
		this.activeDecorations = [];
		DOM.clear(this.activeDecorationsWrapper);
		DOM.clear(this.visibleDecorations);
		this.activeDecorationsWrapper.classList.add('active', 'hidden');
		this.previousCaptures = [];
		DOM.clear(this.previousCapturesWrapper);
		this.previousCapturesWrapper.classList.add('hidden');
		this.decorationSelector.classList.remove('active');
	}

	setMode(mode: 'camera'): Promise<void>;
	setMode(mode: 'upload', bacground: string): Promise<void>;
	async setMode(mode: 'camera' | 'upload', background?: string): Promise<void> {
		if (this.pendingCamera) return;
		this.reset();
		if (mode == 'camera') {
			this.cameraModeButton.disabled = true;
			// Display alerts
			this.pendingCamera = true;
			this.allowCamera.classList.remove('hidden');
			this.noCamera.classList.add('hidden');
			try {
				const stream = await navigator.mediaDevices.getUserMedia({
					audio: false,
					video: {
						width: { min: MIN_WIDTH, ideal: MAX_WIDTH, max: MAX_WIDTH },
						height: { min: MIN_HEIGHT, ideal: MAX_HEIGHT, max: MAX_HEIGHT },
						frameRate: 60,
					},
				});
				this.videoPreview.classList.remove('hidden');
				this.videoPreview.src = '';
				this.videoPreview.srcObject = stream;
				this.decorationSelector.classList.add('active');
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.add('hidden');
			} catch (error) {
				this.allowCamera.classList.add('hidden');
				this.noCamera.classList.remove('hidden');
			} finally {
				this.pendingCamera = false;
			}
		} else if (mode === 'upload') {
			this.cameraModeButton.disabled = false;
			this.decorationSelector.classList.add('active');
			this.imagePreview.src = background!;
			this.imagePreview.classList.remove('hidden');
			// Nothing to do in Upload mode, only a static background is added
			// and all of the UI is reset before
		}
		this.mode = mode;
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
			playsInline: true,
			muted: true,
		});
		if (observer) observer.observe(node);
		return node;
	}

	private translate(node: HTMLElement, position: XYPosition): void {
		node.style.setProperty('--tw-translate-x', `${position.x}px`);
		node.style.setProperty('--tw-translate-y', `${position.y}px`);
	}

	private dragStart(event: TouchEvent | MouseEvent, state: ActiveDecoration): void {
		event.preventDefault();
		if (this.locked) return;

		// Calculate
		const currentState: XYPosition = { x: 0, y: 0 };
		if (event.type === 'touchstart') {
			currentState.x = (event as TouchEvent).touches[0].clientX - state.current.x;
			currentState.y = (event as TouchEvent).touches[0].clientY - state.current.y;
		} else {
			currentState.x = (event as MouseEvent).clientX - state.current.x;
			currentState.y = (event as MouseEvent).clientY - state.current.y;
		}

		// Save
		state.initial = currentState;
		if (event.target === state.node) {
			state.active = true;
		}
	}

	private drag(event: TouchEvent | MouseEvent, state: ActiveDecoration): void {
		if (state.active) {
			event.preventDefault();
			if (event.type === 'touchmove') {
				state.current.x = (event as TouchEvent).touches[0].clientX - state.initial.x;
				state.current.y = (event as TouchEvent).touches[0].clientY - state.initial.y;
			} else {
				state.current.x = (event as MouseEvent).clientX - state.initial.x;
				state.current.y = (event as MouseEvent).clientY - state.initial.y;
			}
			state.percent = {
				x: state.current.x / this.previewContainer.offsetWidth,
				y: state.current.y / this.previewContainer.offsetHeight,
			};
			this.translate(state.node, state.current);
		}
	}

	private dragEnd(_event: TouchEvent | MouseEvent, state: ActiveDecoration): void {
		state.initial = { ...state.current };
		state.active = false;
	}

	private bindActiveDecoration(state: ActiveDecoration): void {
		// Adapted from https://www.kirupa.com/html5/drag.htm
		state.node.addEventListener('touchstart', (e) => this.dragStart(e, state), false);
		state.node.addEventListener('touchend', (e) => this.dragEnd(e, state), false);
		state.node.addEventListener('touchmove', (e) => this.drag(e, state), false);
		state.node.addEventListener('mousedown', (e) => this.dragStart(e, state), false);
		state.node.addEventListener('mouseup', (e) => this.dragEnd(e, state), false);
		state.node.addEventListener('mousemove', (e) => this.drag(e, state), false);
	}

	private insertDecoration(decoration: Decoration): ActiveDecoration {
		const state: ActiveDecoration = {
			decoration: decoration,
			node: this.createDecoration(decoration),
			initial: { x: 0, y: 0 },
			active: false,
			current: { x: 0, y: 0 },
			percent: { x: 1, y: 1 },
		};
		this.activeDecorations.push(state);
		this.visibleDecorations.appendChild(state.node);
		this.bindActiveDecoration(state);
		const quickDecoration = this.createDecoration(decoration);
		quickDecoration.title = 'Click to remove.';
		quickDecoration.addEventListener('click', (event) => {
			event.preventDefault();
			const index = this.activeDecorations.findIndex((d) => d.decoration.id == state.decoration.id);
			if (index >= 0) this.activeDecorations.splice(index, 1);
			state.node.remove();
			quickDecoration.remove();
			if (this.activeDecorations.length == 0) {
				this.activeDecorationsWrapper.classList.add('hidden');
			}
			this.decorationSelector.classList.add('active');
		});
		this.activeDecorationsWrapper.appendChild(quickDecoration);
		if (this.activeDecorations.length == 1) {
			this.activeDecorationsWrapper.classList.remove('hidden');
		}
		if (this.activeDecorations.length == 5) {
			this.decorationSelector.classList.remove('active');
		}
		return state;
	}

	restorePreviousState(state: CaptureState) {
		// Replace background and decorations with the saved state
		this.imagePreview.src = state.background;
		this.activeDecorations = [];
		DOM.clear(this.activeDecorationsWrapper);
		DOM.clear(this.visibleDecorations);
		for (const frozenDecoration of state.decorations) {
			const newState = this.insertDecoration(frozenDecoration.decoration);
			// Set the decoration to the same position as it was on capture
			const percent = { ...frozenDecoration.position };
			const position = {
				x: this.previewContainer.offsetWidth * percent.x,
				y: this.previewContainer.offsetHeight * percent.y,
			};
			newState.initial = { ...position };
			newState.current = { ...position };
			newState.percent = percent;
		}
		// Set the decoration to the same position as it was on capture
		// on the next frame to wait until they exist
		requestAnimationFrame(() => this.repositionActiveDecorations());
		// Enable the Submit interface
		this.captureButton.classList.add('hidden');
		this.captureButton.disabled = false;
		this.cancelCaptureButton.classList.remove('hidden');
		this.submitButton.classList.remove('hidden');
		this.activeDecorationsWrapper.classList.remove('active');
		this.decorationSelector.classList.remove('active');
		this.previousCapturesWrapper.classList.remove('active');
	}

	async saveCurrentState() {
		// Deep copy to avoid references
		const decorations: DecorationState[] = [];
		for (const decoration of this.activeDecorations) {
			decorations.push({
				decoration: decoration.decoration,
				node: this.createDecoration(decoration.decoration),
				position: { ...decoration.percent },
			});
		}

		// Create the node in the list
		const background = this.imagePreview.src;
		const header = DOM.create('h1');
		const preview = DOM.create('img', { src: background });
		const wrapper = DOM.create('div', { className: 'preview-decorations' });
		const container = DOM.create('div', { className: 'relative', childs: [preview, wrapper] });
		const node = DOM.create('div', {
			className: 'cursor-pointer',
			title: 'Click to restore capture',
			childs: [header, container],
		});
		const state: CaptureState = { header, node, preview, wrapper, background, decorations };
		node.addEventListener('click', (event) => {
			event.preventDefault();
			if (!this.previousCapturesWrapper.classList.contains('active')) return;
			// Move the current capture to the first spot
			const index = this.previousCaptures.findIndex((s) => s === state);
			if (index > 0) {
				this.previousCaptures.splice(index, 1);
				this.previousCaptures.unshift(state);
				this.previousCapturesWrapper.insertBefore(node, this.previousCapturesWrapper.firstElementChild);
				for (let i = 0; i < this.previousCaptures.length; i++) {
					const previousNode = this.previousCaptures[i];
					previousNode.header.textContent = `#${i + 1}`;
				}
			}
			this.restorePreviousState(state);
		});

		// Add to the list and display
		this.previousCaptures.unshift(state);
		if (this.previousCaptures.length > 5) {
			const last = this.previousCaptures.pop();
			if (last) last.node.remove();
		}
		for (let i = 0; i < this.previousCaptures.length; i++) {
			const previousNode = this.previousCaptures[i];
			previousNode.header.textContent = `#${i + 1}`;
		}
		this.previousCapturesWrapper.insertBefore(node, this.previousCapturesWrapper.firstElementChild);
		this.previousCapturesWrapper.classList.remove('hidden');

		// Resize the scaled down decorations once the
		requestAnimationFrame(() => {
			// Display the active decorations, scaled down and at the right position
			const scale = wrapper.offsetWidth / preview.naturalWidth;
			for (const state of decorations) {
				wrapper.appendChild(state.node);
				this.resize(state.node, scale);
				this.reposition(wrapper, state.node, state.position);
			}
		});

		// Resize and reposition decorations after the interface is resized
		if (this.previousCaptures.length == 1) {
			requestAnimationFrame(() => {
				this.repositionActiveDecorations();
			});
		}
	}

	capture() {
		this.locked = true;
		// Update preview
		if (this.mode == 'camera') {
			const canvas = DOM.create('canvas');
			canvas.width = this.videoPreview.videoWidth;
			canvas.height = this.videoPreview.videoHeight;
			const context = canvas.getContext('2d')!;
			context.drawImage(this.videoPreview, 0, 0, canvas.width, canvas.height);
			const image = canvas.toDataURL('image/png');
			this.imagePreview.src = image;
			this.imagePreview.classList.remove('hidden');
			this.videoPreview.classList.add('hidden');
		}
		// Update interface
		this.uploadModeButton.disabled = true;
		this.captureButton.classList.add('hidden');
		this.cancelCaptureButton.classList.remove('hidden');
		this.submitButton.classList.remove('hidden');
		this.decorationSelector.classList.remove('active');
		this.activeDecorationsWrapper.classList.remove('active');
		this.previousCapturesWrapper.classList.remove('active');
		this.saveCurrentState();
	}

	cancelCapture() {
		this.locked = false;
		// Update preview
		if (this.mode == 'camera') {
			this.imagePreview.src = '';
			this.imagePreview.classList.add('hidden');
			this.videoPreview.classList.remove('hidden');
			this.videoPreview.play();
		}
		// Update interface
		this.uploadModeButton.disabled = false;
		this.captureButton.classList.remove('hidden');
		this.cancelCaptureButton.classList.add('hidden');
		this.submitButton.classList.add('hidden');
		this.activeDecorationsWrapper.classList.add('active');
		this.previousCapturesWrapper.classList.add('active');
		if (this.activeDecorations.length < 5) {
			this.decorationSelector.classList.add('active');
		}
	}

	private resize(node: HTMLElement, scale: number): void {
		node.style.setProperty('--tw-scale-x', `${scale}`);
		node.style.setProperty('--tw-scale-y', `${scale}`);
	}

	private reposition(parent: HTMLElement, node: HTMLElement, position: XYPosition): XYPosition {
		const { offsetWidth, offsetHeight } = parent;
		const newPosition: XYPosition = { x: offsetWidth * position.x, y: offsetHeight * position.y };
		this.translate(node, newPosition);
		return newPosition;
	}

	repositionActiveDecorations(): void {
		const scale = this.scale;
		for (const activeDecoration of this.activeDecorations) {
			this.resize(activeDecoration.node, scale);
			const newPosition = this.reposition(this.previewContainer, activeDecoration.node, activeDecoration.percent);
			activeDecoration.initial = { ...newPosition };
			activeDecoration.current = { ...newPosition };
		}
	}

	repositionPreviousDecorations(): void {
		for (const state of this.previousCaptures) {
			const scale = state.wrapper.offsetWidth / state.preview.naturalWidth;
			for (const decoration of state.decorations) {
				this.resize(decoration.node, scale);
				this.reposition(state.wrapper, decoration.node, decoration.position);
			}
		}
	}

	repositionAllDecorations(): void {
		this.repositionActiveDecorations();
		this.repositionPreviousDecorations();
	}

	bind(): void {
		this.videoPreview.addEventListener('loadedmetadata', (event) => {
			this.videoPreview.play();
		});
		this.cameraModeButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.setMode('camera');
		});
		this.uploadModeButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.uploadInput.click();
		});
		this.uploadInput.addEventListener('change', (event) => {
			/// Check if the uploaded background is valid
			if (this.uploadInput.value && this.uploadInput.files?.length) {
				const reader = new FileReader();
				reader.readAsDataURL(this.uploadInput.files[0]);
				reader.addEventListener('load', async () => {
					const result = reader.result as string;
					// Check size, approximation
					if (3 * (result.length / 4) >= 10_000_000) {
						Notification.show('danger', `Size limit is 10 MB.`);
						return;
					}
					// Check extension
					const mime = /data:image\/([a-zA-Z]{2,5});base64,/.exec(result);
					if (mime === null) {
						Notification.show('danger', `No valid file type found.`);
						return;
					}
					const extension = mime[1].toLocaleLowerCase();
					if (['png', 'jpeg', 'jpg', 'webp', 'bmp'].indexOf(extension) < 0) {
						Notification.show('danger', `You can only add png, jpeg, jpg, webp or bmp files.`);
						return;
					}
					// Check dimensions
					const image = new Image();
					image.src = result;
					// Wait for the image to load, no events with base64 images
					let tries = 0;
					while (tries < 5 && (image.naturalWidth === 0 || image.naturalHeight === 0)) {
						const [width, height] = await new Promise((resolve) =>
							requestAnimationFrame(() => resolve([image.naturalWidth, image.naturalHeight]))
						);
						if (image.naturalWidth > 0 && image.naturalHeight > 0) {
							if (width < MIN_WIDTH || height < MIN_HEIGHT) {
								Notification.show('warning', `Minimum dimensions are ${MIN_WIDTH}x${MIN_HEIGHT}px.`);
								return;
							}
							if (width > MAX_WIDTH || height > MAX_HEIGHT) {
								Notification.show('warning', `Maximum dimensions are ${MAX_WIDTH}x${MAX_HEIGHT}px.`);
								return;
							}
						}
						tries++;
					}
					if (tries >= 5) {
						Notification.show('danger', `Failed to find Image dimensions.`);
						return;
					}
					// Display
					if (this.mode == 'upload') {
						this.imagePreview.src = result;
					} else this.setMode('upload', result);
				});
			}
		});
		this.captureButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.capture();
		});
		this.cancelCaptureButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.cancelCapture();
		});
		this.submitButton.addEventListener('click', async (event) => {
			event.preventDefault();
			await this.runOnce(
				this.submitButton,
				async () => {
					if (this.activeDecorations.length < 1) {
						Notification.show(
							'danger',
							`You need to add at least one Decoration before submitting your creation.`
						);
						return;
					}
					Notification.show('info', 'Creating Image...');
					const response = await Http.post<{ success: string; id: number }>('/api/upload', {
						upload: this.imagePreview.src,
						decorations: this.activeDecorations.map((d) => {
							return {
								id: d.decoration.id,
								position: d.percent,
							};
						}),
					});
					if (response.ok) {
						Notification.show('success', `Image created.`);
						this.application.navigate(`/${response.body.id}`);
					} else {
						Notification.show('danger', `Could not upload creation: ${response.body.error}`);
					}
				},
				[
					this.cameraModeButton,
					this.uploadModeButton,
					this.captureButton,
					this.cancelCaptureButton,
					this.submitButton,
				]
			);
		});
		window.addEventListener('resize', (e) => this.repositionAllDecorations(), true);
	}

	private async defaultPosition(decoration: Decoration, node: HTMLElement): Promise<XYPosition> {
		const position = { x: 0, y: 0 };
		// Nothing to wait for if the position is top left
		if (decoration.position === 'top-left') {
			return position;
		}
		// Wait for the Video or Image to load to have the real dimensions
		if (
			(node.tagName === 'IMG' && !(node as HTMLImageElement).complete) ||
			(node.tagName === 'VIDEO' && (node as HTMLVideoElement).readyState !== 4)
		) {
			await new Promise<void>((resolve) => node.addEventListener('loadeddata', () => resolve()));
		}
		// We need the translation computed position of the decoration
		const rect = node.getBoundingClientRect();
		const { offsetWidth, offsetHeight } = this.previewContainer;
		switch (decoration.position) {
			case 'top-center':
				position.x = offsetWidth / 2 - rect.width / 2;
				break;
			case 'top-right':
				position.x = offsetWidth - rect.width;
				break;
			case 'center-left':
				position.x = offsetWidth / 2 - rect.width / 2;
				position.y = offsetHeight - rect.height;
				break;
			case 'center-center':
				position.x = offsetWidth / 2 - rect.width / 2;
				position.y = offsetHeight / 2 - rect.height / 2;
				break;
			case 'center-right':
				position.x = offsetWidth - rect.width;
				position.y = offsetHeight / 2 - rect.height / 2;
				break;
			case 'bottom-left':
				position.y = offsetHeight - rect.height;
				break;
			case 'bottom-center':
				position.y = offsetHeight - rect.height;
				position.x = offsetWidth / 2 - rect.width / 2;
				break;
			case 'bottom-right':
				position.x = offsetWidth - rect.width;
				position.y = offsetHeight - rect.height;
				break;
		}
		return position;
	}

	private addDecorationToList(decoration: Decoration, observer: IntersectionObserver): void {
		const visual = this.createDecoration(decoration, observer);
		const card = DOM.create('div', { className: 'card', childs: [visual] });
		card.addEventListener('click', (event) => {
			event.preventDefault();
			if (!this.decorationSelector.classList.contains('active')) return;
			if (decoration.animated && this.hasAnimatedDecoration) {
				Notification.show('info', 'You can only have 1 animated decoration.');
				return;
			}
			if (this.activeDecorations.length == 5) {
				Notification.show('info', 'You can only have up to 5 decorations.');
				return;
			}
			this.captureButton.disabled = false;

			// Display decoration
			const state = this.insertDecoration(decoration);

			// Default position
			requestAnimationFrame(async () => {
				this.resize(state.node, this.scale);
				const position = await this.defaultPosition(state.decoration, state.node);
				state.initial = { ...position };
				state.current = { ...position };
				state.percent = {
					x: position.x / this.previewContainer.offsetWidth,
					y: position.y / this.previewContainer.offsetHeight,
				};
				this.translate(state.node, position);
			});
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
		const observer = Observer.get();
		for (const decoration of this.decorations.still) {
			this.addDecorationToList(decoration, observer);
		}
		for (const decoration of this.decorations.animated) {
			this.addDecorationToList(decoration, observer);
		}
		DOM.append(
			this.parent,
			this.header,
			this.previewWrapper,
			this.activeDecorationsWrapper,
			this.decorationSelector
		);
		this.setMode('camera');
	}

	destroy(): void {
		window.removeEventListener('resize', this.repositionAllDecorations);
	}
}
