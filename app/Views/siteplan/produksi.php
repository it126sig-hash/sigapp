<style>
	.select2-selection__choice {
		display: block;
		margin: 2px 0;
	}

	.select2-container--default .select2-selection--multiple {
		height: auto;
	}

	#modal_divisi7 .modal-dialog {
		max-width: min(1320px, calc(100vw - 32px));
		margin: 1rem auto;
	}

	#modal_divisi7 .modal-content {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
		overflow: hidden;
	}

	#modal_divisi7 .modal-header,
	#modal_divisi7 .modal-footer {
		background: #fff;
		border-color: #e5e7eb;
		padding: 1rem 1.25rem;
	}

	#modal_divisi7 .modal-title {
		color: #111827;
		font-size: 1.05rem;
		font-weight: 700;
	}

	#modal_divisi7 .modal-body {
		background: #f3f5f7 !important;
		max-height: calc(100vh - 8rem);
		overflow-y: auto;
		padding: 1rem;
	}

	#modal_divisi7 .produksi-modal-summary {
		align-items: flex-start;
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		display: flex;
		gap: 1rem;
		justify-content: space-between;
		margin-bottom: .85rem;
		padding: .85rem;
	}

	#modal_divisi7 .produksi-modal-title {
		color: #111827;
		font-size: .92rem;
		font-weight: 800;
		line-height: 1.4;
		margin: 0;
	}

	#modal_divisi7 .produksi-modal-actions {
		display: grid;
		flex: 0 0 210px;
		gap: .45rem;
	}

	#modal_divisi7 .produksi-modal-actions .btn {
		font-size: .78rem;
		font-weight: 700;
		padding: .52rem .75rem;
	}

	#modal_divisi7 .produksi-modal-actions .btn-block {
		margin-top: 0 !important;
	}

	#modal_divisi7 .nav-tabs {
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		gap: .35rem;
		margin-bottom: .85rem;
		padding: .4rem;
	}

	#modal_divisi7 .nav-tabs .nav-link {
		border: 0;
		border-radius: 6px;
		color: #4b5563;
		font-size: .82rem;
		font-weight: 700;
		padding: .55rem .8rem;
		white-space: nowrap;
	}

	#modal_divisi7 .nav-tabs .nav-link.active {
		background: #2057a3;
		box-shadow: 0 6px 14px rgba(32, 87, 163, .2);
		color: #fff;
	}

	#modal_divisi7 .tab-pane {
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		padding: .85rem;
	}

	#modal_divisi7 label,
	#modal_divisi7 .info-label {
		color: #6b7280;
		font-size: .78rem;
		font-weight: 700;
		letter-spacing: 0;
	}

	#modal_divisi7 .form-control {
		background-color: #fff;
		border-color: #d8dde3;
		border-radius: 6px;
		min-height: 36px;
	}

	#modal_divisi7 textarea.form-control {
		min-height: 82px;
	}

	#modal_divisi7 .btn {
		border-radius: 6px;
	}

	#modal_divisi7 .btn-primary {
		background-color: #2057a3 !important;
		border-color: #2057a3 !important;
	}

	#modal_divisi7 .divider {
		margin: .65rem 0 .85rem;
	}

	#modal_divisi7 .divider-left {
		border-left-color: #2057a3;
		margin-bottom: .85rem;
		padding-left: .75rem;
	}

	#modal_divisi7 .divider .divider-text {
		color: #111827;
		font-size: .86rem;
		font-weight: 700;
	}

	#modal_divisi7 .produksi-progress-grid {
		display: grid;
		gap: .75rem;
		grid-template-columns: minmax(260px, 1.1fr) minmax(240px, .9fr) minmax(240px, .9fr) minmax(280px, 1fr);
	}

	#modal_divisi7 .produksi-compact-card {
		background: #fff;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		min-width: 0;
		padding: .8rem;
	}

	#modal_divisi7 .produksi-compact-card .form-group {
		margin-bottom: .65rem;
	}

	#modal_divisi7 .produksi-compact-card .form-group:last-child,
	#modal_divisi7 .produksi-compact-card .divider:last-child {
		margin-bottom: 0;
	}

	#modal_divisi7 .produksi-checklist-grid {
		display: grid;
		gap: .45rem;
	}

	#modal_divisi7 .produksi-checklist-grid .form-group {
		background: #f8fafc;
		border: 1px solid #edf0f2;
		border-radius: 6px;
		margin: 0;
		padding: .48rem .55rem;
	}

	#modal_divisi7 .custom-control-label {
		color: #374151;
		font-size: .78rem;
		font-weight: 700;
		line-height: 1.3;
	}

	#modal_divisi7 .produksi-range-box {
		align-items: center;
		background: #f8fafc;
		border: 1px solid #edf0f2;
		border-radius: 8px;
		display: flex;
		gap: .75rem;
		padding: .7rem;
	}

	#modal_divisi7 .produksi-range-box .form-control-range {
		flex: 1 1 auto;
	}

	#modal_divisi7 .produksi-range-value {
		color: #2057a3;
		font-size: 1rem;
		font-weight: 800;
		min-width: 48px;
		text-align: right;
	}

	#modal_divisi7 .produksi-date-meta {
		display: block;
		font-size: .72rem;
		line-height: 1.35;
		margin: -.25rem 0 .55rem;
	}

	#modal_divisi7 .produksi-edit-highlight {
		animation: produksiEditPulse 1.4s ease-in-out 1;
		box-shadow: 0 0 0 3px rgba(32, 87, 163, .14);
	}

	@keyframes produksiEditPulse {
		0% {
			box-shadow: 0 0 0 0 rgba(32, 87, 163, .28);
		}
		100% {
			box-shadow: 0 0 0 3px rgba(32, 87, 163, .14);
		}
	}

	#modal_divisi7 [id^="list_"],
	#modal_divisi7 #produksi-history-timeline {
		display: grid !important;
		gap: .85rem;
		grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
	}

	#modal_divisi7 .input-foto-container,
	#modal_divisi7 .detail-file-tile {
		background: #fff;
		border: 1px solid #d7deea;
		border-radius: 8px;
		box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
		display: flex;
		flex-direction: column;
		height: auto;
		overflow: hidden;
		position: relative;
		width: 100%;
	}

	#modal_divisi7 .input-foto,
	#modal_divisi7 .detail-file-preview {
		background: #f3f6fb;
		border-bottom: 1px solid #e5eaf2;
		display: block;
		min-height: 128px;
		overflow: hidden;
		position: relative;
		width: 100%;
	}

	#modal_divisi7 .input-foto img,
	#modal_divisi7 .detail-file-preview img {
		display: block;
		height: 128px;
		object-fit: cover;
		width: 100%;
	}

	#modal_divisi7 .input-foto > .btn {
		align-items: center;
		border: 0;
		border-radius: 0;
		display: flex;
		height: 128px;
		justify-content: center;
		min-height: 128px;
		width: 100%;
	}

	#modal_divisi7 .detail-file-body,
	#modal_divisi7 .input-foto-meta {
		padding: .75rem;
	}

	#modal_divisi7 .input-foto-meta strong {
		color: #6b7280;
		display: block;
		font-size: .76rem;
		margin-bottom: .25rem;
	}

	#modal_divisi7 .detail-file-meta,
	#modal_divisi7 .foto-coordinate-status {
		color: #667085;
		font-size: .78rem;
		line-height: 1.35;
	}

	#modal_divisi7 .input-foto > div {
		bottom: .5rem !important;
		left: .5rem !important;
		max-width: calc(100% - 1rem);
		z-index: 2;
	}

	#modal_divisi7 .input-foto > button {
		border-radius: 6px;
		font-size: .72rem;
		line-height: 1;
		padding: .35rem .45rem;
		right: .5rem;
		top: .5rem !important;
		z-index: 3;
	}

	#modal_divisi7 .foto-container .custom-file {
		background: #f8fafc;
		border: 1px dashed #9db5d8;
		border-radius: 8px;
		height: auto;
		margin-bottom: .85rem;
		min-height: 44px;
		padding: .35rem;
	}

	#modal_divisi7 .foto-container .custom-file-input {
		cursor: pointer;
		height: 44px;
	}

	#modal_divisi7 .foto-container .custom-file-label {
		align-items: center;
		background: transparent;
		border: 0;
		color: #2057a3;
		display: flex;
		font-size: .82rem;
		font-weight: 700;
		height: 100%;
		margin: 0;
		padding: .55rem .75rem;
	}

	#modal_divisi7 .foto-container .custom-file-label::after {
		background: #2057a3;
		border: 0;
		border-radius: 6px;
		color: #fff;
		content: "Pilih";
		height: auto;
		margin: .25rem;
		padding: .35rem .75rem;
	}

	#modal_divisi7 .produksi-upload-action {
		align-items: center;
		background: #f8fafc;
		border: 1px dashed #9db5d8;
		border-radius: 8px;
		display: flex;
		gap: .75rem;
		justify-content: space-between;
		margin-bottom: .85rem;
		padding: .75rem;
	}

	#modal_divisi7 .produksi-upload-buttons {
		display: flex;
		flex: 0 0 auto;
		flex-wrap: wrap;
		gap: .5rem;
		justify-content: flex-end;
	}

	#modal_divisi7 .produksi-camera-action {
		display: flex;
		justify-content: flex-end;
		margin: -.35rem 0 .85rem;
	}

	#modal_divisi7 .produksi-upload-hidden-file {
		border: 0;
		height: 1px;
		margin: 0;
		min-height: 0;
		opacity: 0;
		overflow: hidden;
		padding: 0;
		pointer-events: none;
		position: absolute;
		width: 1px;
	}

	#modal_divisi7 .produksi-upload-hidden-file .custom-file-label,
	#modal_divisi7 .produksi-upload-hidden-file .custom-file-label::after {
		content: none !important;
		display: none !important;
	}

	#modal_divisi7 .produksi-history-list {
		display: flex !important;
		flex-direction: column;
		min-width: 1080px;
	}

	#modal_divisi7 #fm-prod-history {
		overflow-x: auto;
	}

	#modal_divisi7 #fm-prod-history .produksi-jalan-timeline-item {
		min-width: 1040px;
	}

	#modal_divisi7 #fm-prod-history .produksi-jalan-timeline-title,
	#modal_divisi7 #fm-prod-history .produksi-jalan-timeline-meta {
		white-space: nowrap;
	}

	#modal_divisi7 .produksi-history-change-list {
		background: #f8fafc;
		border: 1px solid #e5eaf2;
		border-radius: 8px;
		display: flex;
		flex-direction: column;
		gap: .35rem;
		margin-top: .75rem;
		padding: .65rem;
	}

	#modal_divisi7 .produksi-history-change-row {
		align-items: flex-start;
		display: grid;
		gap: .75rem;
		grid-template-columns: minmax(260px, .35fr) minmax(720px, 1fr);
	}

	#modal_divisi7 .produksi-history-change-label {
		color: #4b5563;
		font-size: .78rem;
		font-weight: 800;
		white-space: nowrap;
	}

	#modal_divisi7 .produksi-history-change-value {
		color: #111827;
		font-size: .8rem;
		overflow-x: auto;
		white-space: nowrap;
	}

	#modal_divisi7 .produksi-history-file-list {
		margin: .45rem 0 0;
		padding-left: 1rem;
	}

	#modal_divisi7 .modal-footer {
		position: sticky;
		bottom: 0;
		z-index: 3;
	}

	@media (max-width: 1199.98px) {
		#modal_divisi7 .produksi-progress-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767.98px) {
		#modal_divisi7 .modal-dialog {
			max-width: calc(100vw - 12px);
			margin: .5rem auto;
		}

		#modal_divisi7 .modal-body {
			max-height: calc(100vh - 6rem);
			padding: .75rem;
		}

		#modal_divisi7 .nav-tabs {
			flex-direction: row !important;
			flex-wrap: nowrap;
			overflow-x: auto;
			padding-bottom: .5rem;
		}

		#modal_divisi7 .produksi-modal-summary {
			flex-direction: column;
			gap: .75rem;
		}

		#modal_divisi7 .produksi-modal-actions {
			flex: 0 0 auto;
			width: 100%;
		}

		#modal_divisi7 .produksi-progress-grid {
			grid-template-columns: 1fr;
		}

		#modal_divisi7 .produksi-upload-action {
			align-items: stretch;
			flex-direction: column;
		}

		#modal_divisi7 .produksi-upload-buttons {
			justify-content: stretch;
		}

		#modal_divisi7 .produksi-upload-buttons .btn {
			flex: 1 1 150px;
		}

		#modal_divisi7 .produksi-camera-action {
			justify-content: stretch;
		}

		#modal_divisi7 .produksi-camera-action .btn {
			width: 100%;
		}

		#modal_divisi7 .tab-pane {
			padding: .85rem;
		}

		#modal_divisi7 .modal-footer {
			align-items: stretch;
			flex-direction: column;
		}

		#modal_divisi7 .modal-footer .btn {
			margin: 0 0 .5rem 0 !important;
			width: 100%;
		}
	}

	/* SIGAPP UI Acuan - Modal Pembayaran Produksi (mengikuti #modal-cashout-keu) */
	#modal-bayar_produksi-prod .modal-dialog {
		height: 100vh;
		margin: 0;
		max-width: 100vw;
		width: 100vw;
	}

	#modal-bayar_produksi-prod .modal-content {
		border: 0;
		border-radius: 0;
		height: 100vh;
		max-height: 100vh;
		box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
		overflow: hidden;
	}

	#modal-bayar_produksi-prod .modal-header {
		align-items: center;
		background: #fff;
		border-bottom: 1px solid #e5e7eb;
		margin-bottom: 0 !important;
		padding: 1rem 1.25rem;
	}

	#modal-bayar_produksi-prod .modal-title {
		color: #111827;
		font-size: 1.05rem;
		font-weight: 700;
	}

	#modal-bayar_produksi-prod .prod-bp-body {
		background: #f3f5f7 !important;
		flex: 1 1 auto;
		max-height: none;
		min-height: 0;
		overflow-y: auto;
		padding: 1rem;
	}

	#modal-bayar_produksi-prod .prod-bp-layout {
		display: flex;
		flex-wrap: nowrap;
		gap: 1rem;
		min-width: 0;
	}

	#modal-bayar_produksi-prod .prod-bp-sidebar {
		align-self: flex-start;
		flex: 0 0 320px;
		max-height: calc(100vh - 8rem);
		max-width: 320px;
		overflow-y: auto;
		position: sticky;
		top: 0;
		z-index: 2;
	}

	#modal-bayar_produksi-prod .prod-bp-content {
		flex: 1 1 auto;
		max-width: calc(100% - 336px);
		min-width: 0;
	}

	#modal-bayar_produksi-prod .card {
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		box-shadow: none;
		margin-bottom: 1rem;
		overflow: hidden;
	}

	#modal-bayar_produksi-prod .card-body {
		padding: 1rem;
	}

	#modal-bayar_produksi-prod .prod-bp-hero {
		border: 0;
	}

	#modal-bayar_produksi-prod .bg-primary {
		background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
	}

	#modal-bayar_produksi-prod .label_alamat {
		font-size: 1rem;
		font-weight: 700;
		line-height: 1.35;
		margin-bottom: 0;
		overflow-wrap: anywhere;
	}

	#modal-bayar_produksi-prod .prod-bp-meta-card {
		background: #fff;
		border: 1px solid #cfd6e3;
		border-radius: 8px;
		box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
		margin-bottom: 0;
	}

	#modal-bayar_produksi-prod .prod-bp-meta-card h6,
	#modal-bayar_produksi-prod .prod-bp-meta-card h5 {
		color: #374151;
		line-height: 1.35;
		margin-bottom: .45rem;
	}

	#modal-bayar_produksi-prod .prod-bp-meta-card h5:last-child,
	#modal-bayar_produksi-prod .prod-bp-meta-card h6:last-of-type {
		margin-bottom: 0;
	}

	#modal-bayar_produksi-prod .divider {
		margin: .65rem 0 .85rem;
	}

	#modal-bayar_produksi-prod .divider-left {
		border-left-color: #2057a3;
		margin-bottom: .85rem;
		padding-left: .75rem;
	}

	#modal-bayar_produksi-prod .divider .divider-text {
		color: #111827;
		font-size: .86rem;
		font-weight: 700;
	}

	#modal-bayar_produksi-prod label,
	#modal-bayar_produksi-prod .form-label {
		color: #6b7280;
		font-size: .78rem;
		font-weight: 700;
		letter-spacing: 0;
	}

	#modal-bayar_produksi-prod .form-group {
		margin-bottom: .8rem;
	}

	#modal-bayar_produksi-prod .form-control {
		background-color: #fff;
		border-color: #d8dde3;
		border-radius: 6px;
		min-height: 36px;
	}

	#modal-bayar_produksi-prod .btn {
		border-radius: 6px;
		font-weight: 700;
		white-space: normal;
	}

	#modal-bayar_produksi-prod .btn-primary {
		background-color: #2057a3 !important;
		border-color: #2057a3 !important;
	}

	#modal-bayar_produksi-prod .btn-primary:hover,
	#modal-bayar_produksi-prod .btn-primary:focus {
		background-color: #174b8f !important;
		border-color: #174b8f !important;
	}

	#modal-bayar_produksi-prod #bayar-produksi-table {
		margin-bottom: 0;
	}

	#modal-bayar_produksi-prod #bayar-produksi-table thead th {
		background: #f8fafc;
		border-bottom: 1px solid #e5e7eb;
		color: #374151;
		font-size: .78rem;
		font-weight: 700;
		white-space: nowrap;
	}

	#modal-bayar_produksi-prod #bayar-produksi-table tbody td {
		font-size: .84rem;
		vertical-align: middle;
	}

	#modal-bayar_produksi-prod .modal-footer {
		background: #fff;
		border-top: 1px solid #e5e7eb;
		padding: .85rem 1.25rem;
	}

	.dark-layout #modal-bayar_produksi-prod .modal-header,
	.dark-layout #modal-bayar_produksi-prod .card,
	.dark-layout #modal-bayar_produksi-prod .prod-bp-meta-card,
	.dark-layout #modal-bayar_produksi-prod .modal-footer {
		background: #283046 !important;
		border-color: rgba(255, 255, 255, .08) !important;
	}

	.dark-layout #modal-bayar_produksi-prod .modal-title,
	.dark-layout #modal-bayar_produksi-prod .divider .divider-text {
		color: #f8fafc;
	}

	.dark-layout #modal-bayar_produksi-prod .prod-bp-body {
		background: #1f2937 !important;
	}

	@media (max-width: 1199.98px) {
		#modal-bayar_produksi-prod .prod-bp-layout {
			flex-wrap: wrap;
		}

		#modal-bayar_produksi-prod .prod-bp-sidebar,
		#modal-bayar_produksi-prod .prod-bp-content {
			flex: 0 0 100%;
			max-width: 100%;
		}

		#modal-bayar_produksi-prod .prod-bp-sidebar {
			max-height: none;
			overflow-y: visible;
			position: static;
		}
	}

	@media (max-width: 767.98px) {
		#modal-bayar_produksi-prod .modal-dialog {
			height: 100vh;
			margin: 0;
			max-width: 100vw;
			width: 100vw;
		}

		#modal-bayar_produksi-prod .prod-bp-body {
			padding: .75rem;
		}

		#modal-bayar_produksi-prod .card-body {
			padding: .85rem;
		}
	}
</style>
<div class="modal fade text-left" id="modal_produksi_add_jalan" tabindex="-1" role="dialog"
	aria-labelledby="modal_produksi_add_jalan" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Tambah Jalan Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="fm-produksi-add-jalan" class="add-new-record modal-content pt-0">
				<div class="modal-body">
					<input type="hidden" class="form-control" id="prod_jalan_points" name="points" value="" />

					<div class="form-group">
						<label for="prod_jalan_id_cluster">Cluster</label>
						<select id="prod_jalan_id_cluster" name="id_cluster" class="select2 form-control"></select>
					</div>

					<div class="form-group">
						<label for="prod_jalan_id_jalan">Blok/Jalan</label>
						<select disabled id="prod_jalan_id_jalan" name="id_jalan" class="select2 form-control"></select>
					</div>

					<div class="form-group">
						<label for="prod_jalan_progres">Progres</label>
						<input type="range" onInput="$('.prod_jalan_r_progres').html($(this).val())" class="form-control-range"
							min="0" max="100" step="1" id="prod_jalan_progres" name="f_progres_jalan" value="0">
						<span class="prod_jalan_r_progres">0</span><span>%</span>
					</div>

					<div class="form-group">
						<label for="prod_jalan_luas">Luas Dilapangan</label>
						<input type="text" class="form-control" id="prod_jalan_luas" name="f_produksi_luas"
							placeholder="Luas jalan dilapangan" />
					</div>

					<div class="form-group">
						<label for="prod_jalan_keterangan">Keterangan</label>
						<textarea class="form-control" id="prod_jalan_keterangan" name="f_produksi_keterangan" rows="3"
							placeholder="Keterangan"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="save_produksi_add_jalan-btn" class="btn btn-primary data-submit mr-1"
						onclick="save_jalan_produksi()" href="javascript:void(0)">Simpan</button>
					<button type="button" class="btn btn-outline-secondary" onclick="cancel_tambah_jalan_produksi()">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="modal_fothersproduksi" tabindex="-1" role="dialog"
	aria-labelledby="modal_fothersproduksi" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Progres Jalan Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="fm-fotherproduksi" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
				<div class="modal-body">
					<p class="modal-title label_alamat" id="label_fothersproduksi"></p>
					<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
					<input type="hidden" class="form-control" id="id_proyek" name="id_produksi" value="" />

					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="prod-jalan-progress-tab" data-toggle="tab"
								href="#prod-jalan-progress" role="tab" aria-selected="true">Progres</a>
						</li>
						<li class="nav-item produksi-jalan-only">
							<a class="nav-link" id="prod-jalan-history-tab" data-toggle="tab" href="#prod-jalan-history"
								role="tab" aria-selected="false">History</a>
						</li>
					</ul>

					<div class="tab-content pt-1">
						<div class="tab-pane active" id="prod-jalan-progress" aria-labelledby="prod-jalan-progress-tab"
							role="tabpanel">
							<div class="row">
								<div class="col-md-6">
									<span>Luas di Siteplan : <br>
										<span class='t_luas_planning'></span>
									</span>
								</div>
								<div class="col-md-6">
									<span>Luas di Sertifikat : <br>
										<span class='t_luas_legal'></span>
									</span>
								</div>
							</div>
							<hr>

							<div class="form-group">
								<label for="f_progres_jalan">Progres</label>
								<input type="range" onInput="$('.r_progres').html($(this).val())" class="form-control-range"
									min="0" max="100" step="1" id="f_progres_jalan" name="f_progres_jalan">
								<span class="r_progres"></span><span>%</span>
							</div>
							<div class="form-group">
								<label for="f_progres_jalan">Status</label>
								<select id="slf_jenis" name="slf_jenis" class="form-control">
									<option value="">Basecourse</option>
									<option value="Basecourse">Basecourse</option>
									<option value="Paving">Paving</option>
								</select>
							</div>

							<div class="form-group">
								<label for="f_produksi_luas">Luas Dilapangan</label>
								<input type="text" class="form-control" id="f_produksi_luas" name="f_produksi_luas"
									placeholder="Luas jalan dilapangan" />
							</div>

							<div class="form-group">
								<label for="f_produksi_keterangan">Keterangan</label>
								<textarea class="form-control" id="f_produksi_keterangan" name="f_produksi_keterangan" rows="3"
									placeholder="Keterangan"></textarea>
							</div>

							<div class="form-group produksi-jalan-only">
								<label for="produksi_jalan_foto">Foto Kondisi Jalan Saat Ini</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept="image/*" multiple
										name="produksi_jalan_foto[]" id="produksi_jalan_foto"
										onchange="displayUploadedFiles(this, 'list_produksi_jalan_foto')" />
									<label class="custom-file-label" id="label_produksi_jalan_foto"
										for="produksi_jalan_foto">Bisa lebih dari 1 foto</label>
								</div>
								<div id="list_produksi_jalan_foto" class="mt-1"></div>
							</div>
						</div>
						<div class="tab-pane produksi-jalan-only" id="prod-jalan-history" aria-labelledby="prod-jalan-history-tab"
							role="tabpanel">
							<div id="produksi_jalan_history" class="pt-1"></div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" id="save_fotherproduksi-btn" class="btn btn-primary data-submit mr-1"
						onclick="save_fotherproduksi()" href="javascript:void(0)">Simpan</button>
					<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade text-left" id="modal_divisi7" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="fm-produksi" enctype="multipart/form-data" class="add-new-record">

					<div class="produksi-modal-summary">
						<div>
							<p class="produksi-modal-title label_alamat" id="label_alamat7"></p>
						</div>
						<div class="produksi-modal-actions">
							<button id="produksi-edit-focus-btn" type="button"
								class="btn btn-outline-primary btn-block waves-effect"
								onclick="focusProduksiProgressForm()">
								<i class="fas fa-edit mr-50"></i>Ubah Data Produksi
							</button>
							<button id="download_gambar_kerja" type="button"
								class="btn btn-primary btn-block waves-effect">
								<i class="fas fa-download mr-50"></i>Unduh Gambar Kerja
							</button>
							<a id="produksi-mobile-link" class="btn btn-outline-primary btn-block waves-effect mt-50"
								href="<?= base_url('siteplan/produksi-mobile') ?>">
								<i class="fas fa-mobile-alt mr-50"></i>Mode Mobile
							</a>
						</div>
					</div>

					<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
					<input type="hidden" class="form-control" id="id_produksi" name="id_produksi" value="" />
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="fm-prod-progress-tab" data-toggle="tab"
								href="#fm-prod-progress" role="tab" aria-selected="true">Progres</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-dokumentasi-tab" data-toggle="tab"
								href="#fm-prod-dokumentasi" role="tab" aria-selected="true">Dokumentasi Bangunan</a>
						</li>
						<!-- <li class="nav-item">
							<a class="nav-link" id="fm-prod-slf-tab" data-toggle="tab" href="#fm-prod-slf" role="tab" aria-selected="true">SLF</a>
						</li> -->
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-jalan-tab" data-toggle="tab" href="#fm-prod-jalan"
								role="tab" aria-selected="true">Jalan</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-listrik-tab" data-toggle="tab" href="#fm-prod-listrik"
								role="tab" aria-selected="true">Listrik</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-air-tab" data-toggle="tab" href="#fm-prod-air" role="tab"
								aria-selected="true">Air</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="fm-prod-history-tab" data-toggle="tab" href="#fm-prod-history"
								role="tab" aria-selected="false">Riwayat</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="fm-prod-progress" aria-labelledby="fm-prod-progress-tab"
							role="tabpanel">
							<div id="produksi-progress-edit-area" class="produksi-progress-grid">
								<div class="produksi-compact-card">
									<div class="divider divider-left">
										<div class="divider-text">Progress Fisik</div>
									</div>
									<div class="produksi-checklist-grid">
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_0"
												name="st_0" />
											<label class="custom-control-label" for="st_0">sd Sloof</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_25"
												name="st_25" />
											<label class="custom-control-label" for="st_25">Dinding sd Ringbalok</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_50"
												name="st_50" />
											<label class="custom-control-label" for="st_50">Dinding Full, Atap, PLester
												dan Aci</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="st_75"
												name="st_75" />
											<label class="custom-control-label" for="st_75">Plafon, Keramik, Dapur,
												Kamar Mandi dan Cat</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_100" name="st_100" />
											<label class="custom-control-label" for="st_100">Kusen, Pintu, Jendela,
												Kaca, Halaman dan Finishing</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_saluran" name="st_saluran" />
											<label class="custom-control-label" for="st_saluran">Saluran Jalan</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_jalan" name="st_jalan" />
											<label class="custom-control-label" for="st_jalan">Listrik</label>
										</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp"
												id="st_air" name="st_air" />
											<label class="custom-control-label" for="st_air">Air</label>
										</div>
									</div>
									<!-- <div class="af"> -->
									<div class="">
										<div class="form-group">
											<div class="custom-control custom-switch custom-control-inline">
												<input type="checkbox" value="1" class="custom-control-input cbp"
													id="slo" name="slo" />
												<label class="custom-control-label" for="slo">SLO / NIDI</label>
											</div>
										</div>
										<!-- <div class="form-group">
											<div class="custom-control custom-switch custom-control-inline">
												<input type="checkbox" value="1" class="custom-control-input cbp"
													id="bp" name="bp" />
												<label class="custom-control-label" for="bp">BP</label>
											</div>
										</div> -->

									</div>
									</div>

								</div>
								<div class="produksi-compact-card">
									<div class="divider divider-left">
										<div class="divider-text">LPA</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="lpa"
												name="lpa" />
											<label class="custom-control-label" for="lpa">LPA</label>
										</div>
									</div>
									<div class="form-group">
										<label>Tanggal LPA</label>
										<input type="text" class="form-control flatpickr-human-friendly"
											id="lpa_tanggal" name="lpa_tanggal">
									</div>
									<div class="divider divider-left">
										<div class="divider-text">Sumur Bor</div>
									</div>
									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input cbp" id="sumurbor"
												name="sumurbor" />
											<label class="custom-control-label" for="sumurbor">Sumur Bor</label>
										</div>
									</div>
									<div class="form-group">
										<label>Tanggal Pemasangan Sumur Bor</label>
										<input type="text" class="form-control flatpickr-human-friendly"
											id="sumurbor_tanggal" name="sumurbor_tanggal">
									</div>
									<div class="form-group">
										<label for="sumurbor_keterangan">Keterangan Sumur Bor</label>
										<textarea class="form-control" id="sumurbor_keterangan"
											name="sumurbor_keterangan" rows="3" placeholder="Keterangan"></textarea>

										<small id="last_update-sumurbor" class="text-muted"></small>
									</div>
								</div>

								<div class="produksi-compact-card">
									<div class="divider divider-left">
										<div class="divider-text">Catatan Progres</div>
									</div>
									<div class="form-group">
										<label for="progres_bangunan">Progres Bangunan</label>
										<div class="produksi-range-box">
											<input type="range" class="form-control-range" value="0" id="progres_bangunan"
												name="progres_bangunan" step="1">
											<span class="produksi-range-value"><span id="t_progres_bangunan"></span>%</span>
										</div>
									</div>
									<div class="form-group">
										<label for="produksi_keterangan">Keterangan Pembangunan</label>
										<textarea class="form-control" id="produksi_keterangan"
											name="produksi_keterangan" rows="3" placeholder="Keterangan"></textarea>
									</div>

								</div>
								<div class="produksi-compact-card">
									<div class="divider divider-left">
										<div class="divider-text">Tanggal Pembangunan Rumah</div>
									</div>

										<div class="form-group">
											<label>Tanggal Pembangunan</label>
											<input type="text" class="form-control tanggal_pembangunan flatpickr-human-friendly tgl_bangun"
												id="tanggal_pembangunan" name="tanggal_pembangunan">
											<input type="text" class="hidden" id="tanggal_pembangunan_old"
												name="tanggal_pembangunan_old">
										</div>
										<span class="text-muted produksi-date-meta" id="lu-tanggal_pembangunan"></span>

										<div class="form-group">
											<label>Tanggal Rencana Selesai Pembangunan</label>
											<input type="text" class="form-control tanggal_rencana_selesai_pembangunan flatpickr-human-friendly tgl_bangun"
												id="tanggal_rencana_selesai_pembangunan"
												name="tanggal_rencana_selesai_pembangunan">
											<input type="text" class="hidden" id="tanggal_rencana_selesai_pembangunan_old"
												name="tanggal_rencana_selesai_pembangunan_old">
										</div>
										<span class="text-muted produksi-date-meta" id="lu-tanggal_rencana_selesai_pembangunan"></span>


										<div class="form-group">
											<label>Tanggal Selesai Pembangunan</label>
											<input type="text" class="form-control flatpickr-human-friendly "
												id="tanggal_selesai_pembangunan" name="tanggal_selesai_pembangunan">
											<input type="text" class="hidden" id="tanggal_selesai_pembangunan_old"
												name="tanggal_selesai_pembangunan_old">
										</div>
										<span class="text-muted produksi-date-meta" id="lu-tanggal_selesai_pembangunan"></span>


										<div class="hidden">
											<div class="form-group">
												<label>Diinput oleh</label>
												<input type="text" class="form-control" id="tanggal_pembangunan_oleh" disabled
													name="tanggal_pembangunan_oleh">
											</div>
											<div class="form-group">
												<label>Diinput Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_pembangunan_pada" disabled name="tanggal_pembangunan_pada">
											</div>
											<div class="form-group">
												<label>Diubah oleh</label>
												<input type="text" class="form-control" id="tanggal_pembangunan_diubah_oleh"
													disabled name="tanggal_pembangunan_diubah_oleh">
											</div>
											<div class="form-group">
												<label>Diubah Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_pembangunan_diubah_pada" disabled
													name="tanggal_pembangunan_diubah_pada">
											</div>
											<div class="form-group">
												<label>Diinput oleh</label>
												<input type="text" class="form-control" id="tanggal_selesai_pembangunan_oleh"
													disabled name="tanggal_selesai_pembangunan_oleh">
											</div>
											<div class="form-group">
												<label>Diinput Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_selesai_pembangunan_pada" disabled
													name="tanggal_selesai_pembangunan_pada">
											</div>
											<div class="form-group">
												<label>Diubah oleh</label>
												<input type="text" class="form-control"
													id="tanggal_selesai_pembangunan_diubah_oleh" disabled
													name="tanggal_selesai_pembangunan_diubah_oleh">
											</div>
											<div class="form-group">
												<label>Diubah Pada</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="tanggal_selesai_pembangunan_diubah_pada" disabled
													name="tanggal_selesai_pembangunan_diubah_pada">
											</div>
										</div>
								</div>
							</div>



							<div class="form-group hidden" style="min-height:100px; height: auto;">
								<label>RAB</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" accept=".xls,.xlsx,.pdf"
										name="rab_dokumen[]" id="rab_dokumen"
										onchange="displayUploadedFiles(this, 'list_rab_dokumen')" />
									<label class="custom-file-label" id="label_rab_dokumen" for="rab_dokumen">Upload
										dokuemn RAB</label>
								</div>
								<div id="list_rab_dokumen" style="display: flex; flex-wrap: wrap;"></div>
							</div>
						</div>
						<div class="tab-pane" id="fm-prod-dokumentasi" aria-labelledby="fm-prod-dokumentasi-tab"
							role="tabpanel">
							<div class="form-group foto-container">
								<label>Foto Konstruksi(Pembesian, Pondaasi Sloof & Kolom Ringbalok, Pekerjaan Dinding,
									Pekerjaan Atap & Plafon)</label>
								<div class="produksi-upload-action">
									<div>
										<strong class="d-block">Dokumentasi konstruksi</strong>
										<small class="text-muted">Tambah foto, pilih kategori pekerjaan, dan sistem akan menyimpan koordinat jika tersedia.</small>
									</div>
									<div class="produksi-upload-buttons">
										<button type="button" class="btn btn-primary btn-sm" onclick="triggerProduksiUpload('prod_foto_konstruksi')">
											<i class="fas fa-images mr-50"></i>Pilih Foto
										</button>
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('prod_foto_konstruksi_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="prod_foto_konstruksi[]" id="prod_foto_konstruksi" multiple
											onchange="displayUploadedFiles(this, 'list_prod_foto_konstruksi')" />
										<label class="custom-file-label" id="label_prod_foto_konstruksi"
											for="prod_foto_konstruksi">Bisa Lebih dari 1 foto</label>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="prod_foto_konstruksi_camera" capture="environment"
											data-produksi-upload-target="prod_foto_konstruksi"
											onchange="displayUploadedFiles(this, 'list_prod_foto_konstruksi')" />
										<label class="custom-file-label" id="label_prod_foto_konstruksi_camera"
											for="prod_foto_konstruksi_camera">Ambil foto dari kamera</label>
									</div>
								</div>
								<div id="list_prod_foto_konstruksi" style="display: flex; flex-wrap: wrap;"></div>
							</div>
							<hr>
							<div class="form-group foto-container">
								<label for="upload_komplain_produksi">Foto Exterior(Depan dan Belakang, foto memiliki
									titik koordinat)</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
										name="prod_foto_exterior[]" id="prod_foto_exterior" multiple
										onchange="displayUploadedFiles(this, 'list_prod_foto_exterior')" />
									<label class="custom-file-label" id="label_prod_foto_exterior"
										for="prod_foto_exterior">Bisa Lebih dari 1 foto</label>
								</div>
								<div class="produksi-camera-action">
									<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('prod_foto_exterior_camera')">
										<i class="fas fa-camera mr-50"></i>Ambil Kamera
									</button>
								</div>
								<div class="custom-file produksi-upload-hidden-file">
									<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
										id="prod_foto_exterior_camera" capture="environment"
										data-produksi-upload-target="prod_foto_exterior"
										onchange="displayUploadedFiles(this, 'list_prod_foto_exterior')" />
									<label class="custom-file-label" for="prod_foto_exterior_camera">Ambil foto dari kamera</label>
								</div>
								<div id="list_prod_foto_exterior" style="display: flex; flex-wrap: wrap;"></div>
							</div>
							<hr>
							<div class="form-group foto-container">
								<label for="upload_komplain_produksi">Foto Interior(kamar, dapur, toilet, ruang tengah,
									finishing cat kusen & pintu. Foto memiliki titik koordinat)</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
										name="prod_foto_interior[]" id="prod_foto_interior" multiple
										onchange="displayUploadedFiles(this, 'list_prod_foto_interior')" />
									<label class="custom-file-label" id="label_prod_foto_interior"
										for="prod_foto_interior">Bisa Lebih dari 1 foto</label>

								</div>
								<div class="produksi-camera-action">
									<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('prod_foto_interior_camera')">
										<i class="fas fa-camera mr-50"></i>Ambil Kamera
									</button>
								</div>
								<div class="custom-file produksi-upload-hidden-file">
									<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
										id="prod_foto_interior_camera" capture="environment"
										data-produksi-upload-target="prod_foto_interior"
										onchange="displayUploadedFiles(this, 'list_prod_foto_interior')" />
									<label class="custom-file-label" for="prod_foto_interior_camera">Ambil foto dari kamera</label>
								</div>
								<div id="list_prod_foto_interior" style="display: flex; flex-wrap: wrap;"></div>
							</div>

						</div>
						<!-- <div class="tab-pane" id="fm-prod-slf" aria-labelledby="fm-prod-slf-tab" role="tabpanel">
							<div class="divider divider-left">
								<div class="divider-text">Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (SLF)</div>
							</div>
							<div class="form-group">
								<label>Jenis Dokumen</label>
								<select id="slf_jenis" name="slf_jenis" class="form-control">
									<option value="SLF">SLF</option>
									<option value="Surat Pernyataan">Surat Pernyataan</option>
								</select>
							</div>
							<div id="slf-input-form">
								<div class="form-group">
									<label>No Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="slf_no" name="slf_no">
								</div>
								<div class="form-group">
									<label>Tanggal Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control flatpickr-human-friendly" id="slf_tanggal" name="slf_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_slf_dokumen">Dokumen Surat Pernyataan Laik Fungsi/Sertifikat Laik Fungsi</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf" name="slf_dokumen[]" id="slf_dokumen" onchange="displayUploadedFiles(this, 'list_slf_dokumen')" />
										<label class="custom-file-label" id="label_slf_dokumen" for="slf_dokumen"></label>
									</div>
									<div id="list_slf_dokumen"></div>
								</div>
							</div>
							<div id="surat_pernyataan-input-form" class="hidden">
								<div class="form-group">
									<label>No Surat Pernyataan Laik Fungsi(sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_no" name="surat_pernyataan_no">
								</div>
								<div class="form-group">
									<label>NPWP Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_npwp" name="surat_pernyataan_npwp">
								</div>
								<div class="form-group">
									<label>Nama Penertbit Surat Pernyataan Laik Fungsi (sesuai dokumen)</label>
									<input type="text" class="form-control" id="surat_pernyataan_nama" name="surat_pernyataan_nama">
								</div>
								<div class="form-group">
									<label>Tanggal Surat Pernyataan Laik Fungsi</label>
									<input type="text" class="form-control flatpickr-human-friendly" id="surat_pernyataan_tanggal" name="surat_pernyataan_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_surat_pernyataan_dokumen">Tanggal Surat Pernyataan Laik Fungsi</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf" name="surat_pernyataan_dokumen" id="surat_pernyataan_dokumen" onchange="displayUploadedFiles(this, 'list_surat_pernyataan_dokumen')" />
										<label class="custom-file-label" id="label_surat_pernyataan_dokumen" for="surat_pernyataan_dokumen"></label>

									</div>
									<div id="list_surat_pernyataan_dokumen" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
						</div> -->
						<div class="tab-pane" id="fm-prod-jalan" aria-labelledby="fm-prod-jalan-tab" role="tabpanel">
							<div class="divider divider-left">
								<div class="divider-text">Foto Jalan</div>
							</div>
							<div>
								<div class="form-group foto-container">
									<label for="jalan_foto">Foto Jalan</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="jalan_foto[]" id="jalan_foto"
											onchange="displayUploadedFiles(this, 'list_jalan_foto')" />
										<label class="custom-file-label" id="label_jalan_foto" for="jalan_foto"></label>
									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('jalan_foto_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="jalan_foto_camera" capture="environment"
											data-produksi-upload-target="jalan_foto"
											onchange="displayUploadedFiles(this, 'list_jalan_foto')" />
										<label class="custom-file-label" for="jalan_foto_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_jalan_foto" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div>
								<div class="form-group foto-container">
									<label for="jalan_foto_update">Foto Jalan Update/Setelah Akad(Paving)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="jalan_foto_update[]" id="jalan_foto_update"
											onchange="displayUploadedFiles(this, 'list_jalan_foto_update')" />
										<label class="custom-file-label" id="label_jalan_foto_update"
											for="jalan_foto_update"></label>
									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('jalan_foto_update_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="jalan_foto_update_camera" capture="environment"
											data-produksi-upload-target="jalan_foto_update"
											onchange="displayUploadedFiles(this, 'list_jalan_foto_update')" />
										<label class="custom-file-label" for="jalan_foto_update_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_jalan_foto_update" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>

						</div>
						<div class="tab-pane" id="fm-prod-listrik" aria-labelledby="fm-prod-listrik-tab"
							role="tabpanel">
							<div class="divider divider-left">
								<div class="divider-text">Ketersediaan Listrik</div>
							</div>
							<div class="form-group">
								<label>Jenis Sumber Listrik</label>
								<select id="listrik_jenis" name="listrik_jenis" class="form-control">
									<option value="PLN">PLN</option>
									<option value="Disendiakan Pengembang">Disendiakan Pengembang (Dalam Pengajuan)
									</option>
								</select>
							</div>
							<div id="listrik-pln-input-form">
								<div class="form-group">
									<label>No ID Pelanggan/Nomor Meteran Listrik PLN</label>
									<input type="text" class="form-control" id="listrik_pln" name="listrik_pln">
								</div>
								<div class="form-group foto-container">
									<label for="label_slf_dokumen">Foto Ketersediaan Lampu Menyala</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="listrik_pln_foto[]" id="listrik_pln_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_pln_foto')" />
										<label class="custom-file-label" id="label_slf_dokumen"
											for="slf_dokumen"></label>

									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('listrik_pln_foto_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="listrik_pln_foto_camera" capture="environment"
											data-produksi-upload-target="listrik_pln_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_pln_foto')" />
										<label class="custom-file-label" for="listrik_pln_foto_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_listrik_pln_foto" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="listrik_disediakan" class="hidden">
								<div class="form-group">
									<label>No Pengajuan Listrik PLN</label>
									<input type="text" class="form-control" id="listrik_disediakan_no"
										name="listrik_disediakan_no">
								</div>
								<div class="form-group">
									<label>Tanggal Pengajuan Listrik PLN</label>
									<input type="text" class="form-control flatpickr-human-friendly"
										id="listrik_disediakan_tanggal" name="listrik_disediakan_tanggal">
								</div>
								<div class="form-group foto-container">
									<label for="label_listrik_disediakan_dokumen">Upload Bukti Pengajuan</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" accept="application/pdf"
											name="listrik_disediakan_dokumen" id="listrik_disediakan_dokumen"
											onchange="displayUploadedFiles(this, 'list_listrik_disediakan_dokumen')" />
										<label class="custom-file-label" id="label_listrik_disediakan_dokumen"
											for="listrik_disediakan_dokumen"></label>

									</div>
									<div id="list_listrik_disediakan_dokumen" style="display: flex; flex-wrap: wrap;">
									</div>
								</div>
								<div class="form-group foto-container">
									<label for="listrik_disediakan_foto">Foto Ketersediaan Lampu Menyala</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="listrik_disediakan_foto" id="listrik_disediakan_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_disediakan_foto')" />
										<label class="custom-file-label" id="labe_listrik_disediakan_foto"
											for="listrik_disediakan_foto"></label>

									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('listrik_disediakan_foto_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="listrik_disediakan_foto_camera" capture="environment"
											data-produksi-upload-target="listrik_disediakan_foto"
											onchange="displayUploadedFiles(this, 'list_listrik_disediakan_foto')" />
										<label class="custom-file-label" for="listrik_disediakan_foto_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_listrik_disediakan_foto" style="display: flex; flex-wrap: wrap;">
									</div>
								</div>
							</div>
						</div>
						<div class="tab-pane" id="fm-prod-air" aria-labelledby="fm-prod-air-tab" role="tabpanel">
							<div class="divider divider-left">
								<div class="divider-text">Ketersediaan Air</div>
							</div>
							<div class="form-group">
								<label>Jenis Sumber Air</label>
								<select id="air_jenis" name="air_jenis" class="form-control">
									<option value="Air Tanah">Air Tanah</option>
									<option value="Komunal Warga">Komunal Warga</option>
									<option value="PDAM">PDAM</option>
								</select>
							</div>
							<div id="air_tanah-input_form">
								<div class="form-group foto-container">
									<label for="air_tanah">Foto ketersediaan air bersih dengan air mengalir & sumber air
										(min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*" name="air_tanah[]"
											id="air_tanah" multiple
											onchange="displayUploadedFiles(this, 'list_air_tanah')" />
										<label class="custom-file-label" id="label_air_tanah" for="air_tanah"></label>

									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('air_tanah_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="air_tanah_camera" capture="environment"
											data-produksi-upload-target="air_tanah"
											onchange="displayUploadedFiles(this, 'list_air_tanah')" />
										<label class="custom-file-label" for="air_tanah_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_air_tanah" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="air_komunal-input_form" class="hidden">
								<div class="form-group foto-container">
									<label for="air_komunal">Foto ketersediaan air bersih dengan air mengalir & sumber
										air komunal bersama (min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											name="air_komunal[]" id="air_komunal" multiple
											onchange="displayUploadedFiles(this, 'list_air_komunal')" />
										<label class="custom-file-label" id="label_air_komunal"
											for="air_komunal"></label>

									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('air_komunal_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="air_komunal_camera" capture="environment"
											data-produksi-upload-target="air_komunal"
											onchange="displayUploadedFiles(this, 'list_air_komunal')" />
										<label class="custom-file-label" for="air_komunal_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_air_komunal" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div id="air_pdam-input_form" class="hidden">
								<div class="form-group">
									<label>No Meteran Air PDAM</label>
									<input type="text" class="form-control" id="air_pdam_no" name="air_pdam_no">
								</div>
								<div class="form-group foto-container">
									<label for="air_pdam">Foto ketersediaan air bersih dengan air mengalir & meteran air
										PDAM (min. 1 foto)</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*" name="air_pdam[]"
											id="air_pdam" multiple
											onchange="displayUploadedFiles(this, 'list_air_pdam')" />
										<label class="custom-file-label" id="label_air_pdam" for="air_pdam"></label>

									</div>
									<div class="produksi-camera-action">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('air_pdam_camera')">
											<i class="fas fa-camera mr-50"></i>Ambil Kamera
										</button>
									</div>
									<div class="custom-file produksi-upload-hidden-file">
										<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
											id="air_pdam_camera" capture="environment"
											data-produksi-upload-target="air_pdam"
											onchange="displayUploadedFiles(this, 'list_air_pdam')" />
										<label class="custom-file-label" for="air_pdam_camera">Ambil foto dari kamera</label>
									</div>
									<div id="list_air_pdam" style="display: flex; flex-wrap: wrap;"></div>
								</div>
							</div>
							<div class="form-group">
								<label>Deskripsi Unit (informasi keunggulan unit)</label>
								<input type="text" class="form-control" id="air_deskripsi_unit"
									name="air_deskripsi_unit">
							</div>
						</div>
						<div class="tab-pane" id="fm-prod-history" aria-labelledby="fm-prod-history-tab" role="tabpanel">
							<div class="divider divider-left">
								<div class="divider-text">Riwayat Perubahan Produksi</div>
							</div>
							<div id="produksi-history-timeline" class="produksi-history-list">
								<div class="text-muted">Memuat riwayat...</div>
							</div>
						</div>
					</div>

					<div class="divider divider-left hidden">
						<div class="divider-text">Checklist</div>
					</div>
					<p>
						<button data-toggle="collapse" href="#collapseExample" type="button"
							class="btn btn-outline-primary btn-block waves-effect hidden">Tampilkan Checklist</button>
					</p>
					<div class="collapse" id="collapseExample">
						<small id="last_update_checklist_prod" class="text-muted"></small>
						<div class="card card-body">
							<?php
							$n = 1;
							foreach ($list as $l) {
								echo '
                                    <div class="divider divider-left">
                                        <div class="divider-text">' . $n . '.) ' . $l->nama_group . ' - ' . $l->nama_item . '</div>
                                    </div>
                                    <dl class="row">
                                        <dd class="col-sm-2">' . $l->nama_subitem . '</dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_t[' . $l->id_subitem . ']" name="hasil_cek_t[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_t[' . $l->id_subitem . ']">Tes</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_f[' . $l->id_subitem . ']" name="hasil_cek_f[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_f[' . $l->id_subitem . ']">Fungsi</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-md-2">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-control-inline">
                                                    <input type="checkbox" value="1" class="custom-control-input" id="hasil_cek_v[' . $l->id_subitem . ']" name="hasil_cek_v[' . $l->id_subitem . ']"/>
                                                    <label class="custom-control-label" for="hasil_cek_v[' . $l->id_subitem . ']">Visual</label>
                                                </div>
                                            </div>
                                        </dd>
                                        <dd class="col-sm-4"><textarea placeholder="keterangan" type="text" class="form-control" id="keterangan_cek_produksi[' . $l->id_subitem . ']" name="keterangan_cek_produksi[' . $l->id_subitem . ']"></textarea></dd>
                                    </dl>
                                    ';
								$n++;
							}
							?>

						</div>
					</div>


				</form>
			</div>
			<div class="modal-footer">
				<button id="add-form-btn-produksi" class="btn btn-primary data-submit mr-1" onclick="save_produksi()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_komplain_produksi">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<form id="fm-komplain-produksi" class="add-new-record modal-content pt-0">
			<div class="modal-header mb-1">
				<h5 class="modal-title" id="exampleModalLabel">Komplain Kavling</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
			</div>
			<div class="modal-body flex-grow-1">
				<p class="modal-title label_alamat" id="label_alamat5"></p>
				<hr>
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="fmkp-komplain-tab" data-toggle="tab" href="#fmkp-komplain"
							aria-controls="fmkp-komplain" role="tab" aria-selected="true">Komplain</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="fmkp-ditangani-tab" data-toggle="tab" href="#fmkp-ditangani"
							aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Tangani</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="fmkp-selesai-tab" data-toggle="tab" href="#fmkp-selesai"
							aria-controls="fmkp-ditangani" role="tab" aria-selected="true">Selesai</a>
					</li>
				</ul>

				<input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
				<input type="hidden" class="form-control" id="id_komplain" name="id_komplain" value="" />
				<small id="last_update_komplain_produksi" class="text-muted"></small>

				<div class="tab-content">
					<div class="tab-pane active" id="fmkp-komplain" aria-labelledby="fmkp-komplain-tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="username_komplain_oleh">Dikomplain Oleh</label>
									<input readonly type="text" class="form-control" id="username_komplain_oleh"
										name="username_komplain_oleh" />
								</div>
								<div class="form-group">
									<label for="komplain_tgl">Tanggal Komplain</label>
									<input disabled type="text" class="form-control flatpickr-human-friendly"
										id="komplain_tgl" name="komplain_tgl" />
								</div>
								<div class="form-group">
									<label for="keterangan_komplain">Keterangan Komplain</label>
									<textarea readonly class="form-control" id="keterangan_komplain"
										name="keterangan_komplain" rows="3" placeholder="Keterangan"></textarea>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6">
								<button id="komplain_selesai_btn_produksi" type="button"
									class="btn btn-outline-success btn-block waves-effect hidden">Komplain
									Selesai</button>
								<h5>Foto Komplain</h5>
								<!-- -----------------------------------dikomplain--------------------------------------- -->
								<div id="controls_produksi_foto_komplain_sales" class="carousel slide">
									<div class="carousel-inner" id="foto_komplain_sales">
										<!-- Foto komplain belongs here -->
									</div>
									<a class="carousel-control-prev" href="#controls_produksi_foto_komplain_sales"
										role="button" data-slide="prev">
										<span class="carousel-control-prev-icon" aria-hidden="true"></span>
										<span class="sr-only">Previous</span>
									</a>
									<a class="carousel-control-next" href="#controls_produksi_foto_komplain_sales"
										role="button" data-slide="next">
										<span class="carousel-control-next-icon" aria-hidden="true"></span>
										<span class="sr-only">Next</span>
									</a>
								</div>

							</div>
						</div>
					</div>
					<div class="tab-pane" id="fmkp-ditangani" aria-labelledby="fmkp-ditangani-tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ------------------------------terima komplain------------------------------ -->
								<div class="divider">
									<div class="divider-text">Terima Komplain</div>
								</div>
								<div class="form-group">
									<div class="custom-control custom-switch custom-control-inline">
										<input type="checkbox" value="1" class="custom-control-input"
											id="terima_komplain" name="terima_komplain" />
										<label class="custom-control-label" for="terima_komplain">Terima
											Komplain</label>
									</div>
								</div>
								<div id="terima_komplain_div" class="hidden ditangani_form">
									<div class="form-group">
										<label for="keterangan_ditangani">Keterangan</label>
										<textarea class="form-control" id="keterangan_ditangani"
											name="keterangan_ditangani" rows="3" placeholder="Keterangan"></textarea>
									</div>
								</div>
								<div class="hidden ditangani_form">
									<div class="form-group">
										<label for="username_ditangani_oleh">Komplain Diterima Oleh</label>
										<input disabled type="text" class="form-control" id="username_ditangani_oleh"
											name="username_ditangani_oleh" />
									</div>
									<div class="form-group">
										<label for="ditangani_tgl">Tanggal Komplain Diterima</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="ditangani_tgl" name="ditangani_tgl" />
									</div>
								</div>
							</div>
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ---------------------------------------- komplain diselesaikan ---------------------------->
								<div id="selesaikan_komplain_div" class="hidden">
									<div class="divider">
										<div class="divider-text">Selesaikan Komplain</div>
									</div>

									<div class="form-group">
										<div class="custom-control custom-switch custom-control-inline">
											<input type="checkbox" value="1" class="custom-control-input"
												id="is_selesai_produksi" name="is_selesai_produksi" />
											<label class="custom-control-label" for="is_selesai_produksi">Selesaikan
												Komplain</label>
										</div>
									</div>
									<div id="div_upload_komplain_produksi">
										<label for="upload_komplain_produksi">Foto Perbaikan</label>
										<div class="custom-file">
											<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
												name="upload_komplain_produksi[]" id="upload_komplain_produksi"
												multiple
												onchange="displayUploadedFiles(this, 'list_upload_komplain_produksi')" />
											<label class="custom-file-label" id="label_upload_komplain_produksi"
												for="upload_komplain_produksi">Bisa Lebih dari 1 foto</label>
										</div>
										<div class="produksi-camera-action">
											<button type="button" class="btn btn-outline-primary btn-sm" onclick="triggerProduksiUpload('upload_komplain_produksi_camera')">
												<i class="fas fa-camera mr-50"></i>Ambil Kamera
											</button>
										</div>
										<div class="custom-file produksi-upload-hidden-file">
											<input type="file" class="custom-file-input produksi-photo-input" accept="image/*"
												id="upload_komplain_produksi_camera" capture="environment"
												data-produksi-upload-target="upload_komplain_produksi"
												onchange="displayUploadedFiles(this, 'list_upload_komplain_produksi')" />
											<label class="custom-file-label" for="upload_komplain_produksi_camera">Ambil foto dari kamera</label>
										</div>
										<div id="list_upload_komplain_produksi"></div>
									</div>
									<div class="form-group">
										<label for="selesai_keterangan_produksi">Keterangan </label>
										<textarea class="form-control" id="selesai_keterangan_produksi"
											name="selesai_keterangan_produksi" rows="3"
											placeholder="Keterangan"></textarea>
									</div>
									<div class="form-group">
										<label for="username_selesai_oleh_produksi">Diselesakan Oleh</label>
										<input disabled type="text" class="form-control"
											id="username_selesai_oleh_produksi" name="username_selesai_oleh_produksi" />
									</div>
									<div class="form-group">
										<label for="selesai_tgl_produksi">Tanggal Diselesaikan</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="selesai_tgl_produksi" name="selesai_tgl_produksi" />
									</div>
									<div id="controls_produksi_foto_komplain_produksi" class="carousel slide">
										<div class="carousel-inner" id="foto_komplain_produksi">
											<!-- Foto komplain belongs here -->
										</div>
										<a class="carousel-control-prev"
											href="#controls_produksi_foto_komplain_produksi" role="button"
											data-slide="prev">
											<span class="carousel-control-prev-icon" aria-hidden="true"></span>
											<span class="sr-only">Previous</span>
										</a>
										<a class="carousel-control-next"
											href="#controls_produksi_foto_komplain_produksi" role="button"
											data-slide="next">
											<span class="carousel-control-next-icon" aria-hidden="true"></span>
											<span class="sr-only">></span>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="fmkp-selesai" aria-labelledby="fmkp-selesai -tab" role="tabpanel">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-lg-6">
								<!-- ---------------------------------------- komplain diselesaikan ---------------------------->
								<div id="komplain_selesai_sip" class="hidden">
									<div class="divider">
										<div class="divider-text">Komplain Selesai (sales)</div>
									</div>
									<div class="form-group">
										<label for="selesai_keterangan_sales">Keterangan </label>
										<textarea disabled class="form-control" id="selesai_keterangan_sales"
											name="selesai_keterangan_sales" rows="3"
											placeholder="Keterangan"></textarea>
									</div>
									<div class="form-group">
										<label for="username_selesai_oleh_sales">Diselesakan Oleh</label>
										<input disabled type="text" class="form-control"
											id="username_selesai_oleh_sales" name="username_selesai_oleh_sales" />
									</div>
									<div class="form-group">
										<label for="selesai_tgl_sales">Tanggal Diselesaikan</label>
										<input disabled type="text" class="form-control flatpickr-human-friendly"
											id="selesai_tgl_sales" name="selesai_tgl_sales" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a id="komplain-produksi-form-btn" class="btn btn-primary data-submit mr-1"
					onclick="save_komplain_produksi()" href="javascript:void(0)">Simpan</a>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>


<div class="modal fade text-left" id="modal-pr_slf" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" style="background-color:ccc">
				<div class="card">
					<div class="card-body">
						<p class="modal-title label_alamat"></p>
					</div>
				</div>

				<form id="fm-pr_slf" enctype="multipart/form-data">
					<div class="card">
						<div class="card-body">
							<ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="fm-pr_list_slf-tab" data-toggle="tab"
										href="#fm-pr_list_slf" role="tab" aria-selected="true">List SLF</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="fm-pr_cr_slf-tab" data-toggle="tab" href="#fm-pr_cr_slf"
										role="tab" aria-selected="true">Buat SLF</a>
								</li>
							</ul>
						</div>
					</div>

					<div class="card">
						<div class="card-body">
							<div class="tab-content">
								<div class="tab-pane active" id="fm-pr_list_slf" aria-labelledby="fm-pr_list_slf-tab"
									role="tabpanel">
									<div class="table-responsive">
										<table class="table mb-0">
											<thead>
												<tr>
													<th width="10px">No</th>
													<th width="150px">No SLF</th>
													<th>Kavling</th>
													<th width="180px">File</th>
													<th width="150px">Oleh</th>
												</tr>
											</thead>
											<tbody id="tb-pr_lsit_slf-here">
											</tbody>

										</table>
									</div>

								</div>
								<div class="tab-pane" id="fm-pr_cr_slf" aria-labelledby="fm-pr_cr_slf-tab"
									role="tabpanel">
									<div class="row">


										<div class="col-md-4">
											<div class="divider">
												<div class="divider-text">SURAT PERNYATAAN PEMERIKSAAN KELAIKAN FUNGSI
													BANGUNAN GEDUNG</div>
											</div>
											<div class="form-group">
												<label>No Surat Pernyataan</label>
												<input type="text" class="form-control" id="fm-slf-no_slf" name="no_slf"
													placeholder="" required
													value="...../PROD-..../EX/BTN/DIR/...../<?= date('Y') ?>" />
											</div>
											<div class="form-group">
												<label>Tanggal</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-tgl_slf" required name="tgl_slf" placeholder=""
													value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Penyedia Jasa Pengawas/MK/Instansi Teksnis
													Pembina
													Penyelenggaraan Bangunan gedung</div>
											</div>
											<div class="form-group">
												<label>Nama Penanggung Jawab</label>
												<input type="text" required class="form-control"
													id="fm-slf-penanggungjawab" name="penanggungjawab" placeholder=""
													value="" />
											</div>
											<div class="form-group">
												<label>Nama Perusahaan/Instansi Teknis</label>
												<input type="text" required class="form-control" readonly
													id="fm-slf-nama_perusahaan" name="nama_perusahaan" placeholder=""
													value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Bangunan Gedung</div>
											</div>
											<div class="form-group">
												<label>Funsgi Utama </label>
												<input type="text" class="form-control" id="fm-slf-fungsi_utama"
													name="fungsi_utama" required placeholder="" value="Rumah Tinggal" />
											</div>
											<div class="form-group">
												<label>Funsgi Tambahan </label>
												<input type="text" class="form-control" id="fm-slf-fungsi_tambahan"
													name="fungsi_tambahan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Jenis Bangunan </label>
												<input type="text" class="form-control" id="fm-slf-jenis_bangunan"
													name="jenis_bangunan" required placeholder=""
													value="Rumah Tinggal" />
											</div>
											<div class="form-group">
												<label>Nama Bangunan Gedung </label>
												<input type="text" class="form-control" id="fm-slf-nama_bangunan"
													name="nama_bangunan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>No Pendaftaran Bangunan </label>
												<input type="text" class="form-control"
													id="fm-slf-nomor_pendaftaran_bangunan"
													name="nomor_pendaftaran_bangunan" placeholder="" value="" />
											</div>
										</div>
										<div class="col-md-4">
											<div class="divider">
												<div class="divider-text">Lokasi Bangunan Gedung</div>
											</div>
											<div class="form-group">
												<label>Kampung</label>
												<input type="text" readonly class="form-control" id="fm-slf-kampung"
													name="kampung" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kelurahan/desa</label>
												<input type="text" readonly class="form-control" id="fm-slf-kelurahan"
													name="kelurahan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kecamatan</label>
												<input type="text" readonly class="form-control" id="fm-slf-kecamatan"
													name="kecamatan" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Kabupaten/Kota</label>
												<input type="text" readonly class="form-control" id="fm-slf-kota"
													name="kota" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Provinsi</label>
												<input type="text" readonly class="form-control" id="fm-slf-provinsi"
													name="provinsi" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Alamat lokasi terletak di</label>
												<input type="text" readonly class="form-control" id="fm-slf-alamat"
													name="alamat" placeholder="" value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Permohonan</div>
											</div>
											<div class="form-group">
												<label>No Penerbitan SLF</label>
												<input type="text" class="form-control" id="fm-slf-penerbitan_slf_no"
													required name="penerbitan_slf_no" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Tanggal Penerbitan SLF</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-penerbitan_slf_tgl" required name="penerbitan_slf_tgl"
													placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>No Perpanjangan SLF</label>
												<input type="text" class="form-control" id="fm-slf-perpanjangan_slf_no"
													name="perpanjangan_slf_no" placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Tanggal Perpanjangan SLF</label>
												<input type="text" class="form-control flatpickr-human-friendly"
													id="fm-slf-perpanjangan_slf_tgl" name="perpanjangan_slf_tgl"
													placeholder="" value="" />
											</div>
											<div class="form-group">
												<label>Perpanjangan ke</label>
												<input type="text" class="form-control" id="fm-slf-perpanjangan_slf_ke"
													name="perpanjangan_slf_ke" placeholder="" value="" />
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label>Persyaratan Administrasi</label>
												<input type="text" class="form-control"
													id="fm-slf-persyaratan_administrasi" name="persyaratan_administrasi"
													placeholder="" value="" />
											</div>
											<div class="divider">
												<div class="divider-text">Persyaratan Teknis</div>
											</div>
											<div class="form-group">
												<label>Fungsi Bangunan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_bangunan"
													name="fungsi_bangunan" placeholder="" value="Layak" />
											</div>
											<div class="form-group">
												<label>Peruntukan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_peruntukan"
													name="fungsi_peruntukan" placeholder="" value="Sesuai" />
											</div>
											<div class="form-group">
												<label>Tata Bangunan</label>
												<input type="text" class="form-control" id="fm-slf-fungsi_tata_bangunan"
													name="fungsi_tata_bangunan" placeholder="" value="Sesuai" />
											</div>
											<div class="form-group">
												<label>Kelaikan Fungsi Bangunan gedung dinyatakan</label>
												<select class="form-control" id="fm-slf-persyaratan_kelaikan"
													name="persyaratan_kelaikan">
													<option value="Laik fungsi seluruhnya">Laik fungsi seluruhnya
													</option>
													<option value="Laik fungsi sebagian">Laik fungsi sebagian</option>
												</select>
											</div>
											<div class="divider">
												<div class="divider-text">Pilih Kavling</div>
											</div>
											<select name="id_kavling[]" required class="form-control-sm select2"
												id="fm-slf-id_kavling" multiple="multiple"></select>
										</div>
									</div>



								</div>

							</div>
						</div>
					</div>


				</form>
			</div>
			<div class="modal-footer">
				<button id="btn-slf-simpan" class="btn btn-primary data-submit mr-1" onclick="simpan_slf()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade text-left" id="modal-bayar_produksi-prod" tabindex="-1" role="dialog"
	aria-labelledby="modal-bayar_produksi-prod-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
		<form id="fm-bayar_produksi-prod" class="add-new-record modal-content pt-0" autocomplete="off">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-bayar_produksi-prod-label">Pembayaran Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body flex-grow-1 prod-bp-body">
				<div class="prod-bp-layout">
					<aside class="prod-bp-sidebar">
						<div class="card prod-bp-hero">
							<div class="card-body bg-primary text-light">
								<p class="modal-title label_alamat"></p>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<div class="divider divider-left">
									<div class="divider-text">Info Konsumen</div>
								</div>
								<div class="card prod-bp-meta-card">
									<div class="card-body">
										<h6><i class="fas fa-users"></i> Konsumen</h6>
										<h5><strong><span id="fm-bp-label_konsumen">-</span></strong></h5>
										<h6><i class="fas fa-calendar"></i> Tanggal Booking</h6>
										<h5 class="mb-0"><strong><span id="fm-bp-label_tgl">-</span> (Rp. <span id="fm-bp-label_bookingfee">0</span>)</strong></h5>
									</div>
								</div>
							</div>
						</div>
					</aside>
					<section class="prod-bp-content">
						<input type="hidden" class="form-control" id="bayar_produksi-id_kavling" name="id_kavling">

						<div class="card">
							<div class="card-body">
								<div class="divider divider-left">
									<div class="divider-text">Form Pembayaran Produksi</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-lg-3">
										<div class="form-group">
											<label for="bp-untuk_pembayaran">Untuk Pembayaran</label>
											<select name="bp-untuk_pembayaran" id="bp-untuk_pembayaran"
												class="form-control form-select"></select>
										</div>
									</div>
									<div class="col-md-6 col-lg-3">
										<div class="form-group">
											<label for="bp-tanggal_bayar">Tanggal Pembayaran</label>
											<input type="text" id="bp-tanggal_bayar" name="bp-tanggal_bayar"
												class="form-control flatpickr-human-friendly" placeholder="-" />
										</div>
									</div>
									<div class="col-md-6 col-lg-3">
										<div class="form-group">
											<label for="bp-nominal">Nominal Pembayaran</label>
											<input type="text" class="form-control num" id="bp-nominal" name="bp-nominal">
										</div>
									</div>
									<div class="col-md-6 col-lg-3">
										<div class="form-group mb-0">
											<label for="bp-keterangan">Keterangan Pembayaran</label>
											<textarea class="form-control" id="bp-keterangan" name="bp-keterangan"
												rows="3" placeholder="Keterangan"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="card mb-0">
							<div class="card-body">
								<div class="divider divider-left">
									<div class="divider-text">Riwayat Pembayaran Produksi</div>
								</div>
								<div class="table-responsive">
									<table id="bayar-produksi-table" class="datatables-basic table table-sm compact mb-0">
										<thead>
											<tr>
												<th width=""></th>
												<th width="20%">Item</th>
												<th width="20%">Tanggal Pembayaran</th>
												<th width="25%">Nominal</th>
												<th width="35%">Keterangan</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="modal-footer">
				<button id="add-form-btn-bayar_produksi" class="btn btn-primary data-submit mr-1"
					onclick="save_bayar_produksi(); return false;" href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</form>
	</div>
</div>


<div class="modal fade text-left" id="modal-cashoutsubkon" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-scrollable modal-xl">
		<div class="modal-content pt-0">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Produksi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body bg-primary text-light">
								<p class="modal-title label_alamat"></p>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body pb-0 pt-0">
								<ul class="nav nav-pills flex-column flex-md-row mt-1 row-gap-2" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="status-tab" data-toggle="tab" href="#status"
											aria-controls="detail_tagihan" role="tab" aria-selected="false">Status </a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="add-form-btn-produksi" class="btn btn-primary data-submit mr-1" onclick="save_produksi()"
					href="javascript:void(0)">Simpan</button>
				<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/produksi.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/produksi.js') ?>"></script>