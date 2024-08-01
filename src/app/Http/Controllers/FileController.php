<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();

        $parent = $request->parent;
        $user = $request->user();
        $fileTree = $request->file_tree;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        if (!empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            /**
             * @var \Illuminate\Http\UploadedFile $file
             */
            foreach($data['files'] as $file) {
                $this->storeFile($file, $user, $parent);
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();

        $parent = $request->parent ?? $this->getRoot();

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];
        $parent->appendNode($file);
        $file->save();
    }

    /**
     * Display the specified resource.
     */
    public function showFiles(Request $request, ?string $folderPath = null)
    {
        if ($folderPath) {
            $folder = File::where('created_by', Auth::id())
                ->where('path', $folderPath)
                ->firstOrFail();
        } else {
            $folder = $this->getRoot();
        }

        $files = File::query()->where('parent_id', $folder->id)
                              ->where('created_by', Auth::id())
                              ->orderBy('is_folder', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);
                              /* ->paginate(10); */

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('UserFiles', compact('files', 'folder', 'ancestors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    public function saveFileTree(array $fileTree, File $parent, User $user)
    {
        foreach ($fileTree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = 1;
                $folder->name = $name;

                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->storeFile($file, $user, $parent);
            }
        }
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param \App\Models\User $user
     * @param \App\Models\File $parent
     */
    public function storeFile(UploadedFile $file, User $user, File $parent)
    {
        $path = $file->store('/files/' . $user->id);

        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $parent->appendNode($model);
    }
}
