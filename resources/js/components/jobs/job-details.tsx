import type { JobDetail } from '@/types/jobs';
export default function JobDetails({ job }: { job: JobDetail }) {
    return (
        <div className="flex flex-col gap-3 border-t pt-3 text-sm">
            <p className="whitespace-pre-line">{job.description}</p>
            <dl className="grid grid-cols-2 gap-x-4 gap-y-1">
                <dt className="font-medium">Salary</dt>
                <dd>{job.salary ?? 'Not stated'}</dd>
                <dt className="font-medium">Location</dt>
                <dd>{job.location ?? 'Not stated'}</dd>
                <dt className="font-medium">Working time</dt>
                <dd>{job.working_time ?? 'Not stated'}</dd>
                <dt className="font-medium">Keywords</dt>
                <dd>{job.keywords ?? '—'}</dd>
                <dt className="font-medium">Contact</dt>
                <dd>{job.contact.name}</dd>
                <dt className="font-medium">Company</dt>
                <dd>
                    {job.company.name}
                    {job.company.location && `, ${job.company.location}`}
                    {job.company.website && (
                        <>
                            {' · '}
                            <a
                                href={job.company.website}
                                className="underline"
                                target="_blank"
                                rel="noreferrer"
                            >
                                website
                            </a>
                        </>
                    )}
                </dd>
            </dl>
        </div>
    );
}
