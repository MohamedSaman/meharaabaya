<?php


namespace App\Models;

use App\Models\Attendance;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // Relationship: User has many attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    // Relationship: User has many staff permissions
    public function staffPermissions()
    {
        return $this->hasMany(StaffPermission::class, 'user_id', 'id');
    }

    /**
     * Check if user has a specific permission
     * If staff has no permissions assigned, grant full access by default
     */
    public function hasPermission($permissionKey)
    {
        if ($this->role === 'admin') {
            return true; // Admin has all permissions
        }

        // If staff has no permissions assigned at all, grant full access
        $hasAnyPermissions = $this->staffPermissions()->exists();
        if (!$hasAnyPermissions) {
            return true; // Default: full access when no permissions are set
        }

        // If permissions are assigned, check for specific permission
        return $this->staffPermissions()
            ->where('permission_key', $permissionKey)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }
}
