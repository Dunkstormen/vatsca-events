import { Link, usePage, Head } from '@inertiajs/react';
import Layout from '../../Layouts/Layout';
import Button from '../../Components/Button';

export default function Index({ calendars }) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Calendars" />
            <Layout auth={auth}>
            <div className="mb-12">
                <div className="bg-white dark:bg-dark-bg-secondary" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-semibold text-white">Calendars</h1>
                            {auth.user?.permissions?.includes('create-calendars') && (
                                <Link href="/calendars/create">
                                    <Button variant="success">+ Create</Button>
                                </Link>
                            )}
                        </div>
                    </div>

                    {calendars.data.length > 0 ? (
                        <div>
                            {calendars.data.map((calendar, index) => {
                                const isLast = index === calendars.data.length - 1;
                                
                                return (
                                    <div 
                                        key={calendar.id} 
                                        className="flex items-center p-6 hover:bg-grey-50 dark:hover:bg-dark-bg-tertiary transition-colors"
                                        style={{ 
                                            borderBottom: isLast ? 'none' : '1px solid rgba(0, 0, 0, 0.1)'
                                        }}
                                    >
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2 mb-1">
                                                <h3 className="text-lg font-bold text-secondary dark:text-primary">
                                                    {calendar.name}
                                                </h3>
                                                {!calendar.is_public && (
                                                    <span className="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-warning text-white border-2 border-warning">
                                                        PRIVATE
                                                    </span>
                                                )}
                                            </div>
                                            {calendar.description && (
                                                <p className="text-sm text-grey-600 dark:text-dark-text-secondary line-clamp-2">
                                                    {calendar.description}
                                                </p>
                                            )}
                                            <div className="text-xs text-grey-500 dark:text-dark-text-secondary mt-2">
                                                Created by {calendar.creator?.name || 'Unknown'}
                                            </div>
                                        </div>
                                        <div className="flex-shrink-0">
                                            <Link 
                                                href={`/calendars/${calendar.id}`}
                                                className="inline-block px-4 py-2 bg-primary text-white hover:bg-primary-600 font-medium text-sm transition-colors"
                                            >
                                                View Calendar
                                            </Link>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-grey-500 dark:text-dark-text-secondary">No calendars found.</p>
                        </div>
                    )}

                    {calendars.links && calendars.links.length > 3 && (
                        <div className="border-t border-grey-200 dark:border-dark-border px-6 py-4">
                            <div className="flex justify-center space-x-2">
                                {calendars.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`px-3 py-2 text-sm font-medium border-2 transition-colors ${
                                            link.active
                                                ? 'bg-secondary text-white border-secondary'
                                                : 'bg-white text-grey-700 border-grey-300 hover:border-secondary'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </Layout>
        </>
    );
}
