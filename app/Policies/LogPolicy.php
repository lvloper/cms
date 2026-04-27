<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any logs.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        dd('boot');
        // Allows Filament Shield to generate a permission like 'view_any_log_policy'
        // This generally corresponds to the main page access for FilamentLaravelLogPlugin
        return $user->can('view_any_log_policy');
    }

    /**
     * Determine whether the user can view a specific log entry.
     * (This might not be directly used by the plugin's main page, 
     * but good practice to include if detailed views are possible)
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $user): bool
    {
        // Allows Filament Shield to generate a permission like 'view_log_policy'
        // This might be for viewing individual log entries if supported
        return $user->can('view_log_policy');
    }
}
