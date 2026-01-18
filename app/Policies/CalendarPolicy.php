<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    /**
     * Determine whether the user can view any calendars
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view public calendars
    }

    /**
     * Determine whether the user can view the calendar
     */
    public function view(?User $user, Calendar $calendar): bool
    {
        // Public calendars are visible to everyone
        if ($calendar->is_public) {
            return true;
        }

        // Private calendars require authentication
        if (!$user) {
            return false;
        }

        // Admin can see all calendars
        if ($user->hasRole('admin')) {
            return true;
        }

        // Creator can see their own calendar
        return $calendar->created_by === $user->id;
    }

    /**
     * Determine whether the user can create calendars
     */
    public function create(User $user): bool
    {
        // Only admins can create calendars
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the calendar
     */
    public function update(User $user, Calendar $calendar): bool
    {
        // Only admins can edit calendars
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the calendar
     */
    public function delete(User $user, Calendar $calendar): bool
    {
        // Only admins can delete calendars
        return $user->hasRole('admin');
    }
}
