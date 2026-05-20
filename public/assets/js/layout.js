/**
 * SIGAPP Layout Controller (Monochrome Tech UI)
 * Mengatur perilaku sidebar, navbar, sub-menu accordion, dan responsivitas mobile.
 */

$(function () {
  "use strict";

  const $body = $("body");
  const $sidebar = $(".sidebar-wrapper");
  const $sidebarToggle = $("#sidebar-toggle");
  const $mobileToggle = $("#mobile-toggle");

  // 1. Inisialisasi Status Sidebar dari localStorage (Desktop)
  const isCollapsed = localStorage.getItem("sidebar-collapsed") === "true";
  if (isCollapsed && $(window).width() > 991.98) {
    $body.addClass("sidebar-collapsed");
  }

  // 2. Toggle Sidebar (Desktop)
  $sidebarToggle.on("click", function (e) {
    e.preventDefault();
    if ($(window).width() > 991.98) {
      $body.toggleClass("sidebar-collapsed");
      const currentlyCollapsed = $body.hasClass("sidebar-collapsed");
      localStorage.setItem("sidebar-collapsed", currentlyCollapsed);
    }
  });

  // 3. Toggle Sidebar Mobile (Drawer Slide-in)
  $mobileToggle.on("click", function (e) {
    e.preventDefault();
    openMobileSidebar();
  });

  function openMobileSidebar() {
    $body.addClass("mobile-sidebar-open");
    // Tambah backdrop jika belum ada
    if ($(".sidebar-backdrop").length === 0) {
      $("<div class='sidebar-backdrop'></div>")
        .appendTo("body")
        .fadeIn(200)
        .on("click", function () {
          closeMobileSidebar();
        });
    }
  }

  function closeMobileSidebar() {
    $body.removeClass("mobile-sidebar-open");
    $(".sidebar-backdrop").fadeOut(200, function () {
      $(this).remove();
    });
  }

  // Tombol close di mobile sidebar (jika ada)
  $(document).on("click", "#sidebar-close", function (e) {
    e.preventDefault();
    closeMobileSidebar();
  });

  // 4. Accordion Sub-Menu
  // Klik pada menu parent yang memiliki submenu
  $(document).on("click", ".menu-item-parent.has-submenu > a", function (e) {
    // Jika sidebar dalam kondisi kolaps di desktop, klik langsung membuka link atau memunculkan menu.
    // Tetapi karena accordion vertikal, kita akan ekspansi sidebar terlebih dahulu jika diklik saat kolaps.
    if ($body.hasClass("sidebar-collapsed") && $(window).width() > 991.98) {
      $body.removeClass("sidebar-collapsed");
      localStorage.setItem("sidebar-collapsed", "false");
    }

    e.preventDefault();
    const $parentLi = $(this).parent();
    const $subMenu = $parentLi.children(".menu-content");

    if ($parentLi.hasClass("menu-open")) {
      // Tutup menu
      $subMenu.slideUp(250, function () {
        $parentLi.removeClass("menu-open");
      });
    } else {
      // Tutup menu saudara kandung (sibling) yang sedang terbuka
      $parentLi.siblings(".menu-open").each(function () {
        $(this).removeClass("menu-open");
        $(this).children(".menu-content").slideUp(250);
      });

      // Buka menu ini
      $parentLi.addClass("menu-open");
      $subMenu.slideDown(250);
    }
  });

  // 5. Menentukan Menu Aktif Berdasarkan URL Saat Ini
  const currentPath = window.location.pathname;
  $(".sidebar-menu a").each(function () {
    const href = $(this).attr("href");
    if (href && currentPath.indexOf(href) !== -1 && href !== base_url + "/") {
      $(this).parent("li").addClass("active");
      
      // Jika di dalam submenu, buka parent menu-nya
      const $parentSub = $(this).closest(".menu-content");
      if ($parentSub.length) {
        $parentSub.show();
        $parentSub.closest(".menu-item-parent").addClass("menu-open active");
      }
    }
  });

  // Responsif: Tutup mobile drawer jika layar di-resize ke desktop
  $(window).on("resize", function () {
    if ($(window).width() > 991.98) {
      if ($body.hasClass("mobile-sidebar-open")) {
        closeMobileSidebar();
      }
    }
  });

  // 6. Menyembunyikan Page Loader segera setelah halaman selesai dimuat
  const hideLoader = function () {
    const $loader = $("#loading");
    if ($loader.length) {
      $loader.addClass("hidden");
    }
  };

  if (document.readyState === "complete") {
    hideLoader();
  } else {
    $(window).on("load", hideLoader);
  }
});
