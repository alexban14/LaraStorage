<?php

namespace App\Models;

use App\Traits\HasCreatorAndUpdater;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        'storage_path',
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

    public function get_file_size()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = $this->size > 0 ? floor(log($this->size, 1024)) : 0;

        return number_format($this->size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function(File $model) {
            if (!$model->parent) {
                Log::info('model does not have parent');
                return;
            }

            $parentPath = !$model->parent->isRoot() ? $model->parent->path . '/' : '';

            $model->path = $parentPath . Str::slug($model->name);
        });

//        static::deleted(function(File $model) {
//            if (!$model->is_folder) {
//                Storage::delete($model->storage_path);
//            }
//        });
    }

    public function moveToTrash(): bool
    {
        $this->deleted_at = Carbon::now();

        return $this->save();
    }
}
