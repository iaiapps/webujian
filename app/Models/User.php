<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_name',
        'phone',
        'max_students',
        'max_questions',
        'max_classes',
        'credits',
        'is_active',
        'approved_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            // SISTEM KREDIT - plan_expired_at dihapus
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function testPackages()
    {
        return $this->hasMany(TestPackage::class);
    }

    // SISTEM KREDIT - subscriptions dinonaktifkan
    // public function subscriptions()
    // {
    //     return $this->hasMany(Subscription::class);
    // }

    // SISTEM KREDIT - activeSubscription dinonaktifkan
    // public function activeSubscription()
    // {
    //     return $this->hasOne(Subscription::class)
    //         ->where('status', 'active')
    //         ->where('expired_at', '>=', now())
    //         ->latest();
    // }

    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // SISTEM KREDIT - History transaksi kredit
    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class)->latest();
    }

    // SISTEM KREDIT - History pembelian kredit
    public function creditPurchases()
    {
        return $this->hasMany(CreditPurchase::class)->latest();
    }

    // SISTEM KREDIT - subscriptionHistories dinonaktifkan
    // public function subscriptionHistories()
    // {
    //     return $this->hasMany(SubscriptionHistory::class);
    // }

    // ============================================================
    // SISTEM KREDIT - GANTI DARI SUBSCRIPTION
    // Sekarang menggunakan setting global, bukan per-plan
    // ============================================================

    // Get limits from GLOBAL settings (tidak bergantung pada plan)
    public function getMaxStudentsAttribute($value)
    {
        return Setting::get('global_max_students', $value ?? 50);
    }

    public function getMaxQuestionsAttribute($value)
    {
        return Setting::get('global_max_questions', $value ?? 100);
    }

    public function getMaxClassesAttribute($value)
    {
        // KELAS DINONAKTIFKAN - selalu return 999999
        return 999999;
    }

    protected function getLimitFromSettings($key, $fallback)
    {
        // ============================================================
        // SISTEM KREDIT - Menggunakan setting global
        // ============================================================
        return Setting::get('global_'.$key, $fallback);
    }

    // ============================================================
    // SISTEM KREDIT - Method untuk kredit
    // ============================================================
    public function hasCredits(): bool
    {
        return $this->credits > 0;
    }

    public function canCreatePackage(): bool
    {
        return $this->credits >= 1;
    }

    public function getCreditsAttribute($value)
    {
        return $value ?? 0;
    }

    // SISTEM KREDIT - Add credits dengan history
    public function addCredits(int $amount, string $type = 'manual_add', string $description = '', ?string $referenceId = null, ?string $referenceType = null, ?int $performedBy = null, ?string $notes = null): CreditTransaction
    {
        $balanceBefore = $this->credits;
        $this->increment('credits', $amount);
        $this->refresh();

        return $this->creditTransactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->credits,
            'description' => $description ?: "Penambahan {$amount} kredit",
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'performed_by' => $performedBy,
            'notes' => $notes,
        ]);
    }

    // SISTEM KREDIT - Deduct credits dengan history
    public function deductCredits(int $amount = 1, string $type = 'usage', string $description = '', ?string $referenceId = null, ?string $referenceType = null): ?CreditTransaction
    {
        if ($this->credits < $amount) {
            return null;
        }

        $balanceBefore = $this->credits;
        $this->decrement('credits', $amount);
        $this->refresh();

        return $this->creditTransactions()->create([
            'type' => $type,
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->credits,
            'description' => $description ?: "Penggunaan {$amount} kredit",
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'performed_by' => null,
            'notes' => null,
        ]);
    }

    // Helper Methods - Check Limits
    public function canAddStudent(): bool
    {
        return $this->students()->where('is_active', true)->count() < $this->max_students;
    }

    // ============================================================
    // SISTEM KREDIT - canAddPackage diganti dengan canCreatePackage
    // Tidak ada batas paket, tapi butuh kredit untuk membuat
    // ============================================================
    public function canAddPackage(): bool
    {
        return $this->canCreatePackage();
    }

    public function canAddQuestion(): bool
    {
        return $this->questions()->count() < $this->max_questions;
    }

    public function canAddClass(): bool
    {
        // KELAS DINONAKTIFKAN
        return true;
    }

    // Get current counts
    public function studentsCount(): int
    {
        return $this->students()->where('is_active', true)->count();
    }

    public function packagesCount(): int
    {
        return $this->testPackages()->count();
    }

    public function questionsCount(): int
    {
        return $this->questions()->count();
    }

    public function classesCount(): int
    {
        return $this->classes()->count();
    }

    // ============================================================
    // SISTEM KREDIT - Plan methods tidak lagi digunakan
    // Dihybrid untuk backward compatibility dengan admin
    // ============================================================
    // public function isFree(): bool
    // {
    //     return $this->plan === 'free';
    // }

    // public function isPro(): bool
    // {
    //     return $this->plan === 'pro';
    // }

    // public function isAdvanced(): bool
    // {
    //     return $this->plan === 'advanced';
    // }

    // public function isPlanExpired(): bool
    // {
    //     return false; // Tidak berlaku lagi
    // }

    // Untuk backward compatibility
    public function isFree(): bool
    {
        return true;
    }

    public function isPro(): bool
    {
        return false;
    }

    public function isAdvanced(): bool
    {
        return false;
    }

    public function isPlanExpired(): bool
    {
        return false;
    }

    // Role checks
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isGuru(): bool
    {
        return $this->hasRole('guru');
    }

    // ============================================================
    // APPROVAL MANUAL DINONAKTIFKAN
    // User yang baru daftar langsung approved
    // ============================================================

    // public function isApproved(): bool
    // {
    //     return $this->approved_at !== null;
    // }

    // Untuk backward compatibility, return true selalu
    public function isApproved(): bool
    {
        return true;
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGuru($query)
    {
        return $query->role('guru');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }
}
