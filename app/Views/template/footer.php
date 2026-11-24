   <div id="toast-container"
       class="toast-container position-fixed top-0 end-0 p-3"
       style="z-index: 0;">
   </div>
   <div class="sidenav-overlay"></div>
   <div class="drag-target"></div>
   <div id="loading" role="status" class="hidden" style="z-index:9999"></div>
   <div class="sigapp-mobile-sheet-backdrop" data-sigapp-mobile-close></div>
   <div id="sigapp-mobile-filter-sheet" class="sigapp-mobile-sheet" aria-hidden="true">
       <div class="sigapp-mobile-sheet-panel">
           <div class="sigapp-mobile-sheet-handle"></div>
           <div class="sigapp-mobile-sheet-header">
               <h5 class="sigapp-mobile-sheet-title">Filter</h5>
               <button type="button" class="sigapp-mobile-sheet-close" data-sigapp-mobile-close aria-label="Tutup">
                   <i data-feather="x"></i>
               </button>
           </div>
           <div class="sigapp-mobile-sheet-body" data-sigapp-mobile-filter-body></div>
       </div>
   </div>
   <div id="sigapp-mobile-action-sheet" class="sigapp-mobile-sheet" aria-hidden="true">
       <div class="sigapp-mobile-sheet-panel">
           <div class="sigapp-mobile-sheet-handle"></div>
           <div class="sigapp-mobile-sheet-header">
               <h5 class="sigapp-mobile-sheet-title">Aksi</h5>
               <button type="button" class="sigapp-mobile-sheet-close" data-sigapp-mobile-close aria-label="Tutup">
                   <i data-feather="x"></i>
               </button>
           </div>
           <div class="sigapp-mobile-sheet-body" data-sigapp-mobile-action-body></div>
       </div>
   </div>
   <nav id="sigapp-mobile-bottom-nav" aria-label="Navigasi mobile">
       <button type="button" class="sigapp-mobile-bottom-nav-item" data-sigapp-mobile-nav="back">
           <i data-feather="arrow-left"></i>
           <span>Back</span>
       </button>
       <button type="button" class="sigapp-mobile-bottom-nav-item" data-sigapp-mobile-nav="filter">
           <i data-feather="sliders"></i>
           <span>Filter</span>
       </button>
       <button type="button" class="sigapp-mobile-bottom-nav-item" data-sigapp-mobile-nav="actions">
           <i data-feather="grid"></i>
           <span>Aksi</span>
       </button>
       <button type="button" class="sigapp-mobile-bottom-nav-item" data-sigapp-mobile-nav="menu">
           <i data-feather="menu"></i>
           <span>Menu</span>
       </button>
   </nav>

   <div class="modal fade" id="sigapp-display-scale-modal" tabindex="-1" role="dialog" aria-labelledby="sigapp-display-scale-modal-title" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="sigapp-display-scale-modal-title">
                       <span class="detail-title-icon" aria-hidden="true">
                           <i data-feather="sliders"></i>
                       </span>
                       <span>
                           Pengaturan Tampilan
                           <small class="sigapp-scale-subtitle">Atur kepadatan tata letak dan ukuran huruf secara terpisah</small>
                       </span>
                   </h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   <div class="card">
                       <div class="card-body">
                           <div class="divider divider-left">
                               <div class="divider-text">Skala Tata Letak &amp; Ukuran Font</div>
                           </div>

                           <div class="sigapp-scale-control">
                               <div class="sigapp-scale-control-head">
                                   <div>
                                       <label class="sigapp-scale-label" for="sigapp-ui-scale-range">Skala Tata Letak &amp; Jarak (UI Scale)</label>
                                       <span class="sigapp-scale-desc">Mengatur padding, margin, tinggi input, dan jarak antar elemen</span>
                                   </div>
                                   <span class="sigapp-scale-value-pill" id="sigapp-ui-scale-badge">100%</span>
                               </div>
                               <div class="sigapp-scale-presets" role="group" aria-label="Preset skala tata letak">
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="ui" data-scale-value="0.8">80% · Sangat Padat</button>
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="ui" data-scale-value="0.9">90% · Padat</button>
                                   <button type="button" class="sigapp-scale-preset-btn is-active" data-scale-target="ui" data-scale-value="1" aria-pressed="true">100% · Normal</button>
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="ui" data-scale-value="1.1">110% · Nyaman</button>
                               </div>
                               <div class="sigapp-scale-slider-row">
                                   <button type="button" class="sigapp-scale-step-btn" id="sigapp-ui-scale-dec" data-scale-target="ui" data-scale-step="-0.05" aria-label="Perkecil skala tata letak">-</button>
                                   <input type="range" class="sigapp-scale-range" id="sigapp-ui-scale-range" min="0.75" max="1.15" step="0.05" value="1">
                                   <button type="button" class="sigapp-scale-step-btn" id="sigapp-ui-scale-inc" data-scale-target="ui" data-scale-step="0.05" aria-label="Perbesar skala tata letak">+</button>
                               </div>
                               <div class="sigapp-scale-ticks">
                                   <span>75%</span>
                                   <span>100%</span>
                                   <span>115%</span>
                               </div>
                           </div>

                           <div class="sigapp-scale-control">
                               <div class="sigapp-scale-control-head">
                                   <div>
                                       <label class="sigapp-scale-label" for="sigapp-font-scale-range">Ukuran Font (Font Scale)</label>
                                       <span class="sigapp-scale-desc">Mengatur ukuran teks global dan isi sel tabel DataTables</span>
                                   </div>
                                   <span class="sigapp-scale-value-pill" id="sigapp-font-scale-badge">100%</span>
                               </div>
                               <div class="sigapp-scale-presets" role="group" aria-label="Preset ukuran font">
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="font" data-scale-value="0.9">90% · Kecil</button>
                                   <button type="button" class="sigapp-scale-preset-btn is-active" data-scale-target="font" data-scale-value="1" aria-pressed="true">100% · Normal</button>
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="font" data-scale-value="1.1">110% · Besar</button>
                                   <button type="button" class="sigapp-scale-preset-btn" data-scale-target="font" data-scale-value="1.2">120% · Sangat Besar</button>
                               </div>
                               <div class="sigapp-scale-slider-row">
                                   <button type="button" class="sigapp-scale-step-btn" id="sigapp-font-scale-dec" data-scale-target="font" data-scale-step="-0.05" aria-label="Perkecil ukuran font">-</button>
                                   <input type="range" class="sigapp-scale-range" id="sigapp-font-scale-range" min="0.85" max="1.25" step="0.05" value="1">
                                   <button type="button" class="sigapp-scale-step-btn" id="sigapp-font-scale-inc" data-scale-target="font" data-scale-step="0.05" aria-label="Perbesar ukuran font">+</button>
                               </div>
                               <div class="sigapp-scale-ticks">
                                   <span>85%</span>
                                   <span>100%</span>
                                   <span>125%</span>
                               </div>
                           </div>
                       </div>
                   </div>

                   <div class="card">
                       <div class="card-body">
                           <div class="divider divider-left">
                               <div class="divider-text">Preview Langsung</div>
                           </div>
                           <div class="sigapp-scale-preview-box">
                               <div class="sigapp-scale-preview-row">
                                   <div>
                                       <div class="sigapp-scale-preview-title">Contoh Kartu Data Kavling</div>
                                       <div class="sigapp-scale-preview-meta">Perubahan diterapkan langsung ke halaman &amp; tersimpan otomatis di browser ini.</div>
                                   </div>
                                   <span class="sigapp-scale-preview-badge">Aktif</span>
                               </div>
                               <div class="sigapp-scale-preview-controls">
                                   <span class="sigapp-scale-preview-label">Unit</span>
                                   <input type="text" class="sigapp-scale-preview-input" value="Blok A-12 · Ready" readonly tabindex="-1" aria-label="Contoh input preview">
                                   <button type="button" class="sigapp-scale-preview-btn" tabindex="-1">Simpan</button>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-outline-secondary" id="sigapp-display-scale-reset">
                       <i class="mr-50" data-feather="rotate-ccw"></i>Reset Default (100% / 100%)
                   </button>
                   <button type="button" class="btn btn-primary" data-dismiss="modal">Selesai</button>
               </div>
           </div>
       </div>
   </div>
   <!-- BEGIN: Footer-->
   <!-- <footer class="footer footer-static footer-light">
       <p class="clearfix mb-0"><span class="float-md-left d-block d-md-inline-block mt-25">COPYRIGHT &copy; 2021<a class="ml-25" href="https://1.envato.market/pixinvent_portfolio" target="_blank">Pixinvent</a><span class="d-none d-sm-inline-block">, All rights Reserved</span></span><span class="float-md-right d-none d-md-block">Hand-crafted & Made with<i data-feather="heart"></i></span></p>
   </footer> -->
   <button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
   <!-- END: Footer-->

   <!-- BEGIN: Page Vendor JS-->
   <script>
       if (typeof jQuery === 'undefined') {
           document.write('<script src="<?= base_url('app-assets/vendors/js/vendors.min.js') ?>"><\/script>');
       }
   </script>
   <!-- END: Page Vendor JS-->

   <!-- BEGIN: Theme JS-->
   <script src="<?= base_url() ?>/app-assets/js/core/app-menu.min.js"></script>
   <script src="<?= base_url() ?>/app-assets/js/core/app.min.js"></script>
   <script src="<?= base_url() ?>/app-assets/vendors/js/forms/cleave/cleave.min.js"></script>
   <script src="<?= base_url() ?>/assets/js/active-proyek.js?<?= filemtime(FCPATH . 'assets/js/active-proyek.js') ?>"></script>
   <script src="<?= base_url() ?>/assets/js/scripts.js?<?= filemtime(FCPATH . 'assets/js/scripts.js') ?>"></script>
   <script src="<?= base_url('assets/js/display-scale.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/display-scale.js') ?>"></script>
   <script>
       window.SIGAPP_PWA = {
           serviceWorkerUrl: "<?= base_url('sw.js') ?>",
           serviceWorkerScope: "<?= base_url() ?>"
       };
       window.VAPID_PUBLIC_KEY = "<?= getenv('VAPID_PUBLIC_KEY') ?>";
   </script>
   <script src="<?= base_url('assets/js/pwa-install.js') ?>?<?= filemtime(FCPATH . 'assets/js/pwa-install.js') ?>"></script>
   <script src="<?= base_url('assets/js/push-subscription.js') ?>?<?= filemtime(FCPATH . 'assets/js/push-subscription.js') ?>"></script>
   <!-- END: Theme JS-->

   <!-- BEGIN: Page JS-->
   <!-- END: Page JS-->

   <script>
       function initFlatpickrHumanFriendly(scope) {
           if (typeof flatpickr === 'undefined') {
               return;
           }

           var root = scope || document;
           var inputs = root.querySelectorAll ? root.querySelectorAll('.flatpickr-human-friendly') : [];

           inputs.forEach(function(input) {
               if ((!input.name && !input.id) || input._flatpickr || input.dataset.flatpickrHumanFriendlyReady === '1') {
                   return;
               }

               input.dataset.flatpickrHumanFriendlyReady = '1';
               flatpickr(input, {
                   altInput: true,
                   altFormat: 'F j, Y',
                   dateFormat: 'Y-m-d'
               });
           });
       }

       $(function() {
           initFlatpickrHumanFriendly();

           if (typeof MutationObserver !== 'undefined') {
               var flatpickrObserverTimer = null;
               var flatpickrObserver = new MutationObserver(function(mutations) {
                   var shouldInit = mutations.some(function(mutation) {
                       return Array.prototype.some.call(mutation.addedNodes, function(node) {
                           return node.nodeType === 1 && (
                               node.classList && node.classList.contains('flatpickr-human-friendly') ||
                               node.querySelector && node.querySelector('.flatpickr-human-friendly')
                           );
                       });
                   });

                   if (!shouldInit) {
                       return;
                   }

                   clearTimeout(flatpickrObserverTimer);
                   flatpickrObserverTimer = setTimeout(function() {
                       initFlatpickrHumanFriendly();
                   }, 50);
               });

               flatpickrObserver.observe(document.body, {
                   childList: true,
                   subtree: true
               });
           }
       });

       $(window).on('load', function() {
           if (feather) {
               feather.replace({
                   width: 14,
                   height: 14
               });
           }

           loading(false)
       })

       function loading(hiden = true) {
           if (hiden) return $("#loading").removeClass('hidden')
           return $("#loading").addClass('hidden')
       }
   </script>
   </body>
   <!-- END: Body-->

   </html>
