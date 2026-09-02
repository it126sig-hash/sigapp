<?php

namespace App\Data;

class NotificationAudience
{
    public function __construct(
        private readonly array $groupIds = [],
        private readonly array $userIds = [],
        private readonly bool $global = false
    ) {
    }

    public static function fromLegacyTarget($target): self
    {
        if (is_array($target)) {
            $groupIds = [];
            foreach ($target as $item) {
                $groupIds = array_merge($groupIds, self::parseGroupTarget($item));
            }

            return new self(self::uniqueInts($groupIds));
        }

        $target = trim((string) $target);
        if ($target === '0') {
            return self::global();
        }

        return new self(self::parseGroupTarget($target));
    }

    public static function forUser(int $userId): self
    {
        return new self([], [$userId]);
    }

    public static function forUsers(array $userIds): self
    {
        return new self([], self::uniqueInts($userIds));
    }

    public static function forGroups(array $groupIds): self
    {
        return new self(self::uniqueInts($groupIds));
    }

    public static function global(): self
    {
        return new self([], [], true);
    }

    public function groupIds(): array
    {
        return $this->groupIds;
    }

    public function userIds(): array
    {
        return $this->userIds;
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function legacyGroupTarget(): ?string
    {
        if ($this->global) {
            return '0';
        }

        if ($this->groupIds === []) {
            return null;
        }

        return implode(';', $this->groupIds);
    }

    public function legacyUserId(): ?int
    {
        if ($this->global || $this->groupIds !== [] || count($this->userIds) !== 1) {
            return null;
        }

        return (int) $this->userIds[0];
    }

    private static function parseGroupTarget($target): array
    {
        $parts = preg_split('/[;,]+/', (string) $target) ?: [];
        return self::uniqueInts($parts);
    }

    private static function uniqueInts(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $id = (int) $value;
            if ($id > 0) {
                $result[$id] = $id;
            }
        }

        return array_values($result);
    }
}
