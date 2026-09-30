<?php

namespace App\Models;

use CodeIgniter\Model;

class BpbRequestModel extends Model
{
    protected $table = 'bpb_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nomor', 'sequence_no', 'sequence_year', 'applicant_user_id', 'applicant_name', 'applicant_department', 'submission_key',
        'cc_user_id', 'approver_user_id', 'status', 'current_document_hash', 'verification_token', 'actual_amount',
        'recipient_name', 'account_number', 'disbursement_note', 'purchase_date', 'purchase_note', 'pending_reason',
        'rejection_reason', 'cancellation_reason', 'submitted_at', 'approved_at', 'processed_at', 'disbursed_at',
        'purchased_at', 'created_by', 'updated_by',
    ];
}
