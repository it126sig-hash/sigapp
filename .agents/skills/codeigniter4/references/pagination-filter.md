# Pagination + Filter di Repository

## Repository dengan Pagination

```php
<?php

namespace App\Repositories;

use App\Models\UserModel;

class UserRepository
{
    public function __construct(private readonly UserModel $userModel) {}

    /**
     * @return array{ data: array, pager: array }
     */
    public function paginate(array $filters = [], int $perPage = 15): array
    {
        $builder = $this->userModel->asArray();

        // Filter dinamis
        if (! empty($filters['search'])) {
            $builder->groupStart()
                    ->like('name', $filters['search'])
                    ->orLike('email', $filters['search'])
                    ->groupEnd();
        }

        if (! empty($filters['role'])) {
            $builder->where('role', $filters['role']);
        }

        if (! empty($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        // Sorting
        $sortBy  = $filters['sort_by']  ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'DESC';
        $allowedSorts = ['id', 'name', 'email', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $builder->orderBy($sortBy, $sortDir);
        }

        $data  = $builder->paginate($perPage);
        $pager = $this->userModel->pager;

        return [
            'data'  => $data,
            'pager' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page'     => $perPage,
                'total'        => $pager->getTotal(),
                'page_count'   => $pager->getPageCount(),
            ],
        ];
    }
}
```

## Service

```php
public function listUsers(array $filters = []): array
{
    $perPage = (int) ($filters['per_page'] ?? 15);
    $perPage = max(1, min($perPage, 100)); // batas 1–100

    return $this->userRepository->paginate($filters, $perPage);
}
```

## Controller

```php
public function index(): ResponseInterface
{
    $filters = $this->request->getGet([
        'search', 'role', 'is_active',
        'sort_by', 'sort_dir', 'per_page', 'page',
    ]);

    $result = $this->userService->listUsers($filters);

    return $this->respond($result);
}
```
