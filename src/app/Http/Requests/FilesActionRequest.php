<?php

namespace App\Http\Requests;

use App\Models\File;
use App\Models\FileShare;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FilesActionRequest extends ParentIdBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'all' => 'nullable|bool',
            'ids.*' => function ($attribute, $value, $fail) {
                $fileOwnedByUser = File::query()
                    ->where('id', $value)
                    ->where('created_by', Auth::id())
                    ->exists();

                if (!$fileOwnedByUser) {
                    $fileSharedWithUser = FileShare::query()
                        ->where('file_id', $value)
                        ->where('user_id', Auth::id())
                        ->exists();

                    if (!$fileSharedWithUser) {
                        $fail('The selected file is either not yours or not shared with you.');
                    }
                }
            },
        ]);
    }
}
