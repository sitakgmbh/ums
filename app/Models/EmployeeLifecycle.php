<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EmployeeLifecycleEvent;

class EmployeeLifecycle extends Model
{
    protected $table = 'employee_lifecycle';

    protected $fillable = [
        'ad_user_id',
        'event',
        'description',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function adUser()
    {
        return $this->belongsTo(AdUser::class);
    }

    public function eventEnum(): EmployeeLifecycleEvent
    {
        return EmployeeLifecycleEvent::from($this->event);
    }

    public function getEventLabelAttribute(): string
    {
        return $this->eventEnum()->label();
    }
}
