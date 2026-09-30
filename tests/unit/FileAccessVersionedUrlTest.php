<?php

use App\Services\FileAccessService;
use CodeIgniter\Test\CIUnitTestCase;

final class FileAccessVersionedUrlTest extends CIUnitTestCase
{
    public function testVersionedUrlIsStableUntilFileMetadataChanges(): void
    {
        $service = new FileAccessService();
        $logicalPath = 'phpunit/siteplan-cache-' . bin2hex(random_bytes(5)) . '.png';
        $absolutePath = $service->privatePath($logicalPath);
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        try {
            file_put_contents($absolutePath, 'first');
            clearstatcache(true, $absolutePath);
            $first = $service->versionedAccessUrl('proyek_siteplan', 12, $logicalPath);
            $same = $service->versionedAccessUrl('proyek_siteplan', 12, $logicalPath);

            file_put_contents($absolutePath, 'second-version');
            clearstatcache(true, $absolutePath);
            $changed = $service->versionedAccessUrl('proyek_siteplan', 12, $logicalPath);

            $this->assertSame($first, $same);
            $this->assertStringContainsString('/files/proyek_siteplan/12?v=', $first);
            $this->assertNotSame($first, $changed);
        } finally {
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
            if (is_dir($directory) && count(scandir($directory) ?: []) === 2) {
                rmdir($directory);
            }
        }
    }

    public function testMissingLogicalPathKeepsGatewayUrlWithoutVersion(): void
    {
        $service = new FileAccessService();

        $this->assertSame(
            site_url('files/proyek_siteplan/12'),
            $service->versionedAccessUrl('proyek_siteplan', 12, null)
        );
    }
}
