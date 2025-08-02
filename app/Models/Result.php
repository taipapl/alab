<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model
{
    /** @use HasFactory<\Database\Factories\ResultFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'order_id',
        'test_name',
        'test_value',
        'test_reference',
    ];

    protected $casts = [
        'result_date' => 'datetime',
    ];

    /**
     * Define the relationship with the patient.
     *
     * @return BelongsTo<User, Result>
     */
    public function patient(): BelongsTo
    {
        /** @var BelongsTo<User, Result> */
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Define the relationship with the order.
     *
     * @return BelongsTo<Order, Result>
     */
    public function order(): BelongsTo
    {
        /** @var BelongsTo<Order, Result> */
        return $this->belongsTo(Order::class);
    }
}