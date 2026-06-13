(function ($) {
  "use strict";

  var defaultProjectLogo =
    (window.SIGAPP && window.SIGAPP.activeProyekLogoUrl) ||
    base_url + "app-assets/images/ico/apple-icon-120.png";

  var projectSelectionExemptPaths = [
    "profil",
    "aksesproyek",
    "menu-setting",
    "login",
    "logout",
  ];

  window.activeProyekId = function () {
    if (window.SIGAPP && window.SIGAPP.activeProyekId) {
      return window.SIGAPP.activeProyekId;
    }

    return null;
  };

  function refreshFeatherIcons(scope) {
    if (typeof feather === "undefined") {
      return;
    }

    feather.replace({
      width: 14,
      height: 14,
    });

    if (scope) {
      $(scope).find("[data-feather]").each(function () {
        var icon = $(this).data("feather");
        if (!icon) {
          return;
        }
        var svg = feather.icons[icon] ? feather.icons[icon].toSvg() : "";
        if (svg) {
          $(this).replaceWith(svg);
        }
      });
    }
  }

  function setSwitchingState(isSwitching) {
    $(".navbar-project-item, .project-select-card").prop(
      "disabled",
      isSwitching,
    );
    $("#navbar-project-switcher").prop("disabled", isSwitching);
  }

  function switchActiveProyek(idProyek, options) {
    options = options || {};

    if (!idProyek) {
      return;
    }

    if (
      !options.force &&
      window.SIGAPP &&
      String(window.SIGAPP.activeProyekId) === String(idProyek)
    ) {
      return;
    }

    setSwitchingState(true);

    $.ajax({
      url: base_url + "proyek/setActive",
      method: "POST",
      dataType: "json",
      data: {
        [csrfName]: csrfHash,
        id_proyek: idProyek,
      },
    })
      .done(function (response) {
        if (response && response.token) {
          csrfHash = response.token;
        }

        if (!response || !response.success) {
          setSwitchingState(false);
          if (typeof showToast === "function") {
            showToast(
              "error",
              (response && response.message) || "Gagal mengganti proyek.",
            );
          }
          return;
        }

        window.location.reload();
      })
      .fail(function () {
        setSwitchingState(false);
        if (typeof showToast === "function") {
          showToast("error", "Gagal mengganti proyek.");
        }
      });
  }

  function isProjectSelectionExempt() {
    var pathname = (window.location.pathname || "").toLowerCase();

    return projectSelectionExemptPaths.some(function (exempt) {
      return pathname.indexOf("/" + exempt) !== -1;
    });
  }

  function ensureProjectSelectionModal() {
    if ($("#modal-pilih-proyek-awal").length) {
      return $("#modal-pilih-proyek-awal");
    }

    var $modal = $(`
      <div class="modal fade project-select-modal" id="modal-pilih-proyek-awal" tabindex="-1" role="dialog" aria-labelledby="modalPilihProyekLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title mb-25" id="modalPilihProyekLabel">Pilih Proyek</h5>
                <p class="text-muted mb-0 font-small-3">Pilih proyek yang ingin Anda kerjakan untuk melanjutkan.</p>
              </div>
            </div>
            <div class="modal-body">
              <div class="project-select-grid" id="project-select-grid"></div>
            </div>
          </div>
        </div>
      </div>
    `);

    $("body").append($modal);
    return $modal;
  }

  function renderProjectSelectionModal() {
    if (
      !window.SIGAPP ||
      !window.SIGAPP.needsProjectSelection ||
      isProjectSelectionExempt()
    ) {
      return;
    }

    var projects = window.SIGAPP.accessibleProyek || [];
    if (!projects.length) {
      return;
    }

    var $modal = ensureProjectSelectionModal();
    var $grid = $modal.find("#project-select-grid");
    var html = "";

    $.each(projects, function (_, project) {
      var logoUrl = project.logo_access_url || defaultProjectLogo;
      html += `
        <button type="button" class="project-select-card" data-id-proyek="${project.id_proyek}">
          <img src="${logoUrl}" alt="" class="project-select-card-logo">
          <span class="project-select-card-name">${$("<div>").text(project.nama_proyek || "-").html()}</span>
        </button>
      `;
    });

    $grid.html(html);
    $modal.modal("show");
  }

  $(document).on("click", ".navbar-project-item", function (event) {
    event.preventDefault();

    var $item = $(this);
    if ($item.hasClass("active")) {
      return;
    }

    switchActiveProyek($item.data("id-proyek"));
  });

  $(document).on("click", ".project-select-card", function (event) {
    event.preventDefault();
    switchActiveProyek($(this).data("id-proyek"), { force: true });
  });

  $(function () {
    refreshFeatherIcons(document);
    renderProjectSelectionModal();
  });
})(jQuery);
