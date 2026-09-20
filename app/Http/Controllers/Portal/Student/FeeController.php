<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        if ($student === null) {
            abort(403, 'No student profile linked to this account.');
        }

        $invoices = FeeInvoice::query()
            ->where('student_id', $student->id)
            ->with(['feeStructure:id,name', 'session:id,name'])
            ->orderByDesc('id')
            ->get();

        $payments = FeePayment::query()
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->with('invoice:id,invoice_no')
            ->orderByDesc('paid_at')
            ->get();

        return Inertia::render('portal/student/Fees', [
            'invoices' => $invoices->map(fn (FeeInvoice $invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'structure' => $invoice->feeStructure?->name,
                'session' => $invoice->session?->name,
                'amount' => (float) $invoice->amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance' => $invoice->balance,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
            ]),
            'payments' => $payments->map(fn (FeePayment $payment) => [
                'id' => $payment->id,
                'invoice_no' => $payment->invoice?->invoice_no,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at,
                'transaction_id' => $payment->transaction_id,
            ]),
            'total_balance' => (float) $invoices->sum(fn (FeeInvoice $invoice) => $invoice->balance),
        ]);
    }
}
