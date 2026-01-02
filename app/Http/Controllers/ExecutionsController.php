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
        
        $query = Execution::with('flow')
            ->whereHas('flow', fn($q) => $q->where('shop_id', $shop->id));

        if ($request->has('flow_id')) {
            $query->where('flow_id', $request->flow_id);
        }

        $executions = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Executions/Index', [
            'executions' => $executions,
            'flow_id' => $request->flow_id
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
