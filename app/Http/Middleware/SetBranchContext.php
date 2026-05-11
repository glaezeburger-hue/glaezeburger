<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;

class SetBranchContext
{
    /**
     * Set the current branch context for all queries.
     * 
     * - super_owner: Can switch branches via session/request param
     * - Other roles: Always use their assigned branch
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isSuperOwner()) {
            // Super owner can switch branches
            if ($request->has('switch_branch')) {
                $switchVal = $request->input('switch_branch');
                if ($switchVal === 'all') {
                    session([
                        'current_branch_id' => null,
                        'current_branch_name' => 'Semua Cabang',
                        'current_branch_code' => 'ALL'
                    ]);
                } else {
                    $branchId = (int) $switchVal;
                    $branch = Branch::where('id', $branchId)->where('is_active', true)->first();
                    if ($branch) {
                        session(['current_branch_id' => $branch->id]);
                        session(['current_branch_name' => $branch->name]);
                        session(['current_branch_code' => $branch->code]);
                    }
                }
                // Redirect to remove the query param
                return redirect()->to($request->url());
            }

            // Use session value or fallback to first active branch
            if (!session()->has('current_branch_id')) {
                $branch = Branch::where('is_active', true)->orderBy('id')->first();
                if ($branch) {
                    session([
                        'current_branch_id'   => $branch->id,
                        'current_branch_name' => $branch->name,
                        'current_branch_code' => $branch->code,
                    ]);
                }
            }
        } else {
            // Normal users: always scoped to their assigned branch
            session([
                'current_branch_id'   => $user->branch_id,
                'current_branch_name' => $user->branch?->name ?? 'Unknown',
                'current_branch_code' => $user->branch?->code ?? '???',
            ]);
        }

        // Share branch info with all views
        view()->share('currentBranch', Branch::find(session('current_branch_id')));
        view()->share('currentBranchId', session('current_branch_id'));
        view()->share('currentBranchName', session('current_branch_name'));

        // For super_owner: share all branches for switcher dropdown
        if ($user->isSuperOwner()) {
            view()->share('allBranches', Branch::where('is_active', true)->orderBy('name')->get());
        }

        return $next($request);
    }
}
