   <div id="toast-container"
       class="toast-container position-fixed top-0 end-0 p-3"
       style="z-index: 0;">
   </div>
   <div class="sidenav-overlay"></div>
   <div class="drag-target"></div>
   <div id="loading" role="status" class="hidden" style="z-index:9999"></div>
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
