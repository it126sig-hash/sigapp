const fs = require('fs');
const path = require('path');

const jsPath = path.resolve(__dirname, '../../public/assets/js/siteplan/mkdt.js');
const viewPath = path.resolve(__dirname, '../../app/Views/siteplan/modal/mkdt-isi_data_konsumen.php');
const statusViewPath = path.resolve(__dirname, '../../app/Views/siteplan/mkdt.php');
const financeJsPath = path.resolve(__dirname, '../../public/assets/js/siteplan/keuangan.js');

describe('MKDT consumer replacement contract', () => {
    const source = fs.readFileSync(jsPath, 'utf8');
    const historyView = fs.readFileSync(viewPath, 'utf8');
    const statusView = fs.readFileSync(statusViewPath, 'utf8');
    const financeSource = fs.readFileSync(financeJsPath, 'utf8');

    test('starts from Ubah Status with confirmation and no signed-SPPTB prerequisite', () => {
        expect(statusView).toContain('id="btn-pindah-konsumen"');
        expect(source).toContain('$("#btn-pindah-konsumen").toggle(Boolean(r && r.id_konsumen));');
        expect(source).toContain('title: "Pindah konsumen?"');
        expect(source).not.toContain('Kamu harus mengunggah file SPPTB');
        expect(historyView).not.toContain('id="btn-ganti_nama"');
    });

    test('clears consumer identity and documents while keeping SPPTB read only', () => {
        expect(source).toContain('$(consumerIdentityFields.join(",")).val("").trigger("change")');
        expect(source).toContain('$("#idk-kode_referal").val(null).trigger("change")');
        expect(source).toContain('#file_ktp, #file_npwp, #file_data_diri, #file_spptb, #file_surat_kuasa');
        expect(source).toContain('$("#st-mkdt-no_spptb").prop("readonly", true)');
        expect(source).toContain('.addClass("replacement-locked bg-light")');
    });

    test('uses dedicated endpoint only in replacement mode and clears state on close', () => {
        expect(source).toContain('consumerReplacementState.active ? "api/transaksi/konsumen/ganti" : "api/transaksi/simpan"');
        expect(source).toContain('fd.set("id_konsumen_lama", consumerReplacementState.oldConsumerId)');
        expect(source).toContain('resetConsumerReplacementHistory();');
        expect(source).toContain('resetConsumerReplacementMode();');
    });

    test('loads and reveals ganti nama history for MKDT, including entries without SPPTB files', () => {
        expect(source).toContain('api/mkdt/consumer-replacement-history');
        expect(source).toContain('renderConsumerReplacementHistory(response.history || [])');
        expect(source).toContain('$section.removeClass("d-none")');
        expect(source).toContain('Belum ada file SPPTB');
        expect(historyView).toContain('id="idk-riwayat-ganti-nama-section"');
        expect(financeSource).not.toContain('function getRiwayatGantinama()');
    });
});
