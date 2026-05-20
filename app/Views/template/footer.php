   <div id="toast-container"
       class="toast-container position-fixed top-0 end-0 p-3"
       style="z-index: 0;">
   </div>
   <div class="sidenav-overlay"></div>
   <div class="drag-target"></div>
   <!-- END: Footer-->

   <!-- BEGIN: Page Vendor JS-->
   <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    
    <!-- Feather Icons JS -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

    <!-- Custom Layout JS -->
    <script src="<?= base_url() ?>/assets/js/layout.js?<?= filemtime(FCPATH . 'assets/js/layout.js') ?>"></script>
    
    <script src="<?= base_url() ?>/app-assets/vendors/js/forms/cleave/cleave.min.js"></script>
    <script src="<?= base_url() ?>/assets/js/scripts.js?<?= filemtime(FCPATH . 'assets/js/scripts.js') ?>"></script>
    <!-- END: Theme JS-->

   <!-- BEGIN: Page JS-->
   <!-- END: Page JS-->

   <script>
       $(window).on('load', function() {
           if (feather) {
               feather.replace({
                   width: 14,
                   height: 14
               });
           }
       })

       function loading() {

       }
   </script>
   </body>
   <!-- END: Body-->

   </html>