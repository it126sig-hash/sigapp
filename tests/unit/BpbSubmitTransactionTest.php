<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class BpbSubmitTransactionTest extends CIUnitTestCase
{
    public function testDirectSubmitDoesNotPersistASeparateDraftBeforeSubmission(): void
    {
        $source = file_get_contents(APPPATH . 'Services/Bpb/BpbService.php');
        $start = strpos($source, 'public function submit(');
        $end = strpos($source, 'public function update(', $start);
        $submit = substr($source, $start, $end - $start);

        $this->assertStringNotContainsString('saveDraft(', $submit);
        $this->assertStringContainsString('saveSubmitted($id', $submit);
    }
}
