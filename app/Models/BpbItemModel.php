<?php
namespace App\Models;
use CodeIgniter\Model;
class BpbItemModel extends Model
{
    protected $table = 'bpb_items';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['bpb_id', 'item_order', 'nama_barang', 'jumlah', 'satuan', 'keterangan'];
}
