<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Support\Context\TenantContext;

class ExpenseController extends Controller
{
    private TenantContext $context;

    public function __construct(TenantContext $context)
    {
        $this->context = $context;
    }

    public function index(Request $request)
    {
        $query = Expense::where('doctor_id', $this->context->getClinicOwner()->id)->latest('expense_date');

        if ($request->has('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|gt:0',
            'expense_date' => 'nullable|date',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        $expenseDate = $validated['expense_date'] ?? $validated['date'] ?? null;

        if ($expenseDate === null) {
            return response()->json(['message' => 'Expense date is required.'], 422);
        }

        $expense = Expense::create([
            ...$validated,
            'expense_date' => $expenseDate,
            'doctor_id' => $this->context->getClinicOwner()->id,
        ]);

        AuditLog::log(
            'expense_created',
            "Recorded expense '{$expense->category}' of ₹{$expense->amount}",
            ['expense_id' => $expense->id, 'amount' => $expense->amount]
        );

        return response()->json($expense, 201);
    }

    public function show(Expense $expense)
    {
        if ($expense->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($expense);
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->doctor_id !== $this->context->getClinicOwner()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'category' => 'sometimes|string',
            'amount' => 'sometimes|numeric|gt:0',
            'expense_date' => 'sometimes|date',
            'description' => 'sometimes|string|nullable',
            'payment_method' => 'sometimes|string|nullable',
        ]);

        $oldAmount = $expense->amount;
        $expense->update($validated);

        if (array_key_exists('amount', $validated)) {
            AuditLog::log(
                'expense_updated',
                "Updated expense '{$expense->category}'",
                ['expense_id' => $expense->id, 'old_amount' => $oldAmount, 'new_amount' => $expense->amount]
            );
        }

        return response()->json($expense);
    }

    public function destroy(Expense $expense)
    {
        try {
            return response()->json(app(DeleteManager::class)->delete('expense', $expense->id));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function summary()
    {
        $doctorId = $this->context->getClinicOwner()->id;
        
        $totalExpenses = Expense::where('doctor_id', $doctorId)->sum('amount');
        
        $byCategory = Expense::where('doctor_id', $doctorId)
            ->select('category', \DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->get();

        return response()->json([
            'total' => $totalExpenses,
            'categories' => $byCategory
        ]);
    }
}
