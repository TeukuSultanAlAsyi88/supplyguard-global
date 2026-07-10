<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortImportBatch extends Model
{
    protected $fillable = [
        'user_id', 'filename', 'source', 'total_rows', 'imported_rows', 'skipped_rows', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
