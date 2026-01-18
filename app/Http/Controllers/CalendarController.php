<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    /**
     * Display a listing of calendars
     */
    public function index(Request $request)
    {
        // Only moderators, admins and users with calendar/event management permissions can access
        if (!$request->user() || 
            !($request->user()->hasRole('admin') || 
              $request->user()->hasRole('moderator') ||
              $request->user()->hasAnyPermission(['manage-calendars', 'manage-events', 'create-calendars', 'edit-calendars']))) {
            abort(403, 'Unauthorized access to calendar management.');
        }

        $calendars = Calendar::with('creator')
            ->visibleTo($request->user())
            ->latest()
            ->paginate(12);

        return Inertia::render('Calendars/Index', [
            'calendars' => $calendars,
        ]);
    }

    /**
     * Show the form for creating a new calendar
     */
    public function create()
    {
        $this->authorize('create', Calendar::class);

        return Inertia::render('Calendars/Create');
    }

    /**
     * Store a newly created calendar
     */
    public function store(Request $request)
    {
        $this->authorize('create', Calendar::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);

        $calendar = Calendar::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        \Log::info('Calendar "' . $calendar->name . '" (' . $calendar->id . ') created by user: ' . $request->user()->vatsim_cid);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar created successfully.');
    }

    /**
     * Display the specified calendar
     */
    public function show(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        $calendar->load('creator', 'events');

        return Inertia::render('Calendars/Show', [
            'calendar' => $calendar,
        ]);
    }

    /**
     * Show the form for editing the calendar
     */
    public function edit(Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        return Inertia::render('Calendars/Edit', [
            'calendar' => $calendar,
        ]);
    }

    /**
     * Update the specified calendar
     */
    public function update(Request $request, Calendar $calendar)
    {
        $this->authorize('update', $calendar);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'required|boolean',
        ]);

        $calendar->update($validated);

        \Log::info('Calendar "' . $calendar->name . '" (' . $calendar->id . ') updated by user: ' . auth()->user()->vatsim_cid);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar updated successfully.');
    }

    /**
     * Remove the specified calendar
     */
    public function destroy(Calendar $calendar)
    {
        $this->authorize('delete', $calendar);

        $calendar->delete();

        \Log::info('Calendar "' . $calendar->name . '" (' . $calendar->id . ') deleted by user: ' . auth()->user()->vatsim_cid);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendar deleted successfully.');
    }
}
