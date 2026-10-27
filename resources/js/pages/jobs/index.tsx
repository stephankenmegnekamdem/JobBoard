import { Head } from '@inertiajs/react';
import JobCard from '@/components/jobs/job-card';
import type { Job } from '@/types/jobs';
const sample: Job[] = [
    {
        id: 1,
        title: 'Math Teacher',
        short_description: 'Teach algebra and geometry to grades 9–12.',
        location: 'Houston, TX',
        salary: '$55k',
        working_time: 'Full-time',
        published_at: null,
        company: { id: 1, name: 'Village Schools' },
    },
];
export default function JobsIndex() {
    return (
        <>
            <Head title="Jobs" />
            <h1 className="mb-6 text-2xl font-bold">Open positions</h1>
            <div className="grid gap-4 md:grid-cols-2">
                {sample.map((job) => (
                    <JobCard key={job.id} job={job} />
                ))}
            </div>
        </>
    );
}
