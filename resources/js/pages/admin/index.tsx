import { Head } from '@inertiajs/react';
import { useState } from 'react';
import DataTable, { type ResourceConfig } from '@/components/admin/data-table';
import { Button } from '@/components/ui/button';

const resources: ResourceConfig[] = [
    {
        title: 'Jobs',
        endpoint: '/api/admin/jobs',
        columns: [
            'id',
            'title',
            'team_id',
            'location',
            'salary',
            'working_time',
        ],
        fields: [
            { name: 'team_id', label: 'Company ID', type: 'number' },
            { name: 'contact_id', label: 'Contact user ID', type: 'number' },
            { name: 'title', label: 'Title' },
            { name: 'short_description', label: 'Short description' },
            { name: 'description', label: 'Description', type: 'textarea' },
            { name: 'keywords', label: 'Keywords (comma separated)' },
            { name: 'location', label: 'Location' },
            { name: 'salary', label: 'Salary' },
            { name: 'working_time', label: 'Working time' },
            { name: 'link', label: 'External link' },
        ],
    },
    {
        title: 'Companies',
        endpoint: '/api/admin/companies',
        columns: ['id', 'name', 'slug', 'is_personal', 'location', 'website'],
        fields: [
            { name: 'name', label: 'Name' },
            { name: 'location', label: 'Location' },
            { name: 'website', label: 'Website' },
            { name: 'description', label: 'Description', type: 'textarea' },
        ],
    },
    {
        title: 'Users',
        endpoint: '/api/admin/users',
        columns: ['id', 'name', 'last_name', 'email', 'is_admin'],
        fields: [
            { name: 'name', label: 'First name' },
            { name: 'last_name', label: 'Last name' },
            { name: 'email', label: 'Email', type: 'email' },
            {
                name: 'password',
                label: 'Password (leave blank to keep the current one)',
                type: 'password',
            },
            { name: 'tel_num', label: 'Phone' },
            { name: 'is_admin', label: 'Administrator', type: 'checkbox' },
        ],
    },
    {
        title: 'Applications',
        endpoint: '/api/admin/applications',
        columns: [
            'id',
            'job_listing_id',
            'name',
            'email',
            'status',
            'created_at',
        ],
        fields: [
            { name: 'job_listing_id', label: 'Job ID', type: 'number' },
            { name: 'name', label: 'Name' },
            { name: 'email', label: 'Email', type: 'email' },
            { name: 'tel_num', label: 'Phone' },
            { name: 'message', label: 'Message', type: 'textarea' },
            {
                name: 'status',
                label: 'Status (new, reviewed, rejected, hired)',
            },
        ],
    },
];

export default function AdminIndex() {
    const [active, setActive] = useState(0);

    return (
        <div className="flex flex-col gap-4 p-4">
            <Head title="Admin" />
            <div className="flex flex-wrap gap-2">
                {resources.map((resource, index) => (
                    <Button
                        key={resource.title}
                        variant={index === active ? 'default' : 'outline'}
                        onClick={() => setActive(index)}
                    >
                        {resource.title}
                    </Button>
                ))}
            </div>
            <DataTable
                key={resources[active].endpoint}
                config={resources[active]}
            />
        </div>
    );
}

AdminIndex.layout = {
    breadcrumbs: [{ title: 'Admin', href: '/admin' }],
};
