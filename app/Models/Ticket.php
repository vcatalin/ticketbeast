<?php

namespace App\Models;

use App\Facades\TicketCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function concert(): BelongsTo
    {
        return  $this->belongsTo(Concert::class);
    }

    public function reserve(): void
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function release(): void
    {
        $this->update(['reserved_at' => null]);
    }

    public function claimFor(Order $order): void
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
    }

    public function getPriceAttribute(): int
    {
        return  $this->concert->ticket_price;
    }
}
