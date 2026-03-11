<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_name',
        'phone',
        'plan',
        'plan_expired_at',
        'max_students',
        'max_packages',
        'max_questions',
        'max_classes',
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
            'plan_expired_at' => 'datetime',
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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expired_at', '>=', now())
            ->latest();
    }

    public function usageLogs()
    {
        return $this->hasMany(UsageLog::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function subscriptionHistories()
    {
        return $this->hasMany(SubscriptionHistory::class);
    }

    // Get limits from settings based on plan
    public function getMaxStudentsAttribute($value)
    {
        return $this->getLimitFromSettings('max_students', $value);
    }

    public function getMaxPackagesAttribute($value)
    {
        return $this->getLimitFromSettings('max_packages', $value);
    }

    public function getMaxQuestionsAttribute($value)
    {
        return $this->getLimitFromSettings('max_questions', $value);
    }

    public function getMaxClassesAttribute($value)
    {
        return $this->getLimitFromSettings('max_classes', $value);
    }

    protected function getLimitFromSettings($key, $fallback)
    {
        $limits = Setting::getByGroup('limits');
        $settingKey = $this->plan . '_' . $key;
        return $limits[$settingKey] ?? $fallback;
    }

    // Helper Methods - Check Limits
    public function canAddStudent(): bool
    {
        return $this->students()->where('is_active', true)->count() < $this->max_students;
    }

    public function canAddPackage(): bool
    {
        return $this->testPackages()->count() < $this->max_packages;
    }

    public function canAddQuestion(): bool
    {
        return $this->questions()->count() < $this->max_questions;
    }

    public function canAddClass(): bool
    {
        return $this->classes()->count() < $this->max_classes;
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

    // Plan checks
    public function isFree(): bool
    {
        return $this->plan === 'free';
    }

    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    public function isAdvanced(): bool
    {
        return $this->plan === 'advanced';
    }

    public function isPlanExpired(): bool
    {
        if ($this->isFree()) {
            return false;
        }
        return $this->plan_expired_at && $this->plan_expired_at->isPast();
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

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
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
