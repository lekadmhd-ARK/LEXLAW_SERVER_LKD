<?php

namespace App\Http\Controllers;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkspaceDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $workspace = $request->route('workspace');
            if ($workspace->tenant_id !== $request->user()->tenant_id) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function store(Request $request, TeamWorkspace $workspace)
    {
        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'nullable|max:500',
            'category'    => 'nullable|in:' . implode(',', array_keys(WorkspaceDocument::CATEGORIES)),
            'file'        => 'required|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip,rar',
        ]);

        $uploaded = $request->file('file');
        $filename = time() . '_' . uniqid() . '.' . $uploaded->getClientOriginalExtension();
        $path     = $uploaded->storeAs("workspace-documents/{$workspace->id}", $filename, 'local');

        WorkspaceDocument::create([
            'workspace_id' => $workspace->id,
            'user_id'      => $request->user()->id,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'category'     => $validated['category'] ?? 'lainnya',
            'file_path'    => $path,
            'file_name'    => $uploaded->getClientOriginalName(),
            'file_mime'    => $uploaded->getMimeType(),
            'file_size'    => $uploaded->getSize(),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(TeamWorkspace $workspace, WorkspaceDocument $document)
    {
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(Request $request, TeamWorkspace $workspace, WorkspaceDocument $document)
    {
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
