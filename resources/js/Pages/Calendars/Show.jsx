import { Link, usePage, router, Head } from '@inertiajs/react';
import Layout from '../../Layouts/Layout';
import Button from '../../Components/Button';
import { format } from 'date-fns';
import DateTimeDisplay from '../../Components/DateTimeDisplay';

export default function Show({ calendar }) {
    const { auth } = usePage().props;

    const canEdit = auth.user?.permissions?.includes('edit-calendars') || 
                    calendar.created_by === auth.user?.id ||
                    auth.user?.roles?.includes('admin');

    const canDelete = auth.user?.permissions?.includes('delete-calendars') || 
                      calendar.created_by === auth.user?.id ||
                      auth.user?.roles?.includes('admin');

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this calendar? This will also delete all events in this calendar.')) {
            router.delete(`/calendars/${calendar.id}`);
        }
    };

    return (
        <>
            <Head title={calendar.name} />
            <Layout auth={auth}>
            <div className="mb-12">
                <div className="bg-white dark:bg-dark-bg-secondary" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                        <div className="flex justify-between items-center">
                            <div>
                                <h1 className="text-2xl font-semibold text-white">{calendar.name}</h1>
                                <div className="mt-1 flex items-center space-x-2">
                                    {!calendar.is_public && (
                                        <span className="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-warning text-white border-2 border-warning">
                                            PRIVATE
                                        </span>
                                    )}
                                    <span className="text-sm text-white opacity-75">
                                        by {calendar.creator?.name || 'Unknown'}
                                    </span>
                                </div>
                            </div>
                            {auth.user && (canEdit || canDelete) && (
                                <div className="flex space-x-3">
                                    {canEdit && (
                                        <Link href={`/calendars/${calendar.id}/edit`}>
                                            <Button variant="outline-light">Edit</Button>
                                        </Link>
                                    )}
                                    {canDelete && (
                                        <Button variant="outline-danger" onClick={handleDelete}>
                                            Delete
                                        </Button>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>

                    {calendar.description && (
                        <div className="px-6 py-4" style={{ borderBottom: '1px solid rgba(0, 0, 0, 0.1)' }}>
                            <p className="text-grey-700 dark:text-dark-text whitespace-pre-wrap">{calendar.description}</p>
                        </div>
                    )}

                    <div className="px-6 py-4">
                        <div className="flex justify-between items-center mb-4">
                            <h2 className="text-lg font-semibold text-secondary dark:text-primary">Events</h2>
                            {auth.user?.permissions?.includes('create-events') && (
                                <Link href={`/events/create?calendar_id=${calendar.id}`}>
                                    <Button variant="success">+ Create Event</Button>
                                </Link>
                            )}
                        </div>

                        {calendar.events && calendar.events.length > 0 ? (
                            <div>
                                {calendar.events.map((event, index) => {
                                    const isLast = index === calendar.events.length - 1;
                                    
                                    return (
                                        <div
                                            key={event.id}
                                            className="flex items-center py-4 hover:bg-grey-50 dark:hover:bg-dark-bg-tertiary transition-colors gap-6"
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
                                                    <DateTimeDisplay datetime={event.display_datetime || event.start_datetime} formatString="MMMM d, yyyy, HH:mm" />Z
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
                                <p className="text-grey-500 dark:text-dark-text-secondary">No events in this calendar yet.</p>
                                {auth.user?.permissions?.includes('create-events') && (
                                    <Link href={`/events/create?calendar_id=${calendar.id}`} className="mt-3 inline-block">
                                        <Button variant="primary">Create First Event</Button>
                                    </Link>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
        </>
    );
}
