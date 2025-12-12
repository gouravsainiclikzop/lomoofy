<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Policy for capability-based authorization
 * 
 * All resource policies should extend this class.
 * It provides standard CRUD permission checks using the capability pattern.
 */
abstract class BasePolicy
{
    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'view');
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'view');
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'create');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'update');
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'delete');
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'restore');
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $user->canDo($this->getModule(), $this->getResource(), 'forceDelete');
    }

    /**
     * Get the module name for this policy.
     * Override in child classes if different from resource.
     * 
     * @return string
     */
    protected function getModule(): string
    {
        return $this->getResource();
    }

    /**
     * Get the resource name for this policy.
     * Must be implemented by child classes.
     * 
     * @return string
     */
    abstract protected function getResource(): string;
}

