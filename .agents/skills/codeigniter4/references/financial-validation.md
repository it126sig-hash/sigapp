# Validasi Finansial di Backend

Semua angka finansial WAJIB dihitung ulang dari DB di Service layer.
Jangan pernah percaya nilai nominal, total, atau sisa dari request frontend.

## Pola Dasar

```php
// ❌ SALAH — percaya angka dari frontend
$nominal  = $request->getPost('nominal');
$total    = $request->getPost('total_tagihan');
$is_lunas = $request->getPost('is_lunas');

// ✅ BENAR — hitung dari DB
$totalTagihan = $this->keuanganModel->getTotalTagihan($id_mkdt);  // SUM dari DB
$sudahBayar   = $this->pembayaranModel->getTotalSudahBayar($id_mkdt);
$sisa         = $totalTagihan - $sudahBayar;
$isLunas      = $sisa <= 0; // ditentukan backend
```

## Helper Methods di Model

```php
// KeuanganModel.php
public function getTotalTagihan(int $id_mkdt): float
{
    return (float) $this->selectSum('nominal')
        ->where('id_mkdt', $id_mkdt)
        ->where('deleted_at IS NULL')
        ->get()->getRow()->nominal ?? 0;
}

public function getTagihanTurunKpr(int $id_mkdt): ?array
{
    return $this->where('id_mkdt', $id_mkdt)
        ->where('berita_acara', 'Turun KPR')
        ->first();
}

public function setLunas(int $id_mkdt): void
{
    // Update di tabel mkdt, bukan dari input user
    model('MkdtModel')->update($id_mkdt, ['is_lunas' => 1]);
}

// PembayaranModel.php
public function getTotalSudahBayar(int $id_mkdt): float
{
    return (float) $this->selectSum('nominal')
        ->where('id_mkdt', $id_mkdt)
        ->where('payment_type !=', 'Booking')
        ->get()->getRow()->nominal ?? 0;
}
```

## Validasi Diskon

```php
public function validasiDiskon(int $id_mkdt, float $diskon): void
{
    $mkdt = $this->mkdtModel->find($id_mkdt);
    $maxDiskon = $mkdt['harga_jual'] * 0.10; // max 10% dari harga jual

    if ($diskon > $maxDiskon) {
        throw new ServiceException(
            "Diskon Rp.{$diskon} melebihi batas maksimal Rp.{$maxDiskon}", 422
        );
    }
}
```

## Validasi Turun KPR

```php
public function validasiTurunKpr(int $id_mkdt, float $nominalFromFrontend): float
{
    // Selalu hitung dari DB — abaikan $nominalFromFrontend
    $mkdt     = $this->mkdtModel->find($id_mkdt);
    $turunKpr = (float) $mkdt['harga_kpr'] - (float) $mkdt['harga_kpr_acc'];

    if ($turunKpr <= 0) {
        throw new ServiceException('Tidak ada selisih KPR', 422);
    }

    return $turunKpr; // return nilai yang sudah tervalidasi
}
```

## Pembersihan Input Numerik

Buat helper untuk membersihkan format angka dari frontend (misal: "1,500,000"):

```php
// app/Helpers/numeric_helper.php
function cleanNumeric(mixed $value): float
{
    if (is_null($value) || $value === '') return 0.0;
    // Hapus semua karakter selain digit, titik, dan minus
    $clean = preg_replace('/[^\d.\-]/', '', str_replace(',', '', (string) $value));
    return (float) $clean;
}
```

Gunakan di Service:
```php
$nominal = cleanNumeric($data['nominal']); // "1,500,000" → 1500000.0
```
