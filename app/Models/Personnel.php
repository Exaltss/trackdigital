<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit (opsional, tapi disarankan)
    protected $table = 'personnels';

    // Kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nrp',
        'pangkat',
        'foto_profil',
        'status_aktif',       // offline, online, patroli, siaga, darurat
        'latitude',
        'longitude',
        'last_location_update',
    ];

    /**
     * Relasi ke Model User (Kebalikan dari hasOne di User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Model Report (Satu personel bisa punya banyak laporan)
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'personnel_id');
    }
}