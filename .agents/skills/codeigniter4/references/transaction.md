# Database Transaction di Service

Gunakan transaction ketika satu operasi bisnis menyentuh lebih dari satu tabel.

## Pattern Dasar

```php
<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Exceptions\ServiceException;
use CodeIgniter\Database\BaseConnection;

class OrderService
{
    private BaseConnection $db;

    public function __construct(
        private readonly OrderModel     $orderModel,
        private readonly OrderItemModel $orderItemModel,
        private readonly ProductModel   $productModel,
    ) {
        $this->db = \Config\Database::connect();
    }

    public function createOrder(int $userId, array $items): array
    {
        $this->db->transStart();

        try {
            // 1. Hitung total
            $total = 0;
            foreach ($items as $item) {
                $product = $this->productModel->find($item['product_id']);
                if (! $product) {
                    throw ServiceException::notFound('Product', $item['product_id']);
                }
                if ($product['stock'] < $item['qty']) {
                    throw new ServiceException("Stok {$product['name']} tidak mencukupi", 422);
                }
                $total += $product['price'] * $item['qty'];
            }

            // 2. Buat order
            $orderId = $this->orderModel->insert([
                'user_id' => $userId,
                'total'   => $total,
                'status'  => 'pending',
            ], true);

            // 3. Insert order items & kurangi stok
            foreach ($items as $item) {
                $product = $this->productModel->find($item['product_id']);

                $this->orderItemModel->insert([
                    'order_id'   => $orderId,
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'price'      => $product['price'],
                ]);

                $this->productModel->update($item['product_id'], [
                    'stock' => $product['stock'] - $item['qty'],
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new ServiceException('Order gagal disimpan');
            }

            return $this->orderModel->find($orderId);

        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e; // re-throw agar controller/exception handler menangkap
        }
    }
}
```

## Tips Transaction

- Selalu `transRollback()` di catch, lalu re-throw exception
- Jangan tangkap exception bisnis di dalam transaction tanpa rollback
- Gunakan `transStatus()` untuk cek apakah transaction sukses
