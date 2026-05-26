<?php

namespace Tests\Libraries\Refactor\Generation;

use App\Libraries\Refactor\Generation\CodeGenerator;
use App\Libraries\Refactor\Generation\MigrationResult;
use App\Libraries\Refactor\Generation\ValidationExtractor;
use App\Libraries\Refactor\Generation\ValidationMigrator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * ValidationMigratorTest
 *
 * Unit tests for the ValidationMigrator class that migrates inline validation
 * rules from controllers to dedicated validation rule classes.
 *
 * @package Tests\Libraries\Refactor\Generation
 */
class ValidationMigratorTest extends CIUnitTestCase
{
    private ValidationMigrator $migrator;
    private ValidationExtractor $extractor;
    private CodeGenerator $codeGenerator;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeGenerator = new CodeGenerator();
        $this->extractor = new ValidationExtractor($this->codeGenerator);
        $this->migrator = new ValidationMigrator($this->extractor, $this->codeGenerator);

        // Create temp directory for file-based tests
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'validation_migrator_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    // =========================================================================
    // migrate() Tests
    // =========================================================================

    /**
     * Test migrate returns failure for non-existent controller
     */
    public function testMigrateReturnsFailureForNonExistentFile(): void
    {
        $result = $this->migrator->migrate('/non/existent/Controller.php');

        $this->assertInstanceOf(MigrationResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertStringContainsString('not found', $result->error);
    }

    /**
     * Test migrate skips controller with no validation rules
     */
    public function testMigrateSkipsControllerWithNoRules(): void
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

class EmptyController extends BaseController
{
    public function index()
    {
        return view('home');
    }
}
PHP;

        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'EmptyController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->migrator->migrate($filePath);

        $this->assertTrue($result->success);
        $this->assertTrue($result->skipped);
        $this->assertStringContainsString('No inline validation rules', $result->message);
    }

    /**
     * Test migrate extracts validation rules from controller
     */
    public function testMigrateExtractsValidationRules(): void
    {
        $controllerCode = $this->getSampleControllerWithRules();
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TransaksiController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->migrator->migrate($filePath);

        $this->assertTrue($result->success);
        $this->assertFalse($result->skipped);
        $this->assertGreaterThan(0, $result->rulesExtracted);
        $this->assertGreaterThan(0, $result->methodsAffected);
    }

    /**
     * Test migrate generates validation class code
     */
    public function testMigrateGeneratesValidationClassCode(): void
    {
        $controllerCode = $this->getSampleControllerWithRules();
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TransaksiController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->migrator->migrate($filePath);

        $this->assertNotNull($result->validationClassCode);
        $this->assertStringContainsString('TransaksiControllerValidation', $result->validationClassName);
        $this->assertStringContainsString('namespace App\\Validation', $result->validationClassCode);
        $this->assertStringContainsString('getRules', $result->validationClassCode);
    }

    /**
     * Test migrate generates language file content
     */
    public function testMigrateGeneratesLanguageFile(): void
    {
        $controllerCode = $this->getSampleControllerWithRules();
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TransaksiController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->migrator->migrate($filePath);

        $this->assertNotNull($result->languageFileContent);
        $this->assertStringContainsString('return [', $result->languageFileContent);
    }

    /**
     * Test migrate updates controller code to use validation class
     */
    public function testMigrateUpdatesControllerCode(): void
    {
        $controllerCode = $this->getSampleControllerWithRules();
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'TransaksiController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $this->migrator->migrate($filePath);

        $this->assertNotNull($result->updatedControllerCode);
        $this->assertStringContainsString('use App\\Validation\\TransaksiControllerValidation', $result->updatedControllerCode);
        $this->assertStringContainsString('TransaksiControllerValidation::getRules()', $result->updatedControllerCode);
    }

    /**
     * Test migrate writes files when appPath is set
     */
    public function testMigrateWritesFilesWhenAppPathSet(): void
    {
        $appPath = $this->tempDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
        mkdir($appPath, 0755, true);

        $migrator = new ValidationMigrator($this->extractor, $this->codeGenerator, $appPath);

        $controllerCode = $this->getSampleControllerWithRules();
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . 'BankController.php';
        file_put_contents($filePath, $controllerCode);

        $result = $migrator->migrate($filePath);

        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->filesCreated);

        // Check validation class file was created
        $validationFile = $appPath . 'Validation' . DIRECTORY_SEPARATOR . 'BankControllerValidation.php';
        $this->assertFileExists($validationFile);

        // Check language file was created
        $langFile = $appPath . 'Language' . DIRECTORY_SEPARATOR . 'id' . DIRECTORY_SEPARATOR . 'BankControllerValidation.php';
        $this->assertFileExists($langFile);
    }

    // =========================================================================
    // migrateAll() Tests
    // =========================================================================

    /**
     * Test migrateAll processes multiple controllers
     */
    public function testMigrateAllProcessesMultipleControllers(): void
    {
        $controller1 = $this->getSampleControllerWithRules();
        $controller2 = <<<'PHP'
<?php

namespace App\Controllers;

class EmptyController extends BaseController
{
    public function index()
    {
        return view('home');
    }
}
PHP;

        $path1 = $this->tempDir . DIRECTORY_SEPARATOR . 'Controller1.php';
        $path2 = $this->tempDir . DIRECTORY_SEPARATOR . 'Controller2.php';
        file_put_contents($path1, $controller1);
        file_put_contents($path2, $controller2);

        $results = $this->migrator->migrateAll([$path1, $path2]);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(MigrationResult::class, $results[0]);
        $this->assertInstanceOf(MigrationResult::class, $results[1]);
        $this->assertTrue($results[0]->success);
        $this->assertTrue($results[1]->success);
        $this->assertFalse($results[0]->skipped);
        $this->assertTrue($results[1]->skipped);
    }

    /**
     * Test migrateAll handles non-existent files gracefully
     */
    public function testMigrateAllHandlesNonExistentFiles(): void
    {
        $results = $this->migrator->migrateAll([
            '/non/existent/file1.php',
            '/non/existent/file2.php',
        ]);

        $this->assertCount(2, $results);
        $this->assertFalse($results[0]->success);
        $this->assertFalse($results[1]->success);
    }

    // =========================================================================
    // generateValidationClass() Tests
    // =========================================================================

    /**
     * Test generateValidationClass creates valid PHP code
     */
    public function testGenerateValidationClassCreatesValidCode(): void
    {
        $rules = [
            'nama' => 'required|max_length[255]',
            'email' => 'required|valid_email',
            'telepon' => 'permit_empty|numeric',
        ];

        $code = $this->migrator->generateValidationClass('KonsumenValidation', $rules);

        $this->assertStringContainsString('namespace App\\Validation', $code);
        $this->assertStringContainsString('class KonsumenValidation', $code);
        $this->assertStringContainsString('getRules', $code);
        $this->assertStringContainsString("'nama' => 'required|max_length[255]'", $code);
        $this->assertStringContainsString("'email' => 'required|valid_email'", $code);
        $this->assertStringContainsString("'telepon' => 'permit_empty|numeric'", $code);
    }

    /**
     * Test generateValidationClass generates individual field methods
     */
    public function testGenerateValidationClassGeneratesFieldMethods(): void
    {
        $rules = [
            'nama' => 'required',
            'alamat' => 'permit_empty|max_length[500]',
        ];

        $code = $this->migrator->generateValidationClass('AlamatValidation', $rules);

        $this->assertStringContainsString('getNamaRules', $code);
        $this->assertStringContainsString('getAlamatRules', $code);
    }

    /**
     * Test generateValidationClass with empty rules
     */
    public function testGenerateValidationClassWithEmptyRules(): void
    {
        $code = $this->migrator->generateValidationClass('EmptyValidation', []);

        $this->assertStringContainsString('class EmptyValidation', $code);
        $this->assertStringContainsString('getRules', $code);
    }

    // =========================================================================
    // updateControllerToUseValidationClass() Tests
    // =========================================================================

    /**
     * Test updateControllerToUseValidationClass adds use statement
     */
    public function testUpdateControllerAddsUseStatement(): void
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\TransaksiModel;

class Transaksi extends BaseController
{
    public function simpan()
    {
        $rules = [
            'nama' => 'required',
            'jumlah' => 'required|numeric',
        ];
    }
}
PHP;

        $updated = $this->migrator->updateControllerToUseValidationClass(
            $controllerCode,
            'TransaksiValidation'
        );

        $this->assertStringContainsString('use App\\Validation\\TransaksiValidation;', $updated);
    }

    /**
     * Test updateControllerToUseValidationClass replaces inline rules
     */
    public function testUpdateControllerReplacesInlineRules(): void
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\TransaksiModel;

class Transaksi extends BaseController
{
    public function simpan()
    {
        $rules = [
            'nama' => 'required',
            'jumlah' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back();
        }
    }
}
PHP;

        $updated = $this->migrator->updateControllerToUseValidationClass(
            $controllerCode,
            'TransaksiValidation'
        );

        $this->assertStringContainsString('TransaksiValidation::getRules()', $updated);
        // The inline array should be replaced
        $this->assertStringNotContainsString("'nama' => 'required'", $updated);
    }

    /**
     * Test updateControllerToUseValidationClass does not duplicate use statement
     */
    public function testUpdateControllerDoesNotDuplicateUseStatement(): void
    {
        $controllerCode = <<<'PHP'
<?php

namespace App\Controllers;

use App\Validation\TransaksiValidation;

class Transaksi extends BaseController
{
    public function simpan()
    {
        $rules = [
            'nama' => 'required',
        ];
    }
}
PHP;

        $updated = $this->migrator->updateControllerToUseValidationClass(
            $controllerCode,
            'TransaksiValidation'
        );

        // Count occurrences of the use statement
        $count = substr_count($updated, 'use App\\Validation\\TransaksiValidation;');
        $this->assertEquals(1, $count);
    }

    // =========================================================================
    // generateMigrationReport() Tests
    // =========================================================================

    /**
     * Test generateMigrationReport creates markdown report
     */
    public function testGenerateMigrationReportCreatesMarkdown(): void
    {
        $results = [];

        $result1 = new MigrationResult();
        $result1->success = true;
        $result1->controllerPath = '/app/Controllers/Transaksi.php';
        $result1->validationClassName = 'TransaksiValidation';
        $result1->rulesExtracted = 5;
        $result1->methodsAffected = 2;
        $result1->filesCreated = ['/app/Validation/TransaksiValidation.php'];
        $results[] = $result1;

        $result2 = new MigrationResult();
        $result2->success = true;
        $result2->skipped = true;
        $result2->controllerPath = '/app/Controllers/Home.php';
        $result2->message = 'No inline validation rules found in Home';
        $results[] = $result2;

        $result3 = new MigrationResult();
        $result3->success = false;
        $result3->controllerPath = '/app/Controllers/Missing.php';
        $result3->error = 'Controller file not found';
        $results[] = $result3;

        $report = $this->migrator->generateMigrationReport($results);

        $this->assertStringContainsString('# Validation Migration Report', $report);
        $this->assertStringContainsString('Total Controllers | 3', $report);
        $this->assertStringContainsString('Successfully Migrated | 1', $report);
        $this->assertStringContainsString('Skipped (no rules) | 1', $report);
        $this->assertStringContainsString('Failed | 1', $report);
        $this->assertStringContainsString('Transaksi', $report);
        $this->assertStringContainsString('✅ Success', $report);
        $this->assertStringContainsString('⏭️ Skipped', $report);
        $this->assertStringContainsString('❌ Failed', $report);
    }

    /**
     * Test generateMigrationReport with empty results
     */
    public function testGenerateMigrationReportWithEmptyResults(): void
    {
        $report = $this->migrator->generateMigrationReport([]);

        $this->assertStringContainsString('# Validation Migration Report', $report);
        $this->assertStringContainsString('Total Controllers | 0', $report);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Get sample controller code with inline validation rules
     */
    private function getSampleControllerWithRules(): string
    {
        return <<<'PHP'
<?php

namespace App\Controllers;

use App\Models\TransaksiModel;

class TransaksiController extends BaseController
{
    public function simpan()
    {
        $rules = [
            'nama' => 'required|max_length[255]',
            'jumlah' => 'required|numeric',
            'tanggal' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new TransaksiModel();
        $model->save($this->request->getPost());

        return redirect()->to('/transaksi');
    }
}
PHP;
    }

    /**
     * Recursively remove a directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
