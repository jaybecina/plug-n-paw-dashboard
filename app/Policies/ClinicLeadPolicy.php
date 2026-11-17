<?php

namespace App\Policies;

use App\Models\ClinicLead;
use App\Models\User;

class ClinicLeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, ClinicLead $lead): bool
    {
        return $user->is_admin;
    }
}
