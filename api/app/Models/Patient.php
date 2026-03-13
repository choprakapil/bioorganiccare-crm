<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Traits\ProtectedDeletion;

class Patient extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, ProtectedDeletion;
    use SoftDeletes, \App\Traits\BelongsToTenancy;
    protected $fillable = ['name', 'phone', 'age', 'gender', 'address', 'first_visit_date'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }
}
