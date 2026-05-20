# RepositoryGenerator Usage Examples

This document provides practical examples of using the RepositoryGenerator class to create repository classes for CodeIgniter 4 applications.

## Basic Usage

### Example 1: Simple Repository

```php
<?php

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\QueryAnalyzer;
use App\Libraries\Refactor\Generation\RepositoryGenerator;

// Create dependencies
$codeGen = new CodeGenerator();
$queryAnalyzer = new QueryAnalyzer();
$repoGen = new RepositoryGenerator($codeGen, $queryAnalyzer);

// Generate a basic User repository
$code = $repoGen->generate([
    'modelName' => 'User',
    'tableName' => 'users',
    'primaryKey' => 'id',
]);

// Save to file
file_put_contents(APPPATH . 'Repositories/UserRepository.php', $code);
```

**Generated Output:**
```php
<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;

/**
 * UserRepository
 *
 * Repository for User data access operations. Provides CRUD operations and custom
 * queries using CodeIgniter 4 Query Builder for safe database operations.
 *
 * @package App\Repositories
 */
class UserRepository
{
    private ConnectionInterface $db;
    private string $table;
    private string $primaryKey;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $this->table = 'users';
        $this->primaryKey = 'id';
    }

    // ... 11 CRUD methods (findAll, findById, create, update, delete, etc.)
}
```

### Example 2: Repository with Custom Namespace

```php
// Generate repository in a module
$code = $repoGen->generate([
    'modelName' => 'Product',
    'tableName' => 'products',
    'primaryKey' => 'product_id',
    'namespace' => 'App\Modules\Shop\Repositories',
]);

file_put_contents(APPPATH . 'Modules/Shop/Repositories/ProductRepository.php', $code);
```

### Example 3: Repository with Custom Queries

```php
// Generate repository with custom query methods
$code = $repoGen->generate([
    'modelName' => 'User',
    'tableName' => 'users',
    'queries' => [
        [
            'methodName' => 'findActiveUsers',
            'query' => "SELECT * FROM users WHERE status = 'active' ORDER BY created_at DESC",
            'description' => 'Find all active users ordered by creation date',
            'params' => [],
            'returnType' => 'array',
        ],
        [
            'methodName' => 'findByEmail',
            'query' => "SELECT * FROM users WHERE email = ?",
            'description' => 'Find a user by email address',
            'params' => [
                [
                    'name' => 'email',
                    'type' => 'string',
                    'description' => 'Email address to search for',
                ],
            ],
            'returnType' => 'array|null',
        ],
        [
            'methodName' => 'countActiveUsers',
            'query' => "SELECT COUNT(*) FROM users WHERE status = 'active'",
            'description' => 'Count the number of active users',
            'params' => [],
            'returnType' => 'int',
        ],
    ],
]);
```

**Generated Custom Methods:**
```php
/**
 * Find all active users ordered by creation date
 *
 * @return array
 */
public function findActiveUsers(): array
{
    $builder = $this->db->table($this->table);

    // Query Builder implementation
    $builder->where("status = 'active'");
    $builder->orderBy('created_at DESC');

    return $builder->get()->getResultArray();
}

/**
 * Find a user by email address
 *
 * @param string $email Email address to search for
 * @return array|null
 */
public function findByEmail(string $email): array|null
{
    $builder = $this->db->table($this->table);

    // Parameter binding for SQL injection prevention
    $bindings = ['email' => $email];

    // Query Builder implementation
    $builder->where("email = ?");

    $result = $builder->get()->getRowArray();
    return $result ?: null;
}

/**
 * Count the number of active users
 *
 * @return int
 */
public function countActiveUsers(): int
{
    $builder = $this->db->table($this->table);

    // Query Builder implementation
    $builder->where("status = 'active'");

    return $builder->countAllResults();
}
```

## Using Generated Repositories

### In a Service Class

```php
<?php

namespace App\Services;

use App\Repositories\UserRepository;
use CodeIgniter\Database\ConnectionInterface;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(ConnectionInterface $db)
    {
        $this->userRepository = new UserRepository($db);
    }

    public function getAllUsers(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->userRepository->findAll($perPage, $offset);
    }

    public function getUserById(int $id): ?array
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): int|string|false
    {
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function getActiveUsers(): array
    {
        return $this->userRepository->findActiveUsers();
    }

    public function findUserByEmail(string $email): ?array
    {
        return $this->userRepository->findByEmail($email);
    }
}
```

### In a Controller

```php
<?php

namespace App\Controllers;

use App\Services\UserService;
use CodeIgniter\Database\ConnectionInterface;

class UserController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {
        $db = \Config\Database::connect();
        $this->userService = new UserService($db);
    }

    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $users = $this->userService->getAllUsers($page);

        return view('users/index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('users/show', ['user' => $user]);
    }

    public function create()
    {
        $data = $this->request->getPost();
        $userId = $this->userService->createUser($data);

        if ($userId) {
            return redirect()->to('/users/' . $userId)->with('success', 'User created successfully');
        }

        return redirect()->back()->with('error', 'Failed to create user');
    }
}
```

## Standard CRUD Methods

All generated repositories include these 11 standard methods:

### 1. findAll(int $limit = null, int $offset = 0): array
Retrieve all records with optional pagination.

```php
// Get all users
$users = $userRepo->findAll();

// Get first 10 users
$users = $userRepo->findAll(10);

// Get users 11-20 (pagination)
$users = $userRepo->findAll(10, 10);
```

### 2. findById(int|string $id): array|null
Find a single record by primary key.

```php
$user = $userRepo->findById(1);
if ($user) {
    echo $user['name'];
}
```

### 3. findBy(array $criteria, int $limit = null, int $offset = 0): array
Find records matching criteria.

```php
// Find all active users
$activeUsers = $userRepo->findBy(['status' => 'active']);

// Find first 5 admin users
$admins = $userRepo->findBy(['role' => 'admin'], 5);
```

### 4. findOneBy(array $criteria): array|null
Find a single record matching criteria.

```php
$user = $userRepo->findOneBy(['email' => 'john@example.com']);
```

### 5. create(array $data): int|string|false
Insert a new record.

```php
$userId = $userRepo->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active',
]);
```

### 6. update(int|string $id, array $data): bool
Update a record by primary key.

```php
$success = $userRepo->update(1, [
    'name' => 'Jane Doe',
    'status' => 'inactive',
]);
```

### 7. updateBy(array $criteria, array $data): bool
Update records matching criteria.

```php
// Deactivate all users with old email domain
$success = $userRepo->updateBy(
    ['email' => 'LIKE %@oldomain.com'],
    ['status' => 'inactive']
);
```

### 8. delete(int|string $id): bool
Delete a record by primary key.

```php
$success = $userRepo->delete(1);
```

### 9. deleteBy(array $criteria): bool
Delete records matching criteria.

```php
// Delete all inactive users
$success = $userRepo->deleteBy(['status' => 'inactive']);
```

### 10. count(array $criteria = []): int
Count records matching criteria.

```php
// Count all users
$totalUsers = $userRepo->count();

// Count active users
$activeCount = $userRepo->count(['status' => 'active']);
```

### 11. exists(int|string $id): bool
Check if a record exists by primary key.

```php
if ($userRepo->exists(1)) {
    echo "User exists";
}
```

## Advanced Usage

### Generating Multiple Repositories

```php
$models = [
    ['modelName' => 'User', 'tableName' => 'users'],
    ['modelName' => 'Product', 'tableName' => 'products'],
    ['modelName' => 'Order', 'tableName' => 'orders'],
    ['modelName' => 'Category', 'tableName' => 'categories'],
];

foreach ($models as $model) {
    $code = $repoGen->generate($model);
    $filename = APPPATH . 'Repositories/' . $model['modelName'] . 'Repository.php';
    file_put_contents($filename, $code);
    echo "Generated: {$filename}\n";
}
```

### Converting Existing Raw Queries

```php
// You have an existing model with raw SQL
$rawQuery = "SELECT u.*, p.name as profile_name 
             FROM users u 
             LEFT JOIN profiles p ON u.id = p.user_id 
             WHERE u.status = 'active' 
             ORDER BY u.created_at DESC 
             LIMIT 10";

// Generate a repository method for it
$code = $repoGen->generate([
    'modelName' => 'User',
    'tableName' => 'users',
    'queries' => [
        [
            'methodName' => 'findActiveUsersWithProfiles',
            'query' => $rawQuery,
            'description' => 'Find active users with their profile information',
            'params' => [],
            'returnType' => 'array',
        ],
    ],
]);
```

## Benefits

### Security
- **SQL Injection Prevention**: All queries use Query Builder with parameter binding
- **No Raw SQL**: Eliminates dangerous string concatenation
- **Type Safety**: Type hints prevent type-related vulnerabilities

### Maintainability
- **Centralized Data Access**: All database operations in one place
- **Consistent API**: Same methods across all repositories
- **Easy Testing**: Repositories can be mocked for unit tests

### Code Quality
- **PSR-12 Compliant**: Follows PHP coding standards
- **Type Hints**: Full type safety with PHP 8+ union types
- **PHPDoc Comments**: Complete documentation for IDE support

### Productivity
- **Auto-generated CRUD**: No need to write repetitive code
- **Custom Queries**: Easy conversion of raw SQL to safe Query Builder
- **Reusable**: Generated repositories work across the application

## Best Practices

1. **Always use repositories in services**, not directly in controllers
2. **Keep business logic in services**, repositories only handle data access
3. **Use type hints** for better IDE support and type safety
4. **Add custom methods** for complex queries instead of using raw SQL
5. **Test repositories** with integration tests using a test database
6. **Use dependency injection** to pass database connection to repositories

## Conclusion

The RepositoryGenerator provides a powerful, secure, and maintainable way to create data access layers for CodeIgniter 4 applications. By automatically generating type-safe, well-documented repository classes with CRUD operations and custom query methods, it significantly reduces development time while improving code quality and security.
