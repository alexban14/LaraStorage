<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class File extends Model
{
    use HasFactory;
    use NodeTrait;
    use SoftDeletes;
    use HasCreatorAndUpdater;

    protected $fillable = [
        'name',
        'is_folder',
        'path',
        'mime',
        'size',
        'created_by',
        'updated_by',
    ];

    public function isOwnedBy($userId): bool
    {
        return $this->created_by = $userId;
    }
}
