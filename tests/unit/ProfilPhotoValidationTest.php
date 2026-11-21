<?php

namespace Tests\Unit;

use App\Repositories\UserManagement\ProfilRepository;
use App\Services\UserManagement\ProfilService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class TestableProfilRepository extends ProfilRepository
{
    public function __construct()
    {
        // Don't connect to db for pure unit tests
    }

    public function getUserRow(int $userId): ?object
    {
        return (object) [
            'id'            => $userId,
            'name'          => 'Existing User',
            'username'      => 'existinguser',
            'email'         => 'user@example.com',
            'profile_photo' => null,
        ];
    }

    public function updateUser(int $userId, array $fields): bool
    {
        return true;
    }

    public function syncKaryawanName(int $userId, string $name): bool
    {
        return true;
    }
}

class TestableProfilService extends ProfilService
{
    public function __construct()
    {
        parent::__construct();
        $this->profilRepo = new TestableProfilRepository();
    }
}

final class ProfilPhotoValidationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $db->query('CREATE TABLE IF NOT EXISTS db_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) NOT NULL,
            username VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NULL,
            reset_hash VARCHAR(255) NULL,
            reset_at DATETIME NULL,
            reset_expires DATETIME NULL,
            profile_photo VARCHAR(255) NULL,
            email_notif_enabled INTEGER DEFAULT 1,
            updated_at DATETIME NULL
        )');
        $db->table('users')->truncate();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_FILES = [];
        \Config\Services::reset(true);
    }

    public function testUpdateProfileWithValidPhotoDoesNotThrowTypeError(): void
    {
        $tempImage = tempnam(sys_get_temp_dir(), 'test_img_');
        $im = imagecreatetruecolor(100, 100);
        imagejpeg($im, $tempImage);
        imagedestroy($im);

        $_FILES['profile_photo'] = [
            'name'     => 'avatar.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $tempImage,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($tempImage),
        ];

        \Config\Services::reset(true);
        $request = \Config\Services::request();
        $photo   = $request->getFile('profile_photo');

        $service = new TestableProfilService();

        $postData = [
            'name'             => 'Updated User',
            'username'         => 'updateduser',
            'email'            => 'updated@example.com',
            'password'         => '',
            'password_confirm' => '',
        ];

        // This must not throw TypeError: CodeIgniter\Validation\FileRules::uploaded()
        $result = $service->updateProfile(1, $postData, $photo);

        $this->assertIsArray($result);
        $this->assertTrue($result['success'], 'Update profile with valid photo should succeed: ' . json_encode($result));

        @unlink($tempImage);
    }

    public function testUpdateProfileWithInvalidPhotoFailsValidationWithoutTypeError(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
        file_put_contents($tempFile, 'plain text file pretending to be photo');

        $_FILES['profile_photo'] = [
            'name'     => 'document.txt',
            'type'     => 'text/plain',
            'tmp_name' => $tempFile,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($tempFile),
        ];

        \Config\Services::reset(true);
        $request = \Config\Services::request();
        $photo   = $request->getFile('profile_photo');

        $service = new TestableProfilService();

        $postData = [
            'name'             => 'Updated User',
            'username'         => 'updateduser',
            'email'            => 'updated@example.com',
            'password'         => '',
            'password_confirm' => '',
        ];

        // This must fail validation gracefully and NOT throw TypeError
        $result = $service->updateProfile(1, $postData, $photo);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('profile_photo', $result['messages']);

        @unlink($tempFile);
    }

    public function testUpdateProfileWithoutPhotoSucceeds(): void
    {
        \Config\Services::reset(true);
        $service = new TestableProfilService();

        $postData = [
            'name'             => 'Updated User',
            'username'         => 'updateduser',
            'email'            => 'updated@example.com',
            'password'         => '',
            'password_confirm' => '',
        ];

        $result = $service->updateProfile(1, $postData, null);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }
}
