<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookIssueRequest;
use App\Http\Requests\Admin\BookReturnRequest;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\User;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = BookIssue::query()
            ->with('book:id,title,isbn', 'user:id,name,email')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->search, fn ($q, $s) => $q->whereHas('book', fn ($bq) => $bq->where('title', 'LIKE', "%{$s}%"))->orWhereHas('user', fn ($uq) => $uq->where('name', 'LIKE', "%{$s}%")))
            ->orderByDesc('issued_at');

        return $this->render('admin/Library/Issues', [
            'issues' => $this->paginated($query->paginate(15)->withQueryString()),
            'books' => Book::query()->where('available_count', '>', 0)->select('id', 'title', 'available_count')->get(),
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function store(BookIssueRequest $request)
    {
        $book = Book::findOrFail($request->book_id);

        if ($book->available_count < 1) {
            $this->errorFlash('No copies available for this book.');

            return back();
        }

        $book->decrement('available_count');

        BookIssue::query()->create([
            'book_id' => $book->id,
            'user_id' => $request->user_id,
            'issued_at' => now(),
            'due_date' => $request->date('due_date') ?? now()->addDays(14),
            'status' => BookIssue::STATUS_ISSUED,
        ]);

        $this->successFlash('Book issued.');

        return redirect()->route('admin.book-issues.index');
    }

    public function returnBook(BookReturnRequest $request, BookIssue $issue)
    {
        if ($issue->status !== BookIssue::STATUS_ISSUED) {
            $this->errorFlash('This book has already been returned.');

            return back();
        }

        $issue->update([
            'status' => BookIssue::STATUS_RETURNED,
            'returned_at' => $request->date('returned_at') ?? now(),
        ]);
        $issue->book()->increment('available_count');

        $this->successFlash('Book returned.');

        return back();
    }
}
