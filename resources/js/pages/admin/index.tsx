import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

type Props = {
    stats: {
        users: number;
        companies: number;
        openJobs: number;
        newApplications: number;
    };
};

export default function AdminIndex({ stats }: Props) {
    const cards = [
        { label: 'Users', value: stats.users },
        { label: 'Companies', value: stats.companies },
        { label: 'Open job listings', value: stats.openJobs },
        { label: 'New applications', value: stats.newApplications },
    ];

    return (
        <>
            <Head title="Admin" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Platform overview"
                    description="Key numbers across the job board"
                />

                <div className="grid gap-4 md:grid-cols-4">
                    {cards.map((card) => (
                        <div
                            key={card.label}
                            data-test="admin-stat"
                            className="rounded-lg border p-4"
                        >
                            <div className="text-sm text-muted-foreground">
                                {card.label}
                            </div>
                            <div className="text-2xl font-semibold">
                                {card.value}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminIndex.layout = {
    breadcrumbs: [
        {
            title: 'Admin',
            href: '/admin',
        },
    ],
};
