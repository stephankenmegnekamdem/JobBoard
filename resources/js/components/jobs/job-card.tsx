import { Button } from '@/components/ui/button';
import type { Job } from '@/types/jobs';
export default function JobCard({ job }: { job: Job }) {
    return (
        <article className="flex flex-col gap-3 rounded-lg border p-4">
            <div>
                <h2 className="text-lg font-semibold">{job.title}</h2>
                <p className="text-sm text-muted-foreground">
                    {job.company.name}
                    {job.location && ` · ${job.location}`}
                    {job.working_time && ` ·  ${job.working_time}`}
                </p>
            </div>
            <p className="text-sm">{job.short_description}</p>
            <div className="flex gap-2">
                <Button variant="outline" size="sm">
                    Learn more
                </Button>
                <Button size="sm">Apply</Button>
            </div>
        </article>
    );
}
