<?php

namespace App\Services\Bpb;

final class BpbWorkflow
{
    public const DRAFT = 'draft';
    public const WAITING_CC = 'waiting_cc';
    public const WAITING_APPROVER = 'waiting_approver';
    public const APPROVED = 'approved';
    public const PROCESSED = 'processed';
    public const DISBURSED = 'disbursed';
    public const PENDING = 'pending';
    public const PURCHASED = 'purchased';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    public static function labels(): array
    {
        return [
            self::DRAFT => 'Draft', self::WAITING_CC => 'Diajukan - Menunggu CC',
            self::WAITING_APPROVER => 'Diajukan - Menunggu Mengetahui', self::APPROVED => 'Disetujui',
            self::PROCESSED => 'Diproses', self::DISBURSED => 'Sudah Cair', self::PENDING => 'Pending',
            self::PURCHASED => 'Sudah Dibeli', self::REJECTED => 'Ditolak', self::CANCELLED => 'Dibatalkan',
        ];
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? $status;
    }

    public static function approvalStatus(bool $hasCc): string
    {
        return $hasCc ? self::WAITING_CC : self::WAITING_APPROVER;
    }

    public static function afterSignature(string $status): ?string
    {
        return match ($status) {
            self::WAITING_CC => self::WAITING_APPROVER,
            self::WAITING_APPROVER => self::APPROVED,
            default => null,
        };
    }

    public static function canOperationalTransition(string $from, string $to): bool
    {
        return in_array($to, match ($from) {
            self::APPROVED => [self::PROCESSED],
            self::PROCESSED => [self::DISBURSED],
            self::DISBURSED => [self::PENDING, self::PURCHASED],
            self::PENDING => [self::PURCHASED],
            default => [],
        }, true);
    }

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::REJECTED, self::CANCELLED, self::PURCHASED], true);
    }
}
