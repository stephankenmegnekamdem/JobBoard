import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import JobCard from '@/components/jobs/job-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { apiFetch } from '@/lib/api';
import type { Job, Paginated } from '@/types/jobs';
export default function JobsIndex() {
    const [page, setPage] = useState(1);
    const [query, setQuery] = useState('');
    const [result, setResult] = useState<Paginated<Job> | null>(null);

    useEffect(() => {
        const params = new URLSearchParams({ page: String(page) });

        if (query) {
            params.set('q', query);
        }
        void apiFetch(`/api/jobs?${params.toString()}`)
            .then((res) => res.json())
            .then((data: Paginated<Job>) => setResult(data));
    }, [page, query]);
    return (
        <>
            <Head title="Jobs" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Open positions</h1>
                <Input
                    id="search"
                    placeholder="Search title or keywords"
                    className="max-w-xs"
                    value={query}
                    onChange={(event) => {
                        setPage(1);
                        setQuery(event.target.value);
                    }}
                />
            </div>
            {!result && <p>Loading…</p>}
            {result && result.data.length === 0 && <p>No jobs match.</p>}
            <div className="grid gap-4 md:grid-cols-2">
                {result?.data.map((job) => (
                    <JobCard key={job.id} job={job} />
                ))}
            </div>
            {result && result.meta.last_page > 1 && (
                <div className="mt-6 flex items-center gap-3">
                    <Button
                        variant="outline"
                        disabled={page <= 1}
                        onClick={() => setPage(page - 1)}
                    >
                        Previous
                    </Button>
                    <span className="text-sm">
                        Page {result.meta.current_page} of{' '}
                        {result.meta.last_page}
                    </span>
                    <Button
                        variant="outline"
                        disabled={page >= result.meta.last_page}
                        onClick={() => setPage(page + 1)}
                    >
                        Next
                    </Button>
                </div>
            )}
        </>
    );
}
