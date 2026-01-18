import { Link, usePage, Head } from '@inertiajs/react';
import Layout from '../../Layouts/Layout';
import Button from '../../Components/Button';
import { formatInTimeZone } from 'date-fns-tz';

export default function Index({ events, filters }) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="All Events" />
            <Layout auth={auth}>
                <div className="mb-12">
                <div className="bg-white dark:bg-dark-bg-secondary" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-semibold text-white">All Events</h1>
                            {auth.user?.permissions?.includes('create-events') && (
                                <Link href="/events/create">
                                    <Button variant="success">+ Create</Button>
                                </Link>
                            )}
                        </div>
                    </div>

                    {events.data.length > 0 ? (
                        <div>
                            {events.data.map((event, index) => {
                                // Use display_datetime if available (for recurring events), otherwise use start_datetime
                                const displayDate = event.display_datetime || event.start_datetime;
                                const zuluDate = formatInTimeZone(new Date(displayDate), 'UTC', 'MMMM d, yyyy');
                                const zuluTime = formatInTimeZone(new Date(displayDate), 'UTC', 'HH:mm');
                                const isLast = index === events.data.length - 1;
                                
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
                                            <div className="flex items-center gap-2 mb-1">
                                                <h3 className="text-lg font-bold text-secondary dark:text-primary">
                                                    {event.title}
                                                </h3>
                                                {event.recurrence_rule && (
                                                    <span className="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-secondary text-white border-2 border-secondary">
                                                        RECURRING
                                                    </span>
                                                )}
                                            </div>
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
                            <p className="text-grey-500 dark:text-dark-text-secondary">No events found</p>
                        </div>
                    )}
                </div>
            </div>

            {events.links && events.links.length > 3 && (
                <div className="flex justify-center space-x-2">
                    {events.links.map((link, index) => (
                        <Link
                            key={index}
                            href={link.url || '#'}
                            className={`px-3 py-2 text-sm border-2 ${
                                link.active
                                    ? 'bg-secondary text-white border-secondary'
                                    : 'bg-white dark:bg-dark-bg-secondary text-gray-700 dark:text-dark-text border-grey-200 dark:border-dark-border hover:border-secondary'
                            }`}
                            style={!link.active ? { boxShadow: 'var(--shadow-card)' } : {}}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
                )}
            </Layout>
        </>
    );
}
