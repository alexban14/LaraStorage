<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFileRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn($f) => $f != null);

        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectFolderName($paths)
        ]);
    }

    protected function passedValidation()
    {
        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths, $data['files'])
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function($attribute, $value, $fail) {
                    if (!$this->folder_name) {
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::where('name', $value->getClientOriginalName())
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            $fail('File "' . $value->getClientOriginalName() . '" already exists.');
                        }
                    }
                }
            ],
            'folder_name' => [
                'nullable',
                'string',
                function($attribute, $value, $fail) {
                    if ($value) {
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::where('name', $value)
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            $fail('Folder "' . $value . '" already exists.');
                        }
                    }
                }
            ]
        ]);
    }

    private function detectFolderName(array $paths) {
        if (!$paths) {
            return null;
        }

        $parts = explode('/', $paths[0]);

        return $parts[0];
    }

    private function buildFileTree(array $filePaths, array $files)
    {
        $filePaths = array_slice($filePaths, 0, count($files));
        $filePaths = array_filter($filePaths, fn($f) => $f != null);

        $tree = [];
        /*
         * [
         *      test => [
         *          1.jpg,
         *          ...etc
         *      ]
         * ]
         */

        foreach ($filePaths as $index => $filePath) {
            $parts = explode('/', $filePath);

            $currentNode = &$tree;

            foreach ($parts as $partIndex => $part) {
                if (!isset($currentNode[$part])) {
                    $currentNode[$part] = [];
                }

                if ($partIndex === count($parts) - 1 ) {
                    $currentNode[$part] = $files[$index];
                } else {
                    $currentNode = &$currentNode[$part];
                }
            }
        }

        return $tree;
    }
}
