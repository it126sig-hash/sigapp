(function () {
  "use strict";

  var config = window.SIGAPP_PWA || {};
  var deferredPrompt = null;
  var installAction = null;
  var isStandalone = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
  var isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent || "");
  var isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(window.navigator.userAgent || "");

  function refreshFeather() {
    if (window.feather && typeof window.feather.replace === "function") {
      window.feather.replace({ width: 14, height: 14 });
    }
  }

  function showAction(label, handler) {
    installAction = installAction || document.getElementById("sigapp-pwa-install-action");
    if (!installAction) {
      return;
    }

    installAction.classList.remove("d-none");
    installAction.innerHTML = '<i class="mr-50" data-feather="download"></i> ' + label;
    installAction.onclick = function (event) {
      event.preventDefault();
      handler();
    };
    refreshFeather();
  }

  function hideAction() {
    installAction = installAction || document.getElementById("sigapp-pwa-install-action");
    if (!installAction) {
      return;
    }

    installAction.classList.add("d-none");
    installAction.onclick = null;
  }

  function showIosHint() {
    var message = "Di Safari iOS, buka tombol Share lalu pilih Add to Home Screen.";
    if (window.Swal && typeof window.Swal.fire === "function") {
      window.Swal.fire({
        icon: "info",
        title: "Install SIGAPP",
        text: message,
        confirmButtonColor: "#2057a3",
      });
      return;
    }

    window.alert(message);
  }

  if ("serviceWorker" in navigator && window.isSecureContext && config.serviceWorkerUrl) {
    window.addEventListener("load", function () {
      navigator.serviceWorker.register(config.serviceWorkerUrl, {
        scope: config.serviceWorkerScope || "./",
      }).catch(function () {
        // Registration failures are non-blocking; the app must keep working normally.
      });
    });
  }

  window.addEventListener("beforeinstallprompt", function (event) {
    event.preventDefault();
    deferredPrompt = event;

    if (isStandalone) {
      return;
    }

    showAction("Install SIGAPP", function () {
      if (!deferredPrompt) {
        return;
      }

      deferredPrompt.prompt();
      deferredPrompt.userChoice.finally(function () {
        deferredPrompt = null;
        hideAction();
      });
    });
  });

  window.addEventListener("appinstalled", function () {
    deferredPrompt = null;
    hideAction();
  });

  if (!isStandalone && isIos && isSafari) {
    showAction("Tambah ke Home Screen", showIosHint);
  }
})();
