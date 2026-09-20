<?php

namespace App\Services;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FeeService
{
    public function recordPayment(FeeInvoice $invoice, float $amount, string $method, ?string $referenceId, ?string $note, string $paidAt): FeePayment
    {
        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        $balance = (float) $invoice->amount - (float) $invoice->paid_amount;

        if ($amount > $balance) {
            throw new RuntimeException("Payment of {$amount} exceeds the outstanding balance of {$balance}.");
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $referenceId, $note, $paidAt) {
            $payment = FeePayment::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'transaction_id' => $referenceId,
                'note' => $note,
                'paid_at' => $paidAt,
                'received_by' => auth()->id(),
            ]);

            $newPaid = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->update([
                'paid_amount' => $newPaid,
                'status' => $newPaid >= (float) $invoice->amount
                    ? FeeInvoice::STATUS_PAID
                    : FeeInvoice::STATUS_PARTIAL,
            ]);

            return $payment;
        });
    }

    /**
     * Bulk-generate pending invoices for a fee structure across all active students
     * of a class (or all classes when the structure applies to every class).
     */
    public function generateInvoices(FeeInvoice $template): int
    {
        $query = Student::query()
            ->where('session_id', $template->session_id)
            ->active();

        if ($template->fee_structure_id) {
            $structure = $template->feeStructure;
            if ($structure->class_id) {
                $query->where('class_id', $structure->class_id);
            }
        }

        $students = $query->get();
        $created = 0;

        foreach ($students as $student) {
            $exists = FeeInvoice::query()
                ->where('session_id', $template->session_id)
                ->where('student_id', $student->id)
                ->where('fee_structure_id', $template->fee_structure_id)
                ->exists();

            if ($exists) {
                continue;
            }

            FeeInvoice::query()->create([
                'session_id' => $template->session_id,
                'student_id' => $student->id,
                'fee_structure_id' => $template->fee_structure_id,
                'invoice_no' => $this->nextInvoiceNo(),
                'amount' => $template->amount,
                'paid_amount' => 0,
                'due_date' => $template->due_date,
                'status' => FeeInvoice::STATUS_PENDING,
            ]);

            $created++;
        }

        return $created;
    }

    public function nextInvoiceNo(): string
    {
        $last = FeeInvoice::query()->max('id') ?? 0;

        return 'INV-'.str_pad((string) ($last + 1), 6, '0', STR_PAD_LEFT);
    }
}
