<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%")->orWhere('author', 'LIKE', "%{$s}%")->orWhere('isbn', 'LIKE', "%{$s}%"))
            ->when($request->category, fn ($q, $v) => $q->where('category', $v))
            ->orderByDesc('id');

        return $this->render('admin/Library/Index', [
            'books' => $this->paginated($query->paginate(15)->withQueryString()),
            'categories' => Book::query()->whereNotNull('category')->distinct()->pluck('category'),
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(BookRequest $request)
    {
        Book::query()->create([
            ...$request->validated(),
            'available_count' => $request->integer('quantity'),
        ]);
        $this->successFlash('Book added.');

        return redirect()->route('admin.books.index');
    }

    public function update(BookRequest $request, Book $book)
    {
        $delta = $request->integer('quantity') - $book->quantity;
        $book->update([
            ...$request->validated(),
            'available_count' => max(0, $book->available_count + $delta),
        ]);
        $this->successFlash('Book updated.');

        return redirect()->route('admin.books.index');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        $this->successFlash('Book removed.');

        return back();
    }
}
