<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Spinal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Str;

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

    protected $appends = [
        'owner',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function getOwnerAttribute(): string
    {
        return $this->created_by === Auth::id() ? 'me' : $this->user->name;
    }

    public function isOwnedBy($userId): bool
    {
        return $this->created_by = $userId;
    }

    public function isRoot()
    {
        return $this->parent_id === null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function(File $model) {
            Log::info('creating');
            if (!$model->parent) {
                Log::info('model does not have parent');
                return;
            }

            Log::info($model->parent);

            $parentPath = !$model->parent->isRoot() ? $model->parent->path . '/' : '';

            Log::info($parentPath);

            $model->path = $parentPath . Str::slug($model->name);
        });

    }
}
