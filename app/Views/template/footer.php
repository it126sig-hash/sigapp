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
   <!-- BEGIN: Footer-->
   <!-- <footer class="footer footer-static footer-light">
       <p class="clearfix mb-0"><span class="float-md-left d-block d-md-inline-block mt-25">COPYRIGHT &copy; 2021<a class="ml-25" href="https://1.envato.market/pixinvent_portfolio" target="_blank">Pixinvent</a><span class="d-none d-sm-inline-block">, All rights Reserved</span></span><span class="float-md-right d-none d-md-block">Hand-crafted & Made with<i data-feather="heart"></i></span></p>
   </footer> -->
   <button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
   <!-- END: Footer-->

   <!-- BEGIN: Page Vendor JS-->
   <!-- END: Page Vendor JS-->

   <!-- BEGIN: Theme JS-->
   <script src="<?= base_url() ?>/app-assets/js/core/app-menu.min.js"></script>
   <script src="<?= base_url() ?>/app-assets/js/core/app.min.js"></script>
   <script src="<?= base_url() ?>/app-assets/vendors/js/forms/cleave/cleave.min.js"></script>
   <script src="<?= base_url() ?>/assets/js/active-proyek.js?<?= filemtime(FCPATH . 'assets/js/active-proyek.js') ?>"></script>
   <script src="<?= base_url() ?>/assets/js/scripts.js?<?= filemtime(FCPATH . 'assets/js/scripts.js') ?>"></script>
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
