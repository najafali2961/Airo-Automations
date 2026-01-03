<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Execution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ExecutionsController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user();
        
        $queryBuilder = Execution::with('flow')
            ->whereHas('flow', fn($q) => $q->where('shop_id', $shop->id));

        if ($request->filled('flow_id')) {
            $queryBuilder->where('flow_id', $request->flow_id);
        }

        if ($request->filled('status')) {
            $queryBuilder->where('status', $request->status);
        }

        if ($request->filled('query')) {
            $s = $request->input('query');
            $queryBuilder->where(function($q) use ($s) {
                $q->where('event', 'like', "%{$s}%")
                  ->orWhereHas('flow', fn($fq) => $fq->where('name', 'like', "%{$s}%"));
            });
        }

        $sort = $request->input('sort');
        if (!is_string($sort) || !str_contains($sort, ' ')) {
            $sort = 'created_at desc';
        }

        [$column, $direction] = explode(' ', $sort);
        $queryBuilder->orderBy($column, $direction);

        $executions = $queryBuilder->paginate(15)->withQueryString();

        return Inertia::render('Executions/Index', [
            'executions' => $executions,
            'flow_id' => $request->flow_id,
            'filters' => $request->only(['query', 'status', 'sort'])
        ]);
    }

    public function show($id)
    {
        $shop = Auth::user();
        
        $execution = Execution::with(['flow', 'logs' => fn($q) => $q->orderBy('id', 'asc')])
            ->whereHas('flow', fn($q) => $q->where('shop_id', $shop->id))
            ->findOrFail($id);

        return Inertia::render('Executions/Show', [
            'execution' => $execution
        ]);
    }
}
