<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends ApiController
{
    /**
     * Get all expenses with optional filters
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('expense_type', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('date', '>=', $request->get('from_date'));
        }
        if ($request->has('to_date')) {
            $query->whereDate('date', '<=', $request->get('to_date'));
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $expenses */
        $expenses = $query->orderBy('date', 'desc')->paginate(20);

        // Transform for mobile app
        $transformedExpenses = collect($expenses->items())->map(function ($expense) {
            return $this->transformExpense($expense);
        });

        return $this->paginated($expenses->setCollection($transformedExpenses));
    }

    /**
     * Get a single expense by ID
     */
    public function show($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->error('Expense not found', 404);
        }

        return $this->success($this->transformExpense($expense));
    }

    /**
     * Create a new expense
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Accept various field names from frontend
        $category = $request->category ?? $request->expense_category ?? 'other';
        $description = $request->description ?? $request->expense_description ?? '';
        $date = $request->expense_date ?? $request->date ?? now()->toDateString();

        $expense = Expense::create([
            'category' => $category,
            'expense_type' => $request->expense_type ?? $request->payment_method ?? 'cash',
            'amount' => $request->amount,
            'date' => $date,
            'status' => $request->status ?? 'pending',
            'description' => $description,
        ]);

        return $this->success($this->transformExpense($expense), 'Expense created successfully', 201);
    }

    /**
     * Update an expense
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->error('Expense not found', 404);
        }

        $updateData = [];
        
        if ($request->has('category') || $request->has('expense_category')) {
            $updateData['category'] = $request->category ?? $request->expense_category;
        }
        if ($request->has('description') || $request->has('expense_description')) {
            $updateData['description'] = $request->description ?? $request->expense_description;
        }
        if ($request->has('amount')) {
            $updateData['amount'] = $request->amount;
        }
        if ($request->has('date') || $request->has('expense_date')) {
            $updateData['date'] = $request->expense_date ?? $request->date;
        }
        if ($request->has('expense_type') || $request->has('payment_method')) {
            $updateData['expense_type'] = $request->expense_type ?? $request->payment_method;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        $expense->update($updateData);

        return $this->success($this->transformExpense($expense), 'Expense updated successfully');
    }

    /**
     * Delete an expense
     */
    public function destroy($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return $this->error('Expense not found', 404);
        }

        $expense->delete();
        return $this->success(null, 'Expense deleted successfully');
    }

    /**
     * Get expense statistics
     */
    public function stats()
    {
        $totalExpenses = Expense::count();
        $totalAmount = Expense::sum('amount');
        $pending = Expense::where('status', 'pending')->count();

        return $this->success([
            'total_expenses' => $totalExpenses,
            'total_amount' => (float) $totalAmount,
            'pending' => $pending,
        ]);
    }

    /**
     * Transform expense data for frontend compatibility
     */
    private function transformExpense($expense)
    {
        return [
            'id' => $expense->id,
            'expense_number' => 'EXP-' . str_pad($expense->id, 6, '0', STR_PAD_LEFT),
            'category' => $expense->category,
            'expense_type' => $expense->expense_type,
            'payment_method' => $expense->expense_type, // alias
            'description' => $expense->description,
            'amount' => (float) $expense->amount,
            'expense_date' => $expense->date ? $expense->date->toDateString() : null,
            'date' => $expense->date ? $expense->date->toDateString() : null,
            'status' => $expense->status ?? 'pending',
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
        ];
    }
}
