<?php

namespace App\Http\Requests;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ParentIdBaseRequest extends FormRequest
{
    public ?File $parent = null;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->input('parent_id')) {
            $this->parent = File::where('id', $this->input('parent_id'))
                                         ->first();
        } else {
            $this->parent = File::where('created_by', Auth::id())->whereIsRoot()->first();
        }

        if ($this->parent && !$this->parent->isOwnedBy(Auth::id())) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => [
                Rule::exists(File::class, 'id')
                    ->where(function(Builder $query) {
                        return $query
                            ->where('is_folder', '=', '1')
                            ->where('created_by', '=', Auth::id());
                    })
            ]
        ];
    }
}
