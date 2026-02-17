(function () {
	const { __ } = wp.i18n;

	function setButtonState(button, text, isSuccess) {
		const original = button.getAttribute('data-original-text') || button.textContent;

		if (!button.getAttribute('data-original-text')) {
			button.setAttribute('data-original-text', original);
		}

		button.textContent = text;
		button.classList.toggle('button-primary', !!isSuccess);
		button.classList.toggle('button-secondary', !isSuccess);

		setTimeout(function () {
			button.textContent = original;
			button.classList.remove('button-primary');
			button.classList.remove('button-secondary');
		}, 1200);
	}

	function fallbackCopy(text) {
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.setAttribute('readonly', '');
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();
		textarea.setSelectionRange(0, textarea.value.length);

		let success = false;
		try {
			success = document.execCommand('copy');
		} catch (err) {
			success = false;
		}

		document.body.removeChild(textarea);
		return success;
	}

	function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).then(function () {
				return true;
			});
		}

		return Promise.resolve(fallbackCopy(text));
	}

	document.addEventListener('click', function (event) {
		const button = event.target.closest('.lp-copy-course-url');
		if (!button) {
			return;
		}

		event.preventDefault();
		const url = button.getAttribute('data-url') || '';
		if (!url) {
			setButtonState(button, __('No URL', 'learnpress-add-course-url'), false);
			return;
		}

		copyText(url)
			.then(function (success) {
				setButtonState(button, success ? __('Copied!', 'learnpress-add-course-url') : __('Copy failed', 'learnpress-add-course-url'), success);
			})
			.catch(function () {
				setButtonState(button, __('Copy failed', 'learnpress-add-course-url'), false);
			});
	});
})();
