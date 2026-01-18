import { useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import Layout from '../../Layouts/Layout';
import Button from '../../Components/Button';
import Select from '../../Components/Select';

export default function DevLogin({ users }) {
    const { flash } = usePage().props;
    const [copiedLink, setCopiedLink] = useState(false);
    
    const { data, setData, post, processing } = useForm({
        email: users[0]?.email || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/dev/login-link', {
            preserveScroll: true,
        });
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
        setCopiedLink(true);
        setTimeout(() => setCopiedLink(false), 2000);
    };

    return (
        <Layout>
            <div className="max-w-2xl mx-auto">
                <div className="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-yellow-800">
                                Development Mode Only
                            </h3>
                            <p className="mt-1 text-sm text-yellow-700">
                                This login method is only available in local development environment. 
                                Login links expire after a configured time period.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="bg-white shadow rounded-lg p-6">
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">Development Login</h1>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label htmlFor="user" className="block text-sm font-medium text-gray-700 mb-1">
                                Select User
                            </label>
                            <Select
                                id="user"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                            >
                                {users.length === 0 && (
                                    <option>No users found - create one first</option>
                                )}
                                {users.map((user) => (
                                    <option key={user.id} value={user.email}>
                                        {user.name} ({user.email}) - {user.roles?.map(r => r.name).join(', ') || 'No role'}
                                    </option>
                                ))}
                            </Select>
                            <p className="mt-1 text-xs text-gray-500">
                                Select a user to generate a login link
                            </p>
                        </div>

                        <Button type="submit" disabled={processing || users.length === 0}>
                            Generate Login Link
                        </Button>
                    </form>

                    {flash?.loginLink && (
                        <div className="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                            <h3 className="text-sm font-medium text-green-800 mb-2">
                                Login Link Generated!
                            </h3>
                            <div className="flex items-start space-x-2">
                                <div className="flex-1 min-w-0">
                                    <p className="text-xs text-green-700 break-all font-mono bg-white p-2 rounded border border-green-200">
                                        {flash.loginLink}
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={() => copyToClipboard(flash.loginLink)}
                                    className="flex-shrink-0"
                                >
                                    {copiedLink ? 'Copied!' : 'Copy'}
                                </Button>
                            </div>
                            <div className="mt-3">
                                <a
                                    href={flash.loginLink}
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Click to Login
                                </a>
                            </div>
                        </div>
                    )}

                    <div className="mt-6 pt-6 border-t border-gray-200">
                        <h3 className="text-sm font-medium text-gray-900 mb-3">Create Test Users</h3>
                        <p className="text-sm text-gray-600 mb-4">
                            Use tinker to create test users with different roles:
                        </p>
                        <div className="bg-gray-50 p-4 rounded-md">
                            <pre className="text-xs text-gray-800 overflow-x-auto">
{`php artisan tinker

// Create admin user
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'vatsim_cid' => '1000001',
]);
$admin->assignRole('admin');

// Create moderator user
$mod = User::create([
    'name' => 'Moderator User',
    'email' => 'mod@example.com',
    'vatsim_cid' => '1000002',
]);
$mod->assignRole('moderator');

// Create regular user
$user = User::create([
    'name' => 'Regular User',
    'email' => 'user@example.com',
    'vatsim_cid' => '1000003',
]);
$user->assignRole('user');`}
                            </pre>
                        </div>
                    </div>

                    <div className="mt-6 pt-6 border-t border-gray-200">
                        <h3 className="text-sm font-medium text-gray-900 mb-3">Alternative: VATSIM OAuth</h3>
                        <p className="text-sm text-gray-600 mb-3">
                            You can also use the regular VATSIM OAuth login if configured:
                        </p>
                        <a
                            href="/auth/vatsim"
                            className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Login with VATSIM
                        </a>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
