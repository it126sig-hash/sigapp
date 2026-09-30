<?php

namespace Tests\Unit;

use App\Models\UserSignatureProfileModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class UserSignatureProfileModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $db->query('CREATE TABLE IF NOT EXISTS db_user_signature_profiles (user_id INTEGER PRIMARY KEY, signature_path VARCHAR(500) NOT NULL, updated_at DATETIME NOT NULL)');
        $db->table('user_signature_profiles')->truncate();
    }

    public function testFirstSignatureUsesManualPrimaryKeyInsertThenCanBeUpdated(): void
    {
        $model = new UserSignatureProfileModel();

        $this->assertTrue($model->insert([
            'user_id' => 77,
            'signature_path' => 'profile-signatures/77/first.png',
            'updated_at' => '2026-09-30 10:00:00',
        ], false));
        $this->assertSame('profile-signatures/77/first.png', $model->find(77)['signature_path']);

        $this->assertTrue($model->update(77, [
            'signature_path' => 'profile-signatures/77/second.png',
            'updated_at' => '2026-09-30 10:01:00',
        ]));
        $this->assertSame('profile-signatures/77/second.png', $model->find(77)['signature_path']);
    }
}
