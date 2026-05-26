<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;

/**
 * KaryawanRepository
 *
 * Repository for Karyawan data access operations. Provides CRUD operations and
 * custom queries using CodeIgniter 4 Query Builder for safe database operations.
 *
 * @package App\Repositories
 */
class KaryawanRepository
{
    /**
     * Database connection instance
     *
     * @var ConnectionInterface
     */
    private ConnectionInterface $db;
    /**
     * Table name
     *
     * @var string
     */
    private string $table;
    /**
     * Primary key field name
     *
     * @var string
     */
    private string $primaryKey;

    /**
     * @param ConnectionInterface $db Database connection instance
     */
    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $this->table = 'karyawans';
        $this->primaryKey = 'id';
    }

    /**
     * Retrieve all records from the table
     *
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @return array Array of records
     */
    public function findAll(int $limit = null, int $offset = 0): array
    {
        $builder = $this->db->table($this->table);

        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Find a single record by primary key
     *
     * @param int|string $id Primary key value
     * @return array|null Record array or null if not found
     */
    public function findById(int|string $id): array|null
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);

        $result = $builder->get()->getRowArray();

        return $result ?: null;
    }

    /**
     * Find records matching the given criteria
     *
     * @param array $criteria Array of field => value pairs for WHERE conditions
     * @param int $limit Maximum number of records to return
     * @param int $offset Number of records to skip
     * @return array Array of matching records
     */
    public function findBy(array $criteria, int $limit = null, int $offset = 0): array
    {
        $builder = $this->db->table($this->table);

        foreach ($criteria as $field => $value) {
            $builder->where($field, $value);
        }

        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Find a single record matching the given criteria
     *
     * @param array $criteria Array of field => value pairs for WHERE conditions
     * @return array|null Record array or null if not found
     */
    public function findOneBy(array $criteria): array|null
    {
        $builder = $this->db->table($this->table);

        foreach ($criteria as $field => $value) {
            $builder->where($field, $value);
        }

        $result = $builder->get()->getRowArray();

        return $result ?: null;
    }

    /**
     * Insert a new record into the table
     *
     * @param array $data Data to insert
     * @return int|string|false Insert ID on success, false on failure
     */
    public function create(array $data): int|string|false
    {
        $builder = $this->db->table($this->table);

        if ($builder->insert($data)) {
            return $this->db->insertID();
        }

        return false;
    }

    /**
     * Update a record by primary key
     *
     * @param int|string $id Primary key value
     * @param array $data Data to update
     * @return bool True on success, false on failure
     */
    public function update(int|string $id, array $data): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);

        return $builder->update($data);
    }

    /**
     * Update records matching the given criteria
     *
     * @param array $criteria Array of field => value pairs for WHERE conditions
     * @param array $data Data to update
     * @return bool True on success, false on failure
     */
    public function updateBy(array $criteria, array $data): bool
    {
        $builder = $this->db->table($this->table);

        foreach ($criteria as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder->update($data);
    }

    /**
     * Delete a record by primary key
     *
     * @param int|string $id Primary key value
     * @return bool True on success, false on failure
     */
    public function delete(int|string $id): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);

        return $builder->delete();
    }

    /**
     * Delete records matching the given criteria
     *
     * @param array $criteria Array of field => value pairs for WHERE conditions
     * @return bool True on success, false on failure
     */
    public function deleteBy(array $criteria): bool
    {
        $builder = $this->db->table($this->table);

        foreach ($criteria as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder->delete();
    }

    /**
     * Count records matching the given criteria
     *
     * @param array $criteria Optional array of field => value pairs for WHERE conditions
     * @return int Number of matching records
     */
    public function count(array $criteria = []): int
    {
        $builder = $this->db->table($this->table);

        foreach ($criteria as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder->countAllResults();
    }

    /**
     * Check if a record exists by primary key
     *
     * @param int|string $id Primary key value
     * @return bool True if record exists, false otherwise
     */
    public function exists(int|string $id): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);

        return $builder->countAllResults() > 0;
    }

}
