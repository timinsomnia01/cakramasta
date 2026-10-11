<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Catatan kunjungan untuk statistik dashboard admin.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $product_id
 * @property string|null $session_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $url
 * @property Carbon $visited_date
 * @property Carbon|null $created_at
 * @property-read User|null $user
 * @property-read Product|null $product
 */
#[Fillable(['user_id', 'product_id', 'session_id', 'ip_address', 'user_agent', 'url', 'visited_date'])]
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * Record a visit from the current request.
     */
    public static function record(Request $request, ?Product $product = null): self
    {
        return static::create([
            'user_id' => $request->user()?->id,
            'product_id' => $product?->id,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'visited_date' => now()->toDateString(),
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Jumlah pengunjung unik (per session) dalam rentang tanggal.
     */
    public static function uniqueVisitors(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        return static::query()
            ->when($from, fn (Builder $q) => $q->whereDate('visited_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('visited_date', '<=', $to))
            ->distinct()
            ->count('session_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_date' => 'date',
        ];
    }
}
