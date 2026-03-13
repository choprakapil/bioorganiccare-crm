<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ProtectedDeletion;

class Expense extends Model
{
    use ProtectedDeletion;
    use \App\Traits\BelongsToTenancy;

    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'description',
        'payment_method'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
