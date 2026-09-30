<?php

namespace Tests\Unit;

use App\Services\Bpb\BpbWorkflow;
use CodeIgniter\Test\CIUnitTestCase;

final class BpbWorkflowTest extends CIUnitTestCase
{
    public function testApprovalChainUsesCcWhenConfigured(): void
    {
        $this->assertSame(BpbWorkflow::WAITING_CC, BpbWorkflow::approvalStatus(true));
        $this->assertSame(BpbWorkflow::WAITING_APPROVER, BpbWorkflow::afterSignature(BpbWorkflow::WAITING_CC));
        $this->assertSame(BpbWorkflow::APPROVED, BpbWorkflow::afterSignature(BpbWorkflow::WAITING_APPROVER));
    }

    public function testOperationalTransitionsAreForwardOnly(): void
    {
        $this->assertTrue(BpbWorkflow::canOperationalTransition(BpbWorkflow::APPROVED, BpbWorkflow::PROCESSED));
        $this->assertTrue(BpbWorkflow::canOperationalTransition(BpbWorkflow::DISBURSED, BpbWorkflow::PENDING));
        $this->assertTrue(BpbWorkflow::canOperationalTransition(BpbWorkflow::PENDING, BpbWorkflow::PURCHASED));
        $this->assertFalse(BpbWorkflow::canOperationalTransition(BpbWorkflow::PENDING, BpbWorkflow::DISBURSED));
        $this->assertFalse(BpbWorkflow::canOperationalTransition(BpbWorkflow::PURCHASED, BpbWorkflow::PENDING));
    }

    public function testRejectedCancelledAndPurchasedAreTerminal(): void
    {
        $this->assertTrue(BpbWorkflow::isTerminal(BpbWorkflow::REJECTED));
        $this->assertTrue(BpbWorkflow::isTerminal(BpbWorkflow::CANCELLED));
        $this->assertTrue(BpbWorkflow::isTerminal(BpbWorkflow::PURCHASED));
        $this->assertFalse(BpbWorkflow::isTerminal(BpbWorkflow::PENDING));
    }
}
