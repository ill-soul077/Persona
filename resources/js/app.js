import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global toast store
document.addEventListener('alpine:init', () => {
	Alpine.data('toastStore', () => ({
		toasts: [],
		add(type, title, message, timeout = 2500) {
			const id = Date.now() + Math.random();
			this.toasts.push({ id, type, title, message, show: true });
			setTimeout(() => this.dismiss(id), timeout);
		},
		dismiss(id) {
			const i = this.toasts.findIndex(t => t.id === id);
			if (i > -1) {
				this.toasts[i].show = false;
				setTimeout(() => this.toasts.splice(i, 1), 200);
			}
		}
	}));

	window.addEventListener('toast', e => {
		const root = document.querySelector('[x-data="toastStore"]');
		if (root && root.__x) root.__x.$data.add(e.detail.type, e.detail.title, e.detail.message);
	});
});

// Simple page loading bar on navigation
window.addEventListener('DOMContentLoaded', () => {
	const bar = document.getElementById('page-loading-bar');
	if (!bar) return;
	const start = () => { 
		bar.style.width = '0%'; 
		requestAnimationFrame(() => bar.style.width = '70%'); 
	};
	const end = () => { 
		bar.style.width = '100%'; 
		setTimeout(() => { bar.style.width = '0%'; }, 300); 
	};
	
	// Use beforeunload instead of click to avoid blocking
	window.addEventListener('beforeunload', start);
	window.addEventListener('pageshow', end);
});

// Open chatbot shortcut
document.addEventListener('open-chatbot', () => {
	window.location.href = '/chatbot';
});

Alpine.start();
