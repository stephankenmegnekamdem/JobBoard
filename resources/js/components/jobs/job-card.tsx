import { useState } from 'react';
import ApplyForm from '@/components/jobs/apply-form';
import JobDetails from '@/components/jobs/job-details';
import { Button } from '@/components/ui/button';
import { apiFetch } from '@/lib/api';
import type { Job, JobDetail } from '@/types/jobs';

export default function JobCard({ job }: { job: Job }) {
    const [detail, setDetail] = useState<JobDetail | null>(null);
    const [showDetail, setShowDetail] = useState(false);
    const [showApply, setShowApply] = useState(false);
    async function learnMore() {
        if (!detail) {
            const res = await apiFetch(`/api/jobs/${job.id}`);
            const json = (await res.json()) as { data: JobDetail };
            setDetail(json.data);
        }
        setShowDetail((open) => !open);
    }
    return (
        <article className="flex flex-col gap-3 rounded-lg border p-4">
            <div>
                <h2 className="text-lg font-semibold">{job.title}</h2>
                <p className="text-sm text-muted-foreground">
                    {job.company.name}
                    {job.location && ` · ${job.location}`}
                    {job.working_time && ` · ${job.working_time}`}
                </p>
            </div>
            <p className="text-sm">{job.short_description}</p>
            <div className="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => void learnMore()}
                >
                    {showDetail ? 'Show less' : 'Learn more'}
                </Button>
                <Button size="sm" onClick={() => setShowApply((open) => !open)}>
                    Apply
                </Button>
            </div>
            {showDetail && detail && <JobDetails job={detail} />}
            {showApply && <ApplyForm jobId={job.id} />}
        </article>
    );
}
