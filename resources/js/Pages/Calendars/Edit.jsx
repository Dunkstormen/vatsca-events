import { useForm, usePage, Head } from '@inertiajs/react';
import Layout from '../../Layouts/Layout';
import Button from '../../Components/Button';
import Input from '../../Components/Input';
import Textarea from '../../Components/Textarea';

export default function Edit({ calendar }) {
    const { auth } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        name: calendar.name || '',
        description: calendar.description || '',
        is_public: calendar.is_public,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/calendars/${calendar.id}`);
    };

    return (
        <>
            <Head title={`Edit ${calendar.name}`} />
            <Layout auth={auth}>
                <div className="max-w-3xl mx-auto">
                <div>
                    <div className="bg-white dark:bg-dark-bg-secondary">
                        <div className="bg-secondary dark:bg-dark-bg-tertiary px-6 py-4">
                            <h1 className="text-2xl font-semibold text-white">Edit Calendar</h1>
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-dark-bg-secondary p-6" style={{ boxShadow: 'var(--shadow-card)' }}>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-dark-text mb-1">
                                Calendar Name *
                            </label>
                            <Input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                error={errors.name}
                                required
                            />
                        </div>

                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-dark-text mb-1">
                                Description
                            </label>
                            <Textarea
                                id="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                error={errors.description}
                                rows={4}
                            />
                        </div>

                        <div>
                            <label className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={data.is_public}
                                    onChange={(e) => setData('is_public', e.target.checked)}
                                    className="rounded border-gray-300 text-secondary dark:text-primary focus:ring-secondary"
                                />
                                <span className="ml-2 text-sm text-gray-700 dark:text-dark-text">
                                    Public calendar (visible to everyone)
                                </span>
                            </label>
                        </div>

                        <div className="flex justify-end space-x-3">
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => window.history.back()}
                            >
                                Cancel
                            </Button>
                            <Button variant="primary" type="submit" disabled={processing}>
                                Update Calendar
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
        </>
    );
}
