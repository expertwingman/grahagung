<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ScopesByRole;
use App\Models\Activity;

class Lead extends Model
{
    use ScopesByRole, SoftDeletes;

    /**
     * Daftar status sesuai alur kerja properti GAK.
     * Satu sumber kebenaran — dipakai di dropdown, badge, dan validasi.
     * key (disimpan di DB) => label (ditampilkan ke user)
     */
    public const STATUSES = [
        'no_respon' => 'Belum Merespon',
        'respon'    => 'Sudah Merespon',
        'kirim_pl'  => 'Penawaran Terkirim',
        'survey'    => 'Survei Lokasi',
        'utj'       => 'Tanda Jadi',
        'closing'   => 'Closing',
        'batal'     => 'Tidak Jadi',
    ];

    /** Warna badge per status (kelas Tailwind). */
    public const STATUS_COLORS = [
        'no_respon' => 'bg-gray-100 text-gray-700',
        'respon'    => 'bg-blue-100 text-blue-700',
        'kirim_pl'  => 'bg-purple-100 text-purple-700',
        'survey'    => 'bg-yellow-100 text-yellow-700',
        'utj'       => 'bg-orange-100 text-orange-700',
        'closing'   => 'bg-green-100 text-green-700',
        'batal'     => 'bg-red-100 text-red-700',
    ];

    /** Label status untuk lead ini. */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Kelas warna badge untuk lead ini. */
    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Scope: hanya lead yang BENTROK — nomor WA-nya dipegang >1 sales.
     * Dipakai di halaman khusus & untuk menandai di daftar.
     */
    public function scopeConflicting($query)
    {
        return $query->whereIn('wa_phone', function ($sub) {
            $sub->select('wa_phone')
                ->from('leads')
                ->whereNotNull('wa_phone')
                ->where('wa_phone', '!=', '')
                ->groupBy('wa_phone')
                ->havingRaw('COUNT(DISTINCT assigned_to) > 1');
        });
    }

    /**
     * Daftar nomor WA yang bentrok (dipakai untuk menandai badge di tabel).
     * Mengembalikan array nomor WA.
     */
    public static function conflictingPhones(?User $user = null): array
    {
        $user ??= auth()->user();

        // Nomor WA yang ada di data milik user ini saja
        $visiblePhones = static::visibleTo($user)
            ->whereNotNull('wa_phone')
            ->where('wa_phone', '!=', '')
            ->distinct()
            ->pluck('wa_phone')
            ->toArray();

        if (empty($visiblePhones)) {
            return [];
        }

        // Dari nomor tersebut, mana yang benar-benar dipegang >1 sales.
        // Hasilnya: staff hanya tahu lead-nya SENDIRI yang bentrok,
        // bukan seluruh nomor bentrok se-perusahaan.
        return static::query()
            ->whereIn('wa_phone', $visiblePhones)
            ->groupBy('wa_phone')
            ->havingRaw('COUNT(DISTINCT assigned_to) > 1')
            ->pluck('wa_phone')
            ->toArray();
    }

    /**
     * Berapa hari sebuah lead boleh "diam" sebelum dianggap perlu di-follow up lagi.
     */
    public const FOLLOW_UP_THRESHOLD_DAYS = 3;

    /**
     * Scope: lead yang PERLU DI-FOLLOW UP.
     * Yaitu lead yang masih aktif (belum closing/batal) DAN:
     *   - belum pernah di-follow up (follow_up_date kosong), ATAU
     *   - follow up terakhir sudah lewat ambang batas (default 3 hari).
     */
    public function scopeNeedsFollowUp($query)
    {
        $threshold = now()->subDays(self::FOLLOW_UP_THRESHOLD_DAYS)->toDateString();

        return $query
            ->whereNotIn('status', ['closing', 'batal'])
            ->where(function ($q) use ($threshold) {
                $q->whereNull('follow_up_date')
                  ->orWhereDate('follow_up_date', '<=', $threshold);
            });
    }

    /**
     * Scope: lead DINGIN — masih aktif tapi diam ≥ $days hari.
     */
    public function scopeCold($query, int $days = 30)
    {
        $batas = now()->subDays($days);

        return $query
            ->whereNotIn('status', ['closing', 'batal'])
            ->where(function ($q) use ($batas) {
                $q->where(function ($q2) use ($batas) {
                        $q2->whereNull('follow_up_date')->where('created_at', '<=', $batas);
                    })
                  ->orWhere('follow_up_date', '<=', $batas->toDateString());
            });
    }

    /** Berapa hari lead ini diam. */
    public function daysSilent(): int
    {
        $sejak = $this->follow_up_date ?? $this->created_at;
        return $sejak ? (int) $sejak->diffInDays(now()) : 0;
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    protected $fillable = [
    'name',
    'phone',
    'email',
    'company',
    'source',
    'status',
    'value',
    'notes',
    'address',
    'city',
    'wa_phone',
    'assigned_to',
    'created_by',
    'last_contacted_at',
    'product_id',
    'interest_notes',
    // Kolom properti baru
    'budget_range',
    'interest_type',
    'location_interest',
    'follow_up_date',
    'survey_plan',
    'survey_result',
    'utj_status',
    'utj_date',
    'cancel_reason',
    'input_date',
];

protected $casts = [
    'value'            => 'decimal:2',
    'last_contacted_at'=> 'datetime',
    'follow_up_date'   => 'date',
    'utj_date'         => 'date',
    'utj_status'       => 'boolean',
    'input_date' => 'date',
];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
