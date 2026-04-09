import Alpine from './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

const idrFormatter = new Intl.NumberFormat('id-ID');
window.formatRupiah = (value) => idrFormatter.format(Number(value || 0));

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	const normalizeDigits = (value) => value.replace(/\D/g, '');

	document.querySelectorAll('[data-currency-input="idr"]').forEach((displayInput) => {
		const hiddenInputId = displayInput.dataset.currencyTarget;
		const hiddenInput = hiddenInputId
			? document.getElementById(hiddenInputId)
			: displayInput.closest('form')?.querySelector('input[type="hidden"][name]');
		const form = displayInput.closest('form');

		if (!hiddenInput || !form) {
			return;
		}

		const syncValue = () => {
			const digits = normalizeDigits(displayInput.value);

			hiddenInput.value = digits;
			displayInput.value = digits ? 'Rp ' + window.formatRupiah(digits) : '';
		};

		displayInput.addEventListener('input', syncValue);
		displayInput.addEventListener('blur', syncValue);

		form.addEventListener('submit', () => {
			hiddenInput.value = normalizeDigits(displayInput.value);
		});
	});

	document.querySelectorAll('[data-anggota-search]').forEach((searchContainer) => {
		const searchInput = searchContainer.querySelector('#anggota_lookup');
		const hiddenInput = searchContainer.querySelector('#anggota_id');
		const resultsContainer = searchContainer.querySelector('[data-search-results]');
		const searchUrl = searchContainer.dataset.searchUrl;

		if (!searchInput || !hiddenInput || !resultsContainer || !searchUrl) {
			return;
		}

		let debounceTimer = null;

		const syncSelectedAnggota = (value) => {
			hiddenInput.value = value;
			hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
			hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
		};

		const hideResults = () => {
			resultsContainer.classList.add('hidden');
			resultsContainer.innerHTML = '';
		};

		const renderResults = (items) => {
			if (!items.length) {
				resultsContainer.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Anggota tidak ditemukan.</div>';
				resultsContainer.classList.remove('hidden');
				return;
			}

			resultsContainer.innerHTML = items.map((item) => {
				return '<button type="button" class="flex w-full items-start px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-slate-50" data-option-id="'
					+ item.id
					+ '" data-option-label="'
					+ item.label
					+ '">'
					+ item.label
					+ '</button>';
			}).join('');

			resultsContainer.classList.remove('hidden');
		};

		resultsContainer.addEventListener('click', (event) => {
			const button = event.target.closest('[data-option-id]');

			if (!button) {
				return;
			}

			syncSelectedAnggota(button.dataset.optionId);
			searchInput.value = button.dataset.optionLabel;
			hideResults();
		});

		searchInput.addEventListener('input', () => {
			syncSelectedAnggota('');
			const keyword = searchInput.value.trim();

			window.clearTimeout(debounceTimer);

			if (keyword.length < 3) {
				hideResults();
				return;
			}

			debounceTimer = window.setTimeout(async () => {
				try {
					const response = await fetch(searchUrl + '?q=' + encodeURIComponent(keyword), {
						headers: {
							Accept: 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
						},
					});

					if (!response.ok) {
						throw new Error('Gagal mencari anggota.');
					}

					const payload = await response.json();
					renderResults(Array.isArray(payload.data) ? payload.data : []);
				} catch (error) {
					const message = error instanceof Error ? error.message : 'Gagal mencari anggota.';
					resultsContainer.innerHTML = '<div class="px-4 py-3 text-sm text-rose-600">' + message + '</div>';
					resultsContainer.classList.remove('hidden');
				}
			}, 250);
		});

		document.addEventListener('click', (event) => {
			if (!searchContainer.contains(event.target)) {
				hideResults();
			}
		});
	});
});
