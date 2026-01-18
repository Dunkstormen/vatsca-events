import { Link, usePage, Head } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import { Calendar, momentLocalizer } from 'react-big-calendar';
import moment from 'moment';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { formatInTimeZone } from 'date-fns-tz';
import { format } from 'date-fns';

// Set Monday as first day of week
moment.updateLocale('en', {
    week: {
        dow: 1, // Monday is the first day of the week
        doy: 4  // The week that contains Jan 4th is the first week of the year
    }
});
const localizer = momentLocalizer(moment);

export default function Home({ upcomingEvents, calendarEvents }) {
    const { auth } = usePage().props;

    // Convert events for react-big-calendar
    const events = calendarEvents.map(event => ({
        ...event,
        start: new Date(event.start),
        end: new Date(event.end),
    }));

    const eventStyleGetter = () => {
        return {
            style: {
                backgroundColor: '#2d5266',
                borderColor: '#2d5266',
                color: 'white',
            }
        };
    };

    return (
        <>
            <Head title="Home" />
            <Layout auth={auth}>

                {/* Upcoming Events Section */}
            <div className="mb-12">
                <div className="bg-white dark:bg-dark-bg-secondary" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                        <h2 className="text-2xl font-semibold text-white">Upcoming Events</h2>
                    </div>
                    
                    {upcomingEvents.length > 0 ? (
                        <div>
                            {upcomingEvents.map((event, index) => {
                                // Use display_datetime if available (for recurring events), otherwise use start_datetime
                                const displayDate = event.display_datetime || event.start_datetime;
                                const zuluDate = formatInTimeZone(new Date(displayDate), 'UTC', 'MMMM d, yyyy');
                                const zuluTime = formatInTimeZone(new Date(displayDate), 'UTC', 'HH:mm');
                                const isLast = index === upcomingEvents.length - 1;
                                
                                return (
                                    <div 
                                        key={event.id} 
                                        className="flex items-center p-6 hover:bg-grey-50 dark:hover:bg-dark-bg-tertiary transition-colors gap-6"
                                        style={{ 
                                            borderBottom: isLast ? 'none' : '1px solid rgba(0, 0, 0, 0.1)'
                                        }}
                                    >
                                        {event.banner_path && (
                                            <div className="w-32 flex-shrink-0" style={{ aspectRatio: '16/9' }}>
                                                <img
                                                    src={`/storage/${event.banner_path}`}
                                                    alt={event.title}
                                                    className="w-full h-full object-cover"
                                                />
                                            </div>
                                        )}
                                        <div className="flex-1 min-w-0">
                                            <h3 className="text-lg font-bold text-secondary dark:text-primary mb-1">
                                                {event.title}
                                            </h3>
                                            <div className="text-sm text-grey-600 dark:text-dark-text-secondary">
                                                {zuluDate}, {zuluTime}Z
                                            </div>
                                        </div>
                                        <div className="flex-shrink-0">
                                            <Link 
                                                href={`/events/${event.id}`}
                                                className="inline-block px-4 py-2 bg-primary text-white hover:bg-primary-600 font-medium text-sm transition-colors"
                                            >
                                                View Event
                                            </Link>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-grey-500 dark:text-dark-text-secondary">No upcoming events scheduled</p>
                        </div>
                    )}
                </div>
            </div>

            {/* Calendar View Section */}
            <div className="mb-12">
                <div className="bg-white dark:bg-dark-bg-secondary" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                        <h2 className="text-2xl font-semibold text-white">Event Calendar</h2>
                    </div>
                    <div className="p-6">
                        <div style={{ height: '800px' }}>
                            <Calendar
                                localizer={localizer}
                                events={events}
                                startAccessor="start"
                                endAccessor="end"
                                style={{ height: '100%' }}
                                eventPropGetter={eventStyleGetter}
                                onSelectEvent={(event) => window.location.href = event.url}
                                views={['month', 'week', 'day']}
                                defaultView="month"
                                culture="en-GB"
                                titleAccessor={(event) => {
                                    const startTime = formatInTimeZone(event.start, 'UTC', 'HH:mm');
                                    return `${startTime}Z ${event.title}`;
                                }}
                                formats={{
                                    eventTimeRangeFormat: () => null, // Hide default time range
                                }}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Footer CTA */}
            <div className="text-center py-12 bg-snow dark:bg-dark-bg-tertiary">
                <h3 className="text-2xl font-bold text-secondary dark:text-primary mb-4">Want to see all events?</h3>
                <Link href="/events" className="inline-block px-8 py-3 bg-primary text-white border-2 border-primary hover:bg-primary-600 font-semibold">
                    Browse All Events
                </Link>
            </div>
            </Layout>
        </>
    );
}
