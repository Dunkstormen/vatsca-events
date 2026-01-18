<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events
     */
    public function viewAny(?User $user): bool
    {
        return true; // Events in public calendars are visible to everyone
    }

    /**
     * Determine whether the user can view the event
     */
    public function view(?User $user, Event $event): bool
    {
        // Check calendar visibility
        $calendar = $event->calendar;

        if ($calendar->is_public) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $calendar->created_by === $user->id;
    }

    /**
     * Determine whether the user can create events
     */
    public function create(User $user): bool
    {
        // Admins and moderators can create events
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can update the event
     */
    public function update(User $user, Event $event): bool
    {
        // Admins and moderators can edit any event
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }

    /**
     * Determine whether the user can delete the event
     */
    public function delete(User $user, Event $event): bool
    {
        // Admins and moderators can delete any event
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }
}
