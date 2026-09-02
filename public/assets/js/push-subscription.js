(function () {
  const state = {
    ready: false,
    supported: "serviceWorker" in navigator && "PushManager" in window && "Notification" in window,
  };

  function actionButton() {
    return document.getElementById("sigapp-push-action");
  }

  function setAction(label, disabled = false, visible = true) {
    const button = actionButton();
    if (!button) {
      return;
    }

    button.classList.toggle("d-none", !visible);
    button.classList.toggle("disabled", disabled);
    button.setAttribute("aria-disabled", disabled ? "true" : "false");

    const labelEl = button.querySelector("[data-push-label]");
    if (labelEl) {
      labelEl.textContent = label;
    } else {
      button.textContent = label;
    }
  }

  function updateToken(response) {
    if (!response || !response.token || typeof csrfName === "undefined") {
      return;
    }

    csrfHash = response.token;
    document.querySelectorAll('input[name="' + csrfName + '"]').forEach((input) => {
      input.value = csrfHash;
    });
  }

  function urlB64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  function arrayBufferToBase64(buffer) {
    return window.btoa(String.fromCharCode.apply(null, new Uint8Array(buffer)));
  }

  async function postJson(url, payload) {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload),
    });

    const body = await response.json().catch(() => ({}));
    updateToken(body);

    if (!response.ok) {
      throw new Error(body.messages || body.error || "Request push notification gagal.");
    }

    return body;
  }

  async function currentRegistration() {
    if (window.SIGAPP_PWA && window.SIGAPP_PWA.serviceWorkerUrl) {
      await navigator.serviceWorker.register(window.SIGAPP_PWA.serviceWorkerUrl, {
        scope: window.SIGAPP_PWA.serviceWorkerScope || base_url,
      });
    }

    return navigator.serviceWorker.ready;
  }

  async function subscribePush(options = {}) {
    if (!state.supported) {
      setAction("Browser tidak mendukung", true, true);
      return false;
    }

    if (typeof VAPID_PUBLIC_KEY === "undefined" || !VAPID_PUBLIC_KEY) {
      setAction("VAPID belum siap", true, true);
      return false;
    }

    if (Notification.permission === "denied") {
      setAction("Notifikasi diblokir", true, true);
      return false;
    }

    if (Notification.permission !== "granted") {
      if (!options.askPermission) {
        setAction("Aktifkan Notifikasi", false, true);
        return false;
      }

      const permission = await Notification.requestPermission();
      if (permission !== "granted") {
        setAction(permission === "denied" ? "Notifikasi diblokir" : "Aktifkan Notifikasi", permission === "denied", true);
        return false;
      }
    }

    setAction("Mengaktifkan...", true, true);

    const reg = await currentRegistration();
    const appServerKey = urlB64ToUint8Array(VAPID_PUBLIC_KEY);
    let sub = await reg.pushManager.getSubscription();

    if (sub && sub.options.applicationServerKey) {
      const currentKey = arrayBufferToBase64(sub.options.applicationServerKey);
      const expectedKey = arrayBufferToBase64(appServerKey);
      if (currentKey !== expectedKey) {
        await sub.unsubscribe();
        sub = null;
      }
    }

    if (!sub) {
      sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appServerKey,
      });
    }

    await postJson(base_url + "/api/notif/push/subscribe", sub.toJSON());
    state.ready = true;
    setAction("Notifikasi Aktif", true, true);

    return true;
  }

  async function unsubscribePush() {
    if (!state.supported) {
      return false;
    }

    const reg = await currentRegistration();
    const sub = await reg.pushManager.getSubscription();
    if (!sub) {
      setAction("Aktifkan Notifikasi", false, true);
      return true;
    }

    await postJson(base_url + "/api/notif/push/unsubscribe", {
      endpoint: sub.endpoint,
    });
    await sub.unsubscribe();
    state.ready = false;
    setAction("Aktifkan Notifikasi", false, true);

    return true;
  }

  async function refreshStatus() {
    if (!state.supported) {
      setAction("Browser tidak mendukung", true, true);
      return;
    }

    if (Notification.permission === "granted") {
      try {
        await subscribePush({ askPermission: false });
      } catch (error) {
        console.error("Gagal sinkronisasi subscription push", error);
        setAction("Aktifkan Notifikasi", false, true);
      }
      return;
    }

    setAction(Notification.permission === "denied" ? "Notifikasi diblokir" : "Aktifkan Notifikasi", Notification.permission === "denied", true);
  }

  window.SIGAPP_PUSH = {
    subscribe: () => subscribePush({ askPermission: true }),
    unsubscribe: unsubscribePush,
    refreshStatus,
  };

  window.addEventListener("load", () => {
    refreshStatus();
  });

  document.addEventListener("click", (event) => {
    const button = event.target.closest("#sigapp-push-action");
    if (!button || button.classList.contains("disabled")) {
      return;
    }

    event.preventDefault();
    subscribePush({ askPermission: true }).catch((error) => {
      console.error("Error subscribing to push notifications", error);
      setAction("Aktifkan Notifikasi", false, true);
      if (typeof showToast === "function") {
        showToast(error.message || "Gagal mengaktifkan notifikasi", "warning");
      }
    });
  });
})();
