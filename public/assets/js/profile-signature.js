(function ($) {
    'use strict';
    const cfg = window.SIGAPP.profileSignature;
    if (!cfg) return;
    let csrf = cfg.csrfHash;
    const canvas = document.getElementById('profile-signature-canvas');
    const ctx = canvas.getContext('2d');
    let drawing = false, dirty = false;
    const endpoint = cfg.baseUrl + '/api/profile/signature';
    const resize = () => { const ratio = window.devicePixelRatio || 1; const snapshot = dirty ? canvas.toDataURL() : null; const width = canvas.parentElement.clientWidth; canvas.width = width * ratio; canvas.height = 220 * ratio; ctx.setTransform(ratio,0,0,ratio,0,0); ctx.lineWidth=2.2; ctx.lineCap='round'; ctx.strokeStyle='#172b4d'; if(snapshot){const img=new Image();img.onload=()=>ctx.drawImage(img,0,0,width,220);img.src=snapshot;} };
    const point = event => { const rect=canvas.getBoundingClientRect(); return {x:event.clientX-rect.left,y:event.clientY-rect.top}; };
    canvas.addEventListener('pointerdown', event => { drawing=true;dirty=true;canvas.setPointerCapture(event.pointerId);const p=point(event);ctx.beginPath();ctx.moveTo(p.x,p.y); });
    canvas.addEventListener('pointermove', event => { if(!drawing)return;const p=point(event);ctx.lineTo(p.x,p.y);ctx.stroke(); });
    ['pointerup','pointercancel','pointerleave'].forEach(name=>canvas.addEventListener(name,()=>drawing=false));
    async function api(method, body) { const response=await fetch(endpoint,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:body?JSON.stringify(body):undefined});const json=await response.json();if(json.token)csrf=json.token;if(!response.ok||json.success===false)throw new Error(json.messages||'Permintaan gagal.');return json.data; }
    async function load() { try { const data=await api('GET'); $('#profile-signature-delete').toggleClass('d-none',!data.has_signature); $('#profile-signature-preview').html(data.has_signature?`<img src="${endpoint}/image?v=${Date.now()}" alt="Tanda tangan profil">`:'<span class="text-muted">Belum ada tanda tangan profil.</span>'); } catch(error) { $('#profile-signature-preview').html('<span class="text-danger">Gagal memuat tanda tangan.</span>'); } }
    $('#signature-tab').on('shown.bs.tab',()=>{resize();load();});
    $('#profile-signature-clear').on('click',()=>{ctx.clearRect(0,0,canvas.width,canvas.height);dirty=false;});
    $('#profile-signature-save').on('click',async()=>{try{if(!dirty)throw new Error('Gambar tanda tangan terlebih dahulu.');await api('PUT',{password:$('#profile-signature-password').val(),signature_data:canvas.toDataURL('image/png'),[cfg.csrfName]:csrf});$('#profile-signature-password').val('');Swal.fire('Berhasil','Tanda tangan profil disimpan.','success');load();}catch(error){Swal.fire('Gagal',error.message,'error');}});
    $('#profile-signature-delete').on('click',async()=>{try{await api('DELETE',{password:$('#profile-signature-password').val(),[cfg.csrfName]:csrf});ctx.clearRect(0,0,canvas.width,canvas.height);dirty=false;$('#profile-signature-password').val('');Swal.fire('Berhasil','Tanda tangan profil dihapus.','success');load();}catch(error){Swal.fire('Gagal',error.message,'error');}});
    window.addEventListener('orientationchange',()=>setTimeout(resize,250));
})(jQuery);
