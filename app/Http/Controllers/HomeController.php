<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Calendar;
use App\Services\RecurringEventService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct(
        protected RecurringEventService $recurringEventService
    ) {}

    public function index(Request $request)
    {
        // Get upcoming events from public calendars
        $upcomingEvents = Event::with(['calendar'])
            ->whereHas('calendar', function ($query) {
                $query->where('is_public', true);
            })
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime', 'asc')
            ->limit(3)
            ->get();

        // Calculate next occurrence for recurring events in upcoming list
        $upcomingEvents->transform(function ($event) {
            if ($event->recurrence_rule) {
                $instances = $this->recurringEventService->generateInstances(
                    $event->recurrence_rule,
                    $event->start_datetime,
                    now()->addYears(1),
                    100,
                    $event->cancelled_occurrences ?? []
                );
                
                foreach ($instances as $instance) {
                    if ($instance['start']->isFuture()) {
                        $event->display_datetime = $instance['start'];
                        break;
                    }
                }
                
                if (!isset($event->display_datetime)) {
                    $event->display_datetime = $event->start_datetime;
                }
            } else {
                $event->display_datetime = $event->start_datetime;
            }
            
            return $event;
        });

        // Get all events for the calendar view (next 3 months)
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonths(3)->endOfMonth();
        
        $events = Event::with(['calendar'])
            ->whereHas('calendar', function ($query) {
                $query->where('is_public', true);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                // Get events that start in this range OR are recurring
                $query->whereBetween('start_datetime', [$startDate, $endDate])
                      ->orWhereNotNull('recurrence_rule');
            })
            ->get();

        $calendarEvents = collect();
        
        foreach ($events as $event) {
            $duration = $event->start_datetime->diffInSeconds($event->end_datetime);
            
            if ($event->recurrence_rule) {
                // Generate recurring instances
                $instances = $this->recurringEventService->generateInstances(
                    $event->recurrence_rule,
                    $event->start_datetime,
                    $endDate,
                    100,
                    $event->cancelled_occurrences ?? []
                );
                
                foreach ($instances as $instance) {
                    $instanceStart = $instance['start'];
                    $instanceEnd = $instanceStart->copy()->addSeconds($duration);
                    
                    // Only include if within date range
                    if ($instanceStart->gte($startDate) && $instanceStart->lte($endDate)) {
                        // Cap end time to same day for display
                        if ($instanceStart->format('Y-m-d') !== $instanceEnd->format('Y-m-d')) {
                            $instanceEnd = $instanceStart->copy()->endOfDay();
                        }
                        
                        $calendarEvents->push([
                            'id' => $event->id . '-' . $instanceStart->timestamp,
                            'title' => $event->title,
                            'start' => $instanceStart->toISOString(),
                            'end' => $instanceEnd->toISOString(),
                            'calendar' => $event->calendar->name,
                            'url' => route('events.show', $event),
                        ]);
                    }
                }
            } else {
                // Single event
                if ($event->start_datetime->gte($startDate) && $event->start_datetime->lte($endDate)) {
                    $start = $event->start_datetime;
                    $end = $event->end_datetime;
                    
                    // Cap end time to same day for display
                    if ($start->format('Y-m-d') !== $end->format('Y-m-d')) {
                        $end = $start->copy()->endOfDay();
                    }
                    
                    $calendarEvents->push([
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $start->toISOString(),
                        'end' => $end->toISOString(),
                        'calendar' => $event->calendar->name,
                        'url' => route('events.show', $event),
                    ]);
                }
            }
        }

        return Inertia::render('Home', [
            'upcomingEvents' => $upcomingEvents,
            'calendarEvents' => $calendarEvents,
        ]);
    }
}
