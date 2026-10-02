(function ($, window, document) {
  'use strict';

  var config = window.SIGAPP && window.SIGAPP.adminAuthDevices;
  var list = document.getElementById('admin-auth-devices-list');
  if (!config || !list || !$) return;
  var targetUserId = 0;

  function api(path, method, data) {
    data = data || {};
    if (method !== 'GET') data[config.csrfName] = config.csrfHash;
    var xhr = $.ajax({ url: config.baseUrl + path, method: method, data: data, dataType: 'json' });
    xhr.done(function (response) { if (response && response.token) config.csrfHash = response.token; });
    xhr.fail(function (response) { if (response.responseJSON && response.responseJSON.token) config.csrfHash = response.responseJSON.token; });
    return xhr;
  }

  function element(tag, className, text) {
    var item = document.createElement(tag);
    if (className) item.className = className;
    if (text !== undefined) item.textContent = text;
    return item;
  }

  function showError(xhr) {
    Swal.fire({ icon: 'error', title: 'Permintaan gagal', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan.' });
  }

  function load() {
    if (!targetUserId) return;
    list.replaceChildren(element('div', 'text-muted', 'Memuat perangkat…'));
    api('/api/admin/users/' + targetUserId + '/devices', 'GET').done(function (response) {
      var devices = response && response.data;
      list.replaceChildren();
      if (!Array.isArray(devices) || devices.length === 0) {
        list.appendChild(element('div', 'alert alert-light mb-0', 'Tidak ada sesi perangkat aktif.'));
        return;
      }
      devices.forEach(function (device) {
        var row = element('div', 'border rounded p-1 mb-1 d-flex justify-content-between align-items-center');
        var info = element('div', 'mr-1');
        info.appendChild(element('div', 'font-weight-bold', device.device_label || 'Perangkat tidak dikenal'));
        info.appendChild(element('div', 'text-muted small', [device.browser, device.platform].filter(Boolean).join(' · ') || 'Browser tidak diketahui'));
        info.appendChild(element('div', 'text-muted small', 'IP: ' + (device.ip_address || 'Tidak diketahui') + ' · Aktif: ' + (device.last_seen_at || 'Tidak diketahui')));
        row.appendChild(info);
        var revoke = element('button', 'btn btn-sm btn-outline-danger', 'Logout');
        revoke.type = 'button';
        revoke.addEventListener('click', function () { revokeDevice(device.id); });
        row.appendChild(revoke);
        list.appendChild(row);
      });
    }).fail(showError);
  }

  function askReason(title, prompt, callback) {
    Swal.fire({
      title: title,
      text: prompt,
      input: 'textarea',
      inputAttributes: { maxlength: 255, 'aria-label': 'Alasan audit' },
      inputPlaceholder: 'Alasan pencabutan (wajib untuk audit)',
      showCancelButton: true,
      confirmButtonText: 'Cabut sesi',
      cancelButtonText: 'Batal',
      inputValidator: function (value) { return value && value.trim() ? undefined : 'Alasan wajib diisi.'; }
    }).then(function (result) {
      if (result.isConfirmed) callback(result.value.trim());
    });
  }

  function revokeDevice(deviceId) {
    askReason('Logout perangkat ini?', 'Pengguna harus login kembali di perangkat tersebut.', function (reason) {
      api('/api/admin/users/' + targetUserId + '/devices/' + encodeURIComponent(deviceId) + '/revoke', 'POST', { reason: reason })
        .done(function () { load(); }).fail(showError);
    });
  }

  window.manageDevices = function (userId) {
    targetUserId = parseInt(userId, 10) || 0;
    document.getElementById('auth-devices-user').textContent = 'User ID: ' + targetUserId;
    $('#auth-devices-modal').modal('show');
    load();
  };

  document.getElementById('admin-revoke-all-devices').addEventListener('click', function () {
    askReason('Logout semua perangkat?', 'Semua sesi aktif pengguna akan dicabut dan harus login kembali.', function (reason) {
      api('/api/admin/users/' + targetUserId + '/devices/revoke-all', 'POST', { reason: reason })
        .done(function (response) {
          Swal.fire({ icon: 'success', title: (response.data.revoked_count || 0) + ' sesi dicabut', timer: 1500, showConfirmButton: false });
          load();
        }).fail(showError);
    });
  });
})(window.jQuery, window, document);
