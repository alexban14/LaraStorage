<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show()
    {
        $folder = $this->getRoot();
        $files = File::query()->where('parent_id', $folder->id)
                              ->where('created_by', Auth::id())
                              ->orderBy('is_folder', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        $files = FileResource::collection($files);

        return Inertia::render('UserFiles', compact('files'));
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
}
