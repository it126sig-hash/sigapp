(function ($, window, document) {
  'use strict';

  var config = window.SIGAPP && window.SIGAPP.authDevices;
  var list = document.getElementById('auth-devices-list');
  if (!config || !list || !$) return;

  function request(url, method, data) {
    data = data || {};
    if (method !== 'GET') data[config.csrfName] = config.csrfHash;
    var xhr = $.ajax({
      url: config.baseUrl + url,
      method: method,
      data: data,
      dataType: 'json'
    });
    xhr.done(function (response) { if (response && response.token) config.csrfHash = response.token; });
    xhr.fail(function (response) { if (response.responseJSON && response.responseJSON.token) config.csrfHash = response.responseJSON.token; });
    return xhr;
  }

  function node(tag, className, text) {
    var element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
  }

  function formatDate(value) {
    if (!value) return 'Tidak diketahui';
    var date = new Date(value.replace(' ', 'T'));
    return isNaN(date.getTime()) ? value : date.toLocaleString();
  }

  function loadDevices() {
    list.replaceChildren(node('div', 'text-muted', 'Memuat daftar perangkat…'));
    request('/api/auth/devices', 'GET').done(function (response) {
      var devices = response && response.data;
      list.replaceChildren();
      if (!Array.isArray(devices) || devices.length === 0) {
        list.appendChild(node('div', 'alert alert-light mb-0', 'Belum ada perangkat login aktif.'));
        return;
      }
      devices.forEach(function (device) {
        var row = node('div', 'border rounded p-1 mb-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center');
        var details = node('div', 'mb-1 mb-md-0');
        var title = node('div', 'font-weight-bold', device.device_label || 'Perangkat tidak dikenal');
        if (device.is_current) title.appendChild(node('span', 'badge badge-primary ml-50', 'Perangkat ini'));
        details.appendChild(title);
        details.appendChild(node('div', 'text-muted small', [device.browser, device.platform].filter(Boolean).join(' · ') || 'Browser tidak diketahui'));
        details.appendChild(node('div', 'text-muted small', 'IP: ' + (device.ip_address || 'Tidak diketahui') + ' · Terakhir aktif: ' + formatDate(device.last_seen_at)));
        row.appendChild(details);
        var button = node('button', 'btn btn-sm btn-outline-danger', device.is_current ? 'Logout perangkat ini' : 'Logout');
        button.type = 'button';
        button.addEventListener('click', function () { revokeDevice(device); });
        row.appendChild(button);
        list.appendChild(row);
      });
    }).fail(function (xhr) {
      list.replaceChildren(node('div', 'alert alert-danger mb-0', (xhr.responseJSON && xhr.responseJSON.message) || 'Daftar perangkat gagal dimuat.'));
    });
  }

  function askPassword(title, text) {
    return Swal.fire({
      title: title,
      text: text,
      input: 'password',
      inputAttributes: { autocomplete: 'current-password', maxlength: 100 },
      inputPlaceholder: 'Password saat ini',
      showCancelButton: true,
      confirmButtonText: 'Lanjutkan',
      cancelButtonText: 'Batal',
      inputValidator: function (value) { return value ? undefined : 'Password wajib diisi.'; }
    });
  }

  function revokeDevice(device) {
    askPassword('Logout perangkat?', 'Password diperlukan untuk mengonfirmasi pencabutan sesi ini.')
      .then(function (result) {
        if (!result.isConfirmed) return;
        request('/api/auth/devices/' + encodeURIComponent(device.id) + '/revoke', 'POST', { password: result.value })
          .done(function (response) {
            if (response.data && response.data.reauthenticate) {
              window.location.assign(config.baseUrl + '/login');
              return;
            }
            Swal.fire({ icon: 'success', title: 'Perangkat sudah logout', timer: 1400, showConfirmButton: false });
            loadDevices();
          }).fail(showRequestError);
      });
  }

  function showRequestError(xhr) {
    Swal.fire({ icon: 'error', title: 'Tidak dapat mencabut perangkat', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Permintaan gagal.' });
  }

  document.getElementById('btn-revoke-other-devices').addEventListener('click', function () {
    Swal.fire({
      title: 'Logout perangkat lain?',
      text: 'Perangkat ini akan tetap login. Perangkat lain harus login kembali.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, lanjutkan',
      cancelButtonText: 'Batal'
    }).then(function (confirmation) {
      if (!confirmation.isConfirmed) return;
      askPassword('Konfirmasi password', 'Masukkan password untuk logout dari perangkat lain.')
        .then(function (password) {
          if (!password.isConfirmed) return;
          request('/api/auth/devices/revoke-others', 'POST', { password: password.value })
            .done(function (response) {
              Swal.fire({ icon: 'success', title: (response.data.revoked_count || 0) + ' perangkat logout', timer: 1500, showConfirmButton: false });
              loadDevices();
            }).fail(showRequestError);
        });
    });
  });

  $('#devices-tab').on('shown.bs.tab', loadDevices);
})(window.jQuery, window, document);
