<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilesActionRequest;
use App\Http\Requests\ShareFilesRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\TrashFilesRequest;
use App\Http\Resources\FileResource;
use App\Mail\ShareFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use function Laravel\Prompts\warning;

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
            foreach ($data['files'] as $file) {
                $this->storeFile($file, $user, $parent);
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeFolder(StoreFolderRequest $request): void
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
    public function showFiles(Request $request, ?string $folderPath = null): Response|AnonymousResourceCollection
    {
        if ($folderPath) {
            $folder = File::where('created_by', Auth::id())
                ->where('path', $folderPath)
                ->firstOrFail();
        } else {
            $folder = $this->getRoot();
        }

        $favorites = (int)$request->get('favorites', 0);


        $query = File::query()->select('files.*')
            ->with(['user', 'starred'])
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('files.created_at', 'desc')
            ->orderBy('files.id', 'desc');


        if ($favorites === 1) {
            $query->join('starred_files', 'starred_files.file_id', '=', 'files.id')
                ->where('starred_files.user_id', Auth::id());
        }

        $files = $query->paginate(10);

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
    public function destroy(FilesActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $parent = $request->parent;

        if ($data['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach ($data['ids'] ?? [] as $id) {
                $file = File::whereId($id)->first();
                if ($file) {
                    $file->moveToTrash();
                }
            }
        }

        return to_route('user-files', ['folder' => $parent->path]);
    }

    public function trash(Request $request): Response
    {
        $files = File::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        return Inertia::render('Trash', compact('files'));
    }

    public function restore(TrashFilesRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $children = File::onlyTrashed()->whereIn('id', $data['ids'])->get();
            foreach ($children as $child) {
                $child->restore();
            }
        }

        return to_route('trash');
    }

    public function deleteForever(TrashFilesRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $children = File::onlyTrashed()->whereIn('id', $data['ids'])->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        }

        return to_route('trash');
    }

    public function download(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download',
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name . '.zip';
        } else {
            if (count($ids) === 1) {
                $file = File::find($ids[0]);
                if ($file->is_folder) {
                    if ($file->children->count() === 0) {
                        return [
                            'message' => 'The folder is empty'
                        ];
                    }
                    $url = $this->createZip($file->children);
                    $filename = $parent->name . '.zip';
                } else {
                    $destination = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $destination);

                    $url = asset(Storage::url($destination));
                    $filename = $file->name;
                }
            } else {
                $files = File::whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $parent->name . '.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    public function markFavorite(Request $request, File $file): RedirectResponse
    {
        $data = [];
        if ($file->is_folder) {
            $children = $file->children;

            foreach ($children as $child) {
                $starredFile = StarredFile::where('file_id', $child->id)
                    ->where('user_id', Auth::id())
                    ->first();

                if ($starredFile) {
                    $starredFile->destory();
                    continue;
                }

                $data[] = [
                    'file_id' => $child->id,
                    'user_id' => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            StarredFile::insert($data);
        } else {
            $starredFile = StarredFile::where('file_id', $file->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($starredFile) {
                $starredFile->delete();
            } else {
                StarredFile::create([
                    'file_id' => $file->id,
                    'user_id' => Auth::id(),
                ]);
            }
        }

        return redirect()->back();
    }

    public function share(ShareFilesRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $email = $data['email'] ?? '';
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to share',
            ];
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->back();
        }

        if ($all) {
            $files = $parent->children();
        } else {
            $files = File::whereIn('id', $ids)->get();
        }

        $existingFileIds = FileShare::query()
            ->whereIn('file_id', $ids)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('file_id');

        foreach ($files as $file) {
            if ($existingFileIds->has($file->id)) {
                continue;
            }

            $insertData[] = [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (!empty($insertData)) {
            FileShare::insert($insertData);

            Mail::to($user->email)->send(new ShareFilesMail($user, Auth::user(), $files->toArray()));
        }

        return redirect()->back();
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    private function saveFileTree(array $fileTree, File $parent, User $user)
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
    private function storeFile(UploadedFile $file, User $user, File $parent)
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

    private function createZip($files): string
    {
        $zipPath = 'zip/' . Str::random() . '.zip';
        $publicPath = "public/$zipPath";

        if (!is_dir(dirname($publicPath))) {
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE === true)) {
             $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
            }
        }
    }

}
