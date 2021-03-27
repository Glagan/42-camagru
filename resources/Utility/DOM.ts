export type AppendableElement = HTMLElement | SVGSVGElement | Text;

interface DOMProperties {
	textContent: string;
	childs: AppendableElement[];
}
export type ButtonType = 'primary' | 'success' | 'secondary' | 'error';

export type HeroIcon =
	| 'at-symbol'
	| 'camera'
	| 'chat'
	| 'check'
	| 'check-circle'
	| 'chevron-left'
	| 'cog'
	| 'exclamation'
	| 'exclamation-circle'
	| 'heart'
	| 'information-circle'
	| 'login'
	| 'logout'
	| 'moon'
	| 'photograph'
	| 'plus-circle'
	| 'question-mark-circle'
	| 'save'
	| 'sun'
	| 'upload'
	| 'user'
	| 'user-add'
	| 'x-circle';
export type IconPath = {
	'stroke-linecap'?: 'round';
	'stroke-linejoin'?: 'round';
	'stroke-width'?: number | string;
	d: string;
};
export type IconOptions = {
	width?: string;
	height?: string;
	classes?: string | string[];
	fill?: string;
	stroke?: string;
	viewBox?: string;
};

export class DOM {
	private static createIcon(paths: IconPath[], options?: IconOptions): SVGSVGElement {
		// Default options
		const opts = {
			width: 'w-6',
			height: 'h-6',
			classes: '',
			fill: 'none',
			stroke: 'currentColor',
			viewBox: '0 0 24 24',
			...options,
		};
		if (!Array.isArray(opts.classes)) {
			opts.classes = opts.classes.split(' ').filter((c) => !!c);
		}
		// Create wrapper
		const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
		svg.setAttribute('fill', opts.fill);
		svg.setAttribute('stroke', opts.stroke);
		svg.setAttribute('viewBox', opts.viewBox);
		if (opts.width) svg.classList.add(opts.width);
		if (opts.height) svg.classList.add(opts.height);
		svg.classList.add(...opts.classes);
		// Create the paths
		for (const definition of paths) {
			const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
			path.setAttribute('stroke-linecap', definition['stroke-linecap'] ?? 'round');
			path.setAttribute('stroke-linejoin', definition['stroke-linejoin'] ?? 'round');
			path.setAttribute('stroke-width', `${definition['stroke-width'] ?? 2}`);
			path.setAttribute('d', definition.d);
			svg.appendChild(path);
		}
		return svg;
	}

	static heroIcons: { [key in HeroIcon]: (options?: IconOptions) => SVGSVGElement } = {
		exclamation: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
					},
				],
				options
			),
		check: (options) => DOM.createIcon([{ d: 'M5 13l4 4L19 7' }], options),
		camera: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z',
					},
					{ d: 'M15 13a3 3 0 11-6 0 3 3 0 016 0z' },
				],
				options
			),
		login: (options) =>
			DOM.createIcon(
				[{ d: 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1' }],
				options
			),
		'user-add': (options) =>
			DOM.createIcon(
				[{ d: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' }],
				options
			),
		cog: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
					},
					{ d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
				],
				options
			),
		logout: (options) =>
			DOM.createIcon(
				[{ d: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1' }],
				options
			),
		'chevron-left': (options) => DOM.createIcon([{ d: 'M15 19l-7-7 7-7' }], options),
		'at-symbol': (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207',
					},
				],
				options
			),
		heart: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
					},
				],
				options
			),
		chat: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
					},
				],
				options
			),
		upload: (options) =>
			DOM.createIcon([{ d: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' }], options),
		'plus-circle': (options) =>
			DOM.createIcon([{ d: 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z' }], options),
		user: (options) =>
			DOM.createIcon([{ d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }], options),
		sun: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
					},
				],
				options
			),
		moon: (options) =>
			DOM.createIcon(
				[{ d: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z' }],
				options
			),
		save: (options) =>
			DOM.createIcon(
				[{ d: 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4' }],
				options
			),
		'question-mark-circle': (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
					},
				],
				options
			),
		'information-circle': (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
					},
				],
				options
			),
		'x-circle': (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
					},
				],
				options
			),
		'check-circle': (options) => DOM.createIcon([{ d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }], options),
		'exclamation-circle': (options) =>
			DOM.createIcon([{ d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }], options),
		photograph: (options) =>
			DOM.createIcon(
				[
					{
						d:
							'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
					},
				],
				options
			),
	};

	static icon(name: HeroIcon, options?: IconOptions): SVGSVGElement {
		return DOM.heroIcons[name](options);
	}

	static button(type: ButtonType, iconName: HeroIcon, textContent?: string): HTMLButtonElement {
		const button = document.createElement('button');
		button.classList.add(type);
		button.appendChild(DOM.icon(iconName));
		if (textContent) {
			const content = document.createElement('span');
			content.textContent = textContent;
			button.appendChild(content);
		}
		return button;
	}

	static create<K extends keyof HTMLElementTagNameMap>(
		tagName: K,
		properties?: Partial<HTMLElementTagNameMap[K]> & Partial<DOMProperties>
	): HTMLElementTagNameMap[K] {
		const elt = document.createElement(tagName);
		if (tagName === 'a') {
			(elt as HTMLAnchorElement).rel = 'noreferrer noopener';
		}
		if (properties) {
			Object.assign(elt, properties);
			if (properties.textContent) {
				elt.textContent = properties.textContent;
			}
			if (properties.childs) {
				DOM.append(elt, ...properties.childs);
			}
		}
		return elt;
	}

	static text(text: string | undefined = undefined): Text {
		return document.createTextNode(!text ? '' : text);
	}

	static append(parent: HTMLElement, ...childs: AppendableElement[]): HTMLElement {
		for (const child of childs) {
			parent.appendChild(child);
		}
		return parent;
	}

	static clear(node: HTMLElement) {
		while (node.firstChild) {
			node.removeChild(node.firstChild);
		}
	}

	static validateInput(node: HTMLInputElement, validator: (value: string) => string | true): void {
		let timeout = 0;
		node.addEventListener('input', (event) => {
			node.classList.remove('error');
			clearTimeout(timeout);
			timeout = setTimeout(() => {
				if (validator(node.value) !== true) {
					node.classList.add('error');
				}
			}, 100);
		});
	}
}
