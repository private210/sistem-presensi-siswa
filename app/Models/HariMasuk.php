<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'bulan',
        'tahun',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
