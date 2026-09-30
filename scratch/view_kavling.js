	function view(id_tipe) {
		edit(id_tipe, true);
	}

	function listKavling(id_tipe) {
		$.ajax({
			url: '<?php echo base_url($controller . '/getListKavling') ?>',
			type: 'post',
			data: {
				[$csrfName]: csrfHash,
				id_tipe: id_tipe
			},
			dataType: 'json',
			success: function(response) {
				csrfHash = response.token;
				if (response.success) {
					if (response.data.length === 0) {
						Swal.fire({
							icon: 'info',
							title: 'Informasi',
							text: 'Tidak ada kavling yang menggunakan tipe ini.',
						});
					} else {
						let html = '<ul class="list-group list-group-flush text-left">';
						response.data.forEach(function(item) {
							html += '<li class="list-group-item d-flex justify-content-between align-items-center">' + item.no_kavling;
							if (item.status_kavling) {
								html += ' <span class="badge badge-light-primary">' + item.status_kavling + '</span>';
							}
							html += '</li>';
						});
						html += '</ul>';
						
						Swal.fire({
							title: 'Daftar Kavling',
							html: '<div style="max-height: 300px; overflow-y: auto;">' + html + '</div>',
							confirmButtonText: 'Tutup',
							width: '400px'
						});
					}
				}
			}
		});
	}
