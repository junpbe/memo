<?php

namespace App\Policies;

use App\Models\Memo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MemoPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        // 管理者はすべて許可
        if ($user->isAdministrator()) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // 未使用
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Memo $memo): bool
    {
        // 自分のデータは許可
        if ($memo->user_id === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // 誰でも作成可能
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Memo $memo): bool
    {
        // 自分のデータは許可
        if ($memo->user_id === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Memo $memo): bool
    {
        // 自分のデータは許可
        if ($memo->user_id === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Memo $memo): bool
    {
        // 未使用
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Memo $memo): bool
    {
        // 未使用
        return false;
    }
}
