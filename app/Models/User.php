<?php

namespace App\Models;

// Import library yang dibutuhkan Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Penting untuk Token API Mobile nanti

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Daftar kolom yang boleh diisi secara otomatis (Create/Update).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username', // <-- Wajib ada agar tidak error Field 'username' doesn't have a default value
        'password',
        'role',     // <-- Wajib ada untuk membedakan admin/personel
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Kolom ini tidak akan muncul saat data di-convert ke JSON (untuk keamanan).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * Otomatis hash password saat disimpan.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Relasi ke tabel Personnels.
     * Relasi One-to-One: Satu User (akun) memiliki satu data Personnel (detail petugas).
     */
    public function personnel()
    {
        // Pastikan Model Personnel sudah ada
        return $this->hasOne(Personnel::class, 'user_id');
    }
}