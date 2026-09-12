<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $q      = $request->query('q', '');
        $type   = $request->query('type', null);
        $wsId   = $request->query('workspace_id');
        $results = collect();

        if ($q && $wsId) {
            $workspace = \App\Models\TeamWorkspace::where('tenant_id', $request->user()->tenant_id)->find($wsId);
            if ($workspace && $workspace->members()->where('user_id', $request->user()->id)->exists()) {
                $results = $search->searchWorkspace($workspace, $q, $type);
            }
        } elseif ($q) {
            $workspaces = \App\Models\TeamWorkspace::where('tenant_id', $request->user()->tenant_id)
                ->whereHas('members', fn($m) => $m->where('user_id', $request->user()->id))
                ->get();

            foreach ($workspaces as $ws) {
                $results = $results->merge($search->searchWorkspace($ws, $q, $type));
            }
            $results = $results->sortByDesc('date')->take(30);
        }

        return view('search.index', [
            'q'       => $q,
            'type'    => $type,
            'results' => $results,
        ]);
    }
}
