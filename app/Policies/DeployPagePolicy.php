<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeployPagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Allows Filament Shield to generate a permission like 'view_any_deploy_page_policy'
        return $user->can('view_any_deploy_page_policy');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $user): bool
    {
        // Allows Filament Shield to generate a permission like 'view_deploy_page_policy'
        return $user->can('view_deploy_page_policy');
    }
}
