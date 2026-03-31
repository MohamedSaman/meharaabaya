<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\StaffPermission;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Allow admin to access everything
        if (auth()->user() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Check if staff user has the required permission
        if (auth()->user() && auth()->user()->role === 'staff') {
            // If staff has no permissions assigned, grant full access by default
            $hasAnyPermissions = StaffPermission::where('user_id', auth()->id())->exists();
            if (!$hasAnyPermissions) {
                return $next($request); // Full access when no permissions are set
            }

            // If permissions are assigned, check for specific permission
            if (StaffPermission::hasPermission(auth()->id(), $permission)) {
                return $next($request);
            }
        }

        // Redirect with error message if permission is denied
        session()->flash('error', 'You do not have permission to access this page.');
        return redirect()->route('staff.dashboard');
    }
}
