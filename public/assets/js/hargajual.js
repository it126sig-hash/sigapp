/**
 * Modul Harga Jual / Pricelist
 * Externalized JS for app/Views/hargajual/index.php
 */
(function ($) {
	'use strict';

	const config = window.HARGAJUAL_CONFIG || {};
	const baseUrl = (config.baseUrl || (typeof window.base_url !== 'undefined' ? window.base_url : '')).replace(/\/+$/, '');
	const controller = config.controller || 'hargajual';

	const MAX_RAW_IMAGE_DATA_URL_CHARS = 38000;
	const MAX_KETERANGAN_BYTES = 62000;
	const HARD_KETERANGAN_BYTES = 64000;

	let url = '';
	let quill;

	function getCsrfName() {
		if (typeof window.csrfName !== 'undefined' && window.csrfName) {
			return window.csrfName;
		}
		return config.csrfName || 'csrf_test_name';
	}

	function getCsrfHash() {
		if (typeof window.csrfHash !== 'undefined' && window.csrfHash) {
			return window.csrfHash;
		}
		return config.csrfHash || '';
	}

	function setCsrfHash(newToken) {
		if (!newToken) return;
		config.csrfHash = newToken;
		if (typeof window.csrfHash !== 'undefined') {
			window.csrfHash = newToken;
		}
	}

	function resolveActiveProyek() {
		if (typeof window.activeProyekId === 'function') {
			return window.activeProyekId();
		}
		if (window.SIGAPP && window.SIGAPP.activeProyekId) {
			return window.SIGAPP.activeProyekId;
		}
		return config.activeProyekId || null;
	}

	function getTableScrollY() {
		var windowHeight = $(window).height();
		// Offset 350px agar tinggi table proporsional dan batas bawah card sejajar harmonis dengan sidebar
		var offset = 350;
		var h = windowHeight - offset;
		return h > 280 ? h + 'px' : '280px';
	}

	function getUtf8ByteLength(str) {
		return new Blob([str || '']).size;
	}

	function renderCompressedImageCandidate(img, maxWidth, qualities) {
		const sourceWidth = img.naturalWidth || img.width || 1;
		const sourceHeight = img.naturalHeight || img.height || 1;
		const scale = sourceWidth > maxWidth ? (maxWidth / sourceWidth) : 1;
		const targetWidth = Math.max(1, Math.round(sourceWidth * scale));
		const targetHeight = Math.max(1, Math.round(sourceHeight * scale));

		const canvas = document.createElement('canvas');
		canvas.width = targetWidth;
		canvas.height = targetHeight;

		const ctx = canvas.getContext('2d');
		if (!ctx) {
			throw new Error('Canvas 2D context unavailable');
		}

		ctx.fillStyle = '#ffffff';
		ctx.fillRect(0, 0, targetWidth, targetHeight);
		ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

		const webpProbe = canvas.toDataURL('image/webp', 0.82);
		const mimeType = webpProbe.indexOf('data:image/webp') === 0 ? 'image/webp' : 'image/jpeg';

		let smallest = null;
		for (let i = 0; i < qualities.length; i++) {
			const q = qualities[i];
			const candidate = (mimeType === 'image/webp' && q === 0.82)
				? webpProbe
				: canvas.toDataURL(mimeType, q);
			if (!smallest || candidate.length < smallest.length) {
				smallest = candidate;
			}
			if (candidate.length <= MAX_RAW_IMAGE_DATA_URL_CHARS) {
				return { matched: candidate, smallest: smallest };
			}
		}

		return { matched: null, smallest: smallest };
	}

	function preparePastedImageDataUrl(file) {
		return new Promise(function (resolve, reject) {
			if (!file) {
				reject(new Error('Berkas gambar tidak ditemukan'));
				return;
			}

			const reader = new FileReader();
			reader.onerror = function () {
				reject(new Error('Gagal membaca berkas gambar dari clipboard'));
			};
			reader.onload = function (loadEvent) {
				const rawDataUrl = loadEvent && loadEvent.target ? loadEvent.target.result : '';
				if (!rawDataUrl || typeof rawDataUrl !== 'string') {
					reject(new Error('Data gambar clipboard kosong'));
					return;
				}

				if (rawDataUrl.length <= MAX_RAW_IMAGE_DATA_URL_CHARS) {
					resolve(rawDataUrl);
					return;
				}

				const img = new Image();
				img.onerror = function () {
					reject(new Error('Gambar clipboard tidak dapat diproses'));
				};
				img.onload = function () {
					try {
						const pass800 = renderCompressedImageCandidate(img, 800, [0.82, 0.70, 0.55, 0.42]);
						if (pass800.matched) {
							resolve(pass800.matched);
							return;
						}

						const pass600 = renderCompressedImageCandidate(img, 600, [0.65, 0.50]);
						if (pass600.matched) {
							resolve(pass600.matched);
							return;
						}

						let smallest = rawDataUrl;
						if (pass800.smallest && pass800.smallest.length < smallest.length) {
							smallest = pass800.smallest;
						}
						if (pass600.smallest && pass600.smallest.length < smallest.length) {
							smallest = pass600.smallest;
						}
						resolve(smallest);
					} catch (err) {
						reject(err);
					}
				};
				img.src = rawDataUrl;
			};

			reader.readAsDataURL(file);
		});
	}

	function setupQuillImagePaste(quillInstance) {
		if (!quillInstance || !quillInstance.root || quillInstance.root.dataset.imagePasteBound === '1') {
			return;
		}
		quillInstance.root.dataset.imagePasteBound = '1';

		quillInstance.root.addEventListener('paste', function (e) {
			const clipboard = e.clipboardData;
			if (!clipboard || !clipboard.items) return;

			const imageFiles = [];
			for (let i = 0; i < clipboard.items.length; i++) {
				const item = clipboard.items[i];
				if (item.kind === 'file' && item.type && item.type.indexOf('image/') === 0) {
					const file = item.getAsFile();
					if (file) {
						imageFiles.push(file);
					}
				}
			}

			if (imageFiles.length === 0) {
				return;
			}

			// Anti-regression guard: salinan teks Excel/Word sering ikut membawa bitmap pratinjau
			const plainText = (clipboard.getData('text/plain') || '').trim();
			const htmlText = clipboard.getData('text/html') || '';
			if (plainText.length > 0 && htmlText.length > 0 && !/<img\b/i.test(htmlText)) {
				return;
			}

			e.preventDefault();
			e.stopImmediatePropagation();

			const savedRange = quillInstance.getSelection(true);
			let insertIndex = savedRange ? savedRange.index : quillInstance.getLength();
			if (savedRange && savedRange.length > 0) {
				quillInstance.deleteText(savedRange.index, savedRange.length, 'user');
			}

			let chain = Promise.resolve();
			let limitExceeded = false;

			imageFiles.forEach(function (file) {
				chain = chain.then(function () {
					if (limitExceeded) {
						return;
					}
					return preparePastedImageDataUrl(file).then(function (dataUrl) {
						const projectedBytes = getUtf8ByteLength(quillInstance.root.innerHTML) +
							getUtf8ByteLength('<p><img src="' + dataUrl + '"></p>');
						if (projectedBytes > MAX_KETERANGAN_BYTES) {
							limitExceeded = true;
							Swal.fire({
								icon: 'warning',
								title: 'Keterangan Terlalu Besar',
								text: 'Ukuran gambar dan teks pada Keterangan melebihi kapasitas maksimal (~60 KB). Perkecil area tangkapan layar atau hapus gambar sebelumnya.',
								confirmButtonColor: '#2057a3'
							});
							return;
						}

						const safeIndex = Math.min(insertIndex, quillInstance.getLength());
						quillInstance.insertEmbed(safeIndex, 'image', dataUrl, 'user');
						insertIndex = safeIndex + 1;
						quillInstance.setSelection(insertIndex, 0, 'silent');
						$('#keterangan').val(quillInstance.root.innerHTML);
					});
				});
			});

			chain.catch(function () {
				Swal.fire({
					icon: 'warning',
					title: 'Gagal Memproses Gambar',
					text: 'Gambar dari clipboard tidak dapat dibaca atau dikonversi. Coba salin ulang gambar.',
					confirmButtonColor: '#2057a3'
				});
			});
		}, true);
	}

	// Helper untuk Drag & Drop dan Pilih Berkas pada Berkas Lampiran PDF
	function setupDropzone() {
		const $zone = $('#dropzone-filehj');
		const $input = $('#id_filehj');
		const $prompt = $('#dropzone-prompt');
		const $preview = $('#dropzone-preview');
		const $previewName = $('#preview-filename');
		const $previewSize = $('#preview-filesize');

		function formatSize(bytes) {
			if (!bytes || bytes <= 0) return '0 B';
			if (bytes < 1024) return bytes + ' B';
			if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
			return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
		}

		function setStagedFile(file) {
			if (!file) {
				$input.val('');
				$preview.addClass('d-none');
				$prompt.removeClass('d-none');
				return false;
			}

			const isPdf = (file.type && file.type === 'application/pdf') || (file.name && file.name.toLowerCase().endsWith('.pdf'));
			if (!isPdf) {
				Swal.fire({
					icon: 'warning',
					title: 'Format Tidak Sesuai',
					text: 'Hanya berkas berformat PDF (.pdf) yang diperbolehkan.',
					confirmButtonColor: '#2057a3'
				});
				return false;
			}

			if (file.size > 10 * 1024 * 1024) {
				Swal.fire({
					icon: 'warning',
					title: 'Ukuran Terlalu Besar',
					text: 'Ukuran berkas PDF maksimal adalah 10 MB.',
					confirmButtonColor: '#2057a3'
				});
				return false;
			}

			$previewName.text(file.name);
			$previewSize.text(formatSize(file.size));
			$prompt.addClass('d-none');
			$preview.removeClass('d-none');
			return true;
		}

		// Klik untuk memilih berkas
		$zone.off('click').on('click', function (e) {
			if ($(e.target).closest('#btn-cancel-file').length) return;
			$input.trigger('click');
		});

		$input.off('change').on('change', function () {
			if (this.files && this.files[0]) {
				if (!setStagedFile(this.files[0])) {
					$input.val('');
				}
			}
		});

		$('#btn-cancel-file').off('click').on('click', function (e) {
			e.stopPropagation();
			setStagedFile(null);
		});

		// Drag & drop events
		$zone.off('dragover dragenter').on('dragover dragenter', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.addClass('is-dragover is-dragging');
		});

		$zone.off('dragleave dragend').on('dragleave dragend', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.removeClass('is-dragover is-dragging');
		});

		$zone.off('drop').on('drop', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$zone.removeClass('is-dragover is-dragging');

			const dt = e.originalEvent && e.originalEvent.dataTransfer;
			if (dt && dt.files && dt.files.length > 0) {
				const droppedFile = dt.files[0];
				if (setStagedFile(droppedFile)) {
					const dataTransfer = new DataTransfer();
					dataTransfer.items.add(droppedFile);
					$input[0].files = dataTransfer.files;
				}
			}
		});

		// Hapus berkas lama
		$('#btn-remove-existing-file').off('click').on('click', function (e) {
			e.preventDefault();
			$('#remove_filehj').val('1');
			$('#existing-file-container').addClass('d-none');
			Swal.fire({
				toast: true,
				position: 'top-end',
				icon: 'info',
				title: 'Berkas lama akan dihapus saat disimpan',
				showConfirmButton: false,
				timer: 2500
			});
		});
	}

	function syncHargaNet() {
		const isSubsidi = $('#is_subsidi').val() === '1';
		const hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0;
		if (isSubsidi) {
			$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
			$('#hint-hargajual_net').html('<i class="fas fa-info-circle text-primary mr-25"></i> Tipe Subsidi: Harga Jual Net otomatis sama dengan Harga Jual.');
		} else {
			$('#hint-hargajual_net').html('<i class="fas fa-edit text-muted mr-25"></i> Tipe Non-Subsidi: Isi net secara spesifik atau klik tombol Samakan.');
		}
	}

	function load_total() {
		let tot = 0,
			bphtb = parseFloat(removeComma($('#add-form #bphtb').val())) || 0,
			um = parseFloat(removeComma($('#add-form #uang_muka').val())) || 0,
			ba = parseFloat(removeComma($('#add-form #biaya_adm').val())) || 0,
			bp = parseFloat(removeComma($('#add-form #biaya_proses').val())) || 0;

		tot = um + bphtb + ba + bp;

		$('#add-form #total').val(num_format(tot)).trigger('keyup');
		$('#display-total-tagihan').text('Rp ' + num_format(tot));
	}

	function initFormValidation() {
		$.validator.setDefaults({
			highlight: function (element) {
				$(element).addClass('is-invalid').removeClass('is-valid');
			},
			unhighlight: function (element) {
				$(element).removeClass('is-invalid').addClass('is-valid');
			},
			errorElement: 'div',
			errorClass: 'invalid-feedback',
			errorPlacement: function (error, element) {
				if (element.parent('.input-group').length) {
					error.insertAfter(element.parent());
				} else if ($(element).is('.select')) {
					element.next().after(error);
				} else if (element.hasClass('select2')) {
					error.insertAfter(element.next());
				} else if (element.hasClass('selectpicker')) {
					error.insertAfter(element.next());
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler: function () {
				// Pastikan keterangan tersinkron dari quill
				if (typeof quill !== 'undefined' && quill) {
					$('#keterangan').val(quill.root.innerHTML);
				}

				if (getUtf8ByteLength($('#keterangan').val()) > HARD_KETERANGAN_BYTES) {
					Swal.fire({
						icon: 'warning',
						title: 'Keterangan Terlalu Besar',
						text: 'Total ukuran teks dan gambar pada Keterangan melebihi batas penyimpanan (64 KB). Kurangi ukuran atau jumlah gambar sebelum menyimpan.',
						confirmButtonColor: '#2057a3'
					});
					return false;
				}

				var formEl = $('#add-form')[0];
				var fd = new FormData(formEl);
				fd.append(getCsrfName(), getCsrfHash());

				$('.text-danger.validation-error').remove();

				$.ajax({
					url: url,
					type: 'post',
					contentType: false,
					processData: false,
					data: fd,
					dataType: 'json',
					beforeSend: function () {
						$('#add-form-btn').prop('disabled', true);
						$('#add-form-btn').html('<i class="fa fa-spinner fa-spin mr-25"></i> Menyimpan...');
					},
					success: function (response) {
						setCsrfHash(response.token);

						if (response.success === true) {
							Swal.fire({
								icon: 'success',
								title: response.messages,
								showConfirmButton: false,
								timer: 1500
							}).then(function () {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
								// Tandai tutup form terkonfirmasi agar tidak memicu initModalListener
								$('#add-modal').data('isConfirmedClose', true).modal('hide');
							});
						} else {
							if (response.messages instanceof Object) {
								$.each(response.messages, function (index, value) {
									var id = $('#' + index);
									id.closest('.form-control')
										.removeClass('is-invalid')
										.removeClass('is-valid')
										.addClass(value.length > 0 ? 'is-invalid' : 'is-valid');
									id.after('<div class="invalid-feedback validation-error">' + value + '</div>');
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Gagal Menyimpan',
									text: response.messages,
									confirmButtonColor: '#2057a3'
								});
							}
						}
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fas fa-save mr-25"></i> Simpan Data');
					},
					error: function () {
						Swal.fire({
							icon: 'error',
							title: 'Kesalahan Sistem',
							text: 'Terjadi kesalahan saat memproses data ke server.',
							confirmButtonColor: '#2057a3'
						});
						$('#add-form-btn').prop('disabled', false);
						$('#add-form-btn').html('<i class="fas fa-save mr-25"></i> Simpan Data');
					}
				});

				return false;
			}
		});

		$('#add-form').validate({
			ignore: ':hidden, #keterangan-editor [contenteditable], .ql-editor, .ql-clipboard'
		});
	}

	function add() {
		url = baseUrl + '/' + controller + '/add';
		$('#add-form-btn').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Data');

		// Reset form
		$('#add-form')[0].reset();
		$('#id').val('');
		$('#remove_filehj').val('0');

		// Reset dropzone
		$('#id_filehj').val('');
		$('#dropzone-preview').addClass('d-none');
		$('#dropzone-prompt').removeClass('d-none');
		$('#existing-file-container').addClass('d-none');

		if (typeof quill !== 'undefined' && quill) {
			quill.root.innerHTML = '';
			$('#keterangan').val('');
		}

		$('#modal-title-text').text('Tambah Harga Jual / Pricelist');
		$('.form-control').removeClass('is-invalid').removeClass('is-valid');

		// Inisialisasi modal listener untuk konfirmasi batal
		if (typeof initModalListener === 'function') {
			initModalListener('#add-modal');
		}

		$('#add-modal').modal('show');

		const activeProyek = resolveActiveProyek();
		if (activeProyek) {
			$('#add-form #idProyek').val(activeProyek).trigger('change');
		} else {
			$('#idProyek').val($('#idProyek option:first').val()).trigger('change');
		}

		$('#is_subsidi').val('1').trigger('change');
		load_total();
	}

	function edit(id_cluster) {
		url = baseUrl + '/' + controller + '/edit';
		$('#add-form-btn').prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Data');

		$('#add-form')[0].reset();
		$('#remove_filehj').val('0');
		$('#id_filehj').val('');
		$('#dropzone-preview').addClass('d-none');
		$('#dropzone-prompt').removeClass('d-none');
		$('#existing-file-container').addClass('d-none');

		const reqData = {
			id_cluster: id_cluster
		};
		reqData[getCsrfName()] = getCsrfHash();

		$.ajax({
			url: baseUrl + '/' + controller + '/getOne',
			type: 'post',
			data: reqData,
			dataType: 'json',
			success: function (r) {
				setCsrfHash(r.token);

				$('#modal-title-text').text('Edit Harga Jual / Pricelist');
				$('.form-control').removeClass('is-invalid').removeClass('is-valid');

				// Inisialisasi modal listener untuk konfirmasi batal
				if (typeof initModalListener === 'function') {
					initModalListener('#add-modal');
				}

				$('#add-modal').modal('show');

				$('#id').val(r.id);
				$('#row').val(r.row);
				$('#lb').val(r.lb);
				$('#lt').val(r.lt);
				$('#id_tipe').val(r.id_tipe);

				$('#hargajual').val(num_format(r.hargajual)).trigger('keyup');
				$('#hargajual_net').val(num_format(r.hargajual_net)).trigger('keyup');
				$('#kpr').val(num_format(r.kpr)).trigger('keyup');
				$('#ppn').val(num_format(r.ppn)).trigger('keyup');
				$('#uang_muka').val(num_format(r.uang_muka)).trigger('keyup');
				$('#bphtb').val(num_format(r.bphtb)).trigger('keyup');
				$('#biaya_adm').val(num_format(r.biaya_adm)).trigger('keyup');
				$('#biaya_proses').val(num_format(r.biaya_proses)).trigger('keyup');

				$('#keterangan').val(r.keterangan || '');
				if (typeof quill !== 'undefined' && quill) {
					quill.root.innerHTML = r.keterangan || '';
				}

				$('#is_subsidi').val(r.is_subsidi).trigger('change');

				// Integrasi berkas lampiran dengan FileAccessService gateway
				if (r.access_url) {
					$('#existing-file-container').removeClass('d-none');
					$('#existing-file-name').text(r.default_filename || r.file_name || 'Berkas Pricelist Tersimpan');
					$('#existing-file-btn').attr('href', r.access_url);
				} else {
					$('#existing-file-container').addClass('d-none');
				}

				load_total();

				// Gunakan pemilihan nilai langsung pada select2 yang sudah ada (mencegah opsi duplikat)
				$('#idProyek').val(r.id_proyek).trigger('change');

				if (r.tgl_harga && r.tgl_harga !== '0000-00-00') {
					const datePicker = document.querySelector('#tgl_harga');
					if (datePicker && datePicker._flatpickr) {
						datePicker._flatpickr.setDate(r.tgl_harga);
					}
				}
			},
			error: function () {
				Swal.fire({
					icon: 'error',
					title: 'Gagal Memuat Data',
					text: 'Tidak dapat mengambil rincian pricelist dari server.',
					confirmButtonColor: '#2057a3'
				});
			}
		});
	}

	function detail(id_cluster) {
		return edit(id_cluster);
	}

	function save() {
		$('#add-form').trigger('submit');
	}

	function remove(id_cluster, st) {
		Swal.fire({
			title: 'Ubah status pricelist?',
			text: 'Pricelist tidak akan terhapus, hanya jadi berubah status',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#2057a3',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Confirm',
			cancelButtonText: 'Cancel'
		}).then((result) => {
			if (result.value) {
				const reqData = {
					id: id_cluster,
					is_active: st
				};
				reqData[getCsrfName()] = getCsrfHash();

				$.ajax({
					url: baseUrl + '/' + controller + '/remove',
					type: 'post',
					data: reqData,
					dataType: 'json',
					success: function (response) {
						setCsrfHash(response.token);

						if (response.success === true) {
							Swal.fire({
								icon: 'success',
								title: response.messages,
								showConfirmButton: false
							}).then(function () {
								$('#data_table').DataTable().ajax.reload(null, false).draw(false);
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: response.messages,
								showConfirmButton: false
							});
						}
					},
					error: function () {
						Swal.fire({
							icon: 'error',
							title: 'Oops!! Terjadi kesalahan.',
							showConfirmButton: false
						});
					}
				});
			}
		});
	}

	$(function () {
		var table = $('#data_table').DataTable({
			scrollY: getTableScrollY(),
			scrollX: true,
			scrollCollapse: true,
			fixedColumns: {
				leftColumns: 5
			},
			columnDefs: [
				{ targets: '_all', className: 'text-left' }
			],
			paging: true,
			lengthChange: true,
			searching: true,
			ordering: false,
			info: true,
			autoWidth: true,
			responsive: false,
			processing: true,
			serverSide: true,
			drawCallback: function () {
				var api = this.api();
				$('#data_table tbody img').each(function () {
					if (!this.complete) {
						$(this).one('load error', function () {
							api.columns.adjust();
						});
					}
				});
			},
			ajax: {
				url: baseUrl + '/' + controller + '/get/all',
				type: 'POST',
				dataType: 'json',
				data: function (data) {
					data[getCsrfName()] = getCsrfHash();
					data.id_proyek = resolveActiveProyek();
					data.is_active = $('#filter-is_active').val();
				},
				dataSrc: function (r) {
					setCsrfHash(r.token);
					return r.data;
				},
				async: 'true'
			}
		});

		flatpickr('.flatpickr-human-friendly', {
			altInput: true,
			altFormat: 'F j, Y',
			dateFormat: 'Y-m-d'
		});

		$('.dataTables_filter input')
			.off()
			.on('change', function () {
				table.search(this.value).draw();
			});

		$('#idProyek').select2({
			placeholder: 'Pilih Proyek',
			allowClear: true,
			dropdownParent: $('#add-modal')
		});

		if (resolveActiveProyek()) {
			table.draw();
		}

		$('#btn_draw').on('click', function () {
			table.draw();
		});

		// Dynamic table height on resize
		$(window).on('resize', function () {
			var newH = getTableScrollY();
			$('.dataTables_scrollBody').css('max-height', newH);
			table.columns.adjust();
		});

		// Remove bug arrow select2
		$('.select2-selection__arrow').css('pointer-events', 'none');

		// Initialize Quill Editor
		quill = new Quill('#keterangan-editor', {
			theme: 'snow',
			modules: {
				toolbar: [
					['bold', 'italic', 'underline', 'strike'],
					[{ list: 'ordered' }, { list: 'bullet' }],
					['clean']
				]
			}
		});
		setupQuillImagePaste(quill);

		// Update hidden input before submit
		quill.on('text-change', function () {
			$('#keterangan').val(quill.root.innerHTML);
		});

		$('#is_subsidi').on('change', function () {
			syncHargaNet();
		});

		$('#add-form #hargajual, #add-form #kpr').on('input change keyup', function () {
			let hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0,
				kpr = parseFloat(removeComma($('#add-form #kpr').val())) || 0;

			$('#add-form #uang_muka').val(num_format(hj - kpr)).trigger('keyup');

			const currentNet = parseFloat(removeComma($('#add-form #hargajual_net').val())) || 0;
			if ($('#is_subsidi').val() === '1' || currentNet === 0) {
				$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
			}

			load_total();
		});

		$('#btn-sync-net').on('click', function (e) {
			e.preventDefault();
			let hj = parseFloat(removeComma($('#add-form #hargajual').val())) || 0;
			$('#add-form #hargajual_net').val(num_format(hj)).trigger('keyup');
		});

		$('#add-form .num').on('input change keyup', function () {
			load_total();
		});

		setupDropzone();
		initFormValidation();

		$('.hargajual-page #data_table thead > tr > th:nth-child(1)').css({ 'min-width': '30px', 'max-width': '30px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(2)').css({ 'min-width': '100px', 'max-width': '100px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(3)').css({ 'min-width': '130px', 'max-width': '130px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(4)').css({ 'min-width': '50px', 'max-width': '50px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(5)').css({ 'min-width': '120px', 'max-width': '120px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(6)').css({ 'min-width': '180px', 'max-width': '180px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(7)').css({ 'min-width': '180px', 'max-width': '180px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(8)').css({ 'min-width': '250px', 'max-width': '250px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(9)').css({ 'min-width': '100px', 'max-width': '100px' });
		$('.hargajual-page #data_table thead > tr > th:nth-child(10), .hargajual-page #data_table thead > tr > th:nth-child(11)').css({ 'min-width': '150px', 'max-width': '150px' });
	});

	// Expose global handlers for DataTables onclick attributes
	window.add = add;
	window.edit = edit;
	window.detail = detail;
	window.remove = remove;
	window.save = save;
	window.load_total = load_total;
	window.syncHargaNet = syncHargaNet;
})(jQuery);
