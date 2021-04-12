export class Observer {
	static get() {
		// https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
		return new IntersectionObserver((entries, observer) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) return;
				const img = entry.target as HTMLImageElement;
				img.src = img.dataset.src!;
				observer.unobserve(entry.target);
			}
		});
	}
}
