<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceGenerateRequest;
use App\Http\Requests\Admin\PaymentRequest;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Services\FeeService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private readonly FeeService $fees) {}

    public function index(Request $request)
    {
        $query = FeeInvoice::query()
            ->with(['student:id,name,admission_no,class_id', 'student.class:id,name', 'feeStructure:id,name'])
            ->when($request->search, fn ($q, $s) => $q->whereHas('student', fn ($sq) => $sq->where('name', 'LIKE', "%{$s}%")->orWhere('admission_no', 'LIKE', "%{$s}%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->structure_id, fn ($q, $v) => $q->where('fee_structure_id', $v))
            ->when($request->month, function ($q, $v) {
                [$year, $month] = explode('-', $v);
                $q->whereYear('created_at', $year)->whereMonth('created_at', $month);
            })
            ->orderByDesc('id');

        return $this->render('admin/Invoices/Index', [
            'invoices' => $this->paginated($query->paginate(15)->withQueryString()),
            'structures' => FeeStructure::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'status', 'structure_id', 'month']),
        ]);
    }

    public function generate(InvoiceGenerateRequest $request)
    {
        $structure = FeeStructure::findOrFail($request->fee_structure_id);

        $template = new FeeInvoice;
        $template->session_id = $request->session_id;
        $template->fee_structure_id = $structure->id;
        $template->amount = $structure->amount;
        $template->due_date = $request->date('due_date');

        $created = $this->fees->generateInvoices($template);

        $this->successFlash("Invoices generated for {$created} students.");

        return redirect()->route('admin.invoices.index');
    }

    public function pay(PaymentRequest $request, FeeInvoice $invoice)
    {
        try {
            $payment = $this->fees->recordPayment(
                $invoice,
                (float) $request->amount,
                $request->method,
                $request->transaction_id,
                $request->note,
                $request->date('paid_at')?->toDateTimeString() ?? now()
            );

            $this->successFlash('Payment recorded.', [
                'invoice' => $invoice->fresh(),
                'payment' => $payment,
            ]);
        } catch (\Throwable $e) {
            $this->errorFlash($e->getMessage());
        }

        return back();
    }

    public function destroy(FeeInvoice $invoice)
    {
        $invoice->payments()->delete();
        $invoice->delete();
        $this->successFlash('Invoice deleted.');

        return back();
    }
}
