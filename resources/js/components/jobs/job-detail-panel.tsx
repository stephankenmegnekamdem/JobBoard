import { useState } from 'react';
import ApplyForm from '@/components/jobs/apply-form';
import Tag from '@/components/jobs/tag';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { closesOn, keywordList, postedAgo } from '@/lib/jobs';
import type { Job, JobDetail } from '@/types/jobs';

type Props = {
    job: Job;
    detail: JobDetail | null;
    failed: boolean;
    canApply: boolean;
    /** Present on phones, where the panel sits inside the list. */
    onClose?: () => void;
};

export default function JobDetailPanel({
    job,
    detail,
    failed,
    canApply,
    onClose,
}: Props) {
    const [applying, setApplying] = useState(false);
    const facts: Array<[string, string | null]> = [
        ['Salary', job.salary],
        ['Working time', job.working_time],
        ['Location', job.location],
        ['Posted', postedAgo(job.published_at)],
        ['Closes', detail ? closesOn(detail.expires_at) : null],
        ['Contact', detail?.contact?.name ?? null],
    ];

    return (
        <article className="grid gap-5 px-4 py-6 sm:px-6">
            <header className="grid gap-2">
                <div className="flex items-start justify-between gap-4">
                    <p className="text-xs tracking-[0.1em] text-primary uppercase">
                        {job.company.name}
                        {job.location && ` · ${job.location}`}
                    </p>
                    {onClose && (
                        <button
                            type="button"
                            onClick={onClose}
                            className="text-xs font-semibold text-muted-foreground underline-offset-4 hover:underline"
                        >
                            Close
                        </button>
                    )}
                </div>
                <h2 className="font-serif text-2xl leading-tight font-medium md:text-3xl">
                    {job.title}
                </h2>
                <p className="text-muted-foreground">{job.short_description}</p>
            </header>

            <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 border-y py-3 text-sm">
                {facts
                    .filter(([, value]) => value)
                    .map(([label, value]) => (
                        <div key={label} className="contents">
                            <dt className="text-muted-foreground">{label}</dt>
                            <dd className="tabular-nums">{value}</dd>
                        </div>
                    ))}
            </dl>

            {failed && (
                <p className="text-sm text-destructive">
                    The full listing could not be loaded. Try again in a moment.
                </p>
            )}

            {!detail && !failed && (
                <div className="grid gap-2" aria-busy="true">
                    <Skeleton className="h-4 w-full" />
                    <Skeleton className="h-4 w-11/12" />
                    <Skeleton className="h-4 w-4/5" />
                    <Skeleton className="h-4 w-2/3" />
                </div>
            )}

            {detail && (
                <>
                    <p className="leading-relaxed whitespace-pre-line">
                        {detail.description}
                    </p>

                    {keywordList(detail).length > 0 && (
                        <div className="flex flex-wrap gap-1.5">
                            {keywordList(detail).map((keyword) => (
                                <Tag key={keyword}>{keyword}</Tag>
                            ))}
                        </div>
                    )}

                    <section className="border-t pt-3 text-sm">
                        <h3 className="font-serif text-base font-medium">
                            About {detail.company.name}
                        </h3>
                        {detail.company.description && (
                            <p className="mt-1 text-muted-foreground">
                                {detail.company.description}
                            </p>
                        )}
                        {detail.company.website && (
                            <a
                                href={detail.company.website}
                                target="_blank"
                                rel="noreferrer"
                                className="mt-1 inline-block underline underline-offset-4"
                            >
                                Company website
                            </a>
                        )}
                    </section>

                    <div className="flex flex-wrap gap-2">
                        {canApply && (
                            <Button
                                onClick={() => setApplying((open) => !open)}
                                aria-expanded={applying}
                            >
                                {applying ? 'Hide form' : 'Apply for this role'}
                            </Button>
                        )}
                        {detail.link && (
                            <Button asChild variant="outline">
                                <a
                                    href={detail.link}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    Apply on company site
                                </a>
                            </Button>
                        )}
                    </div>

                    {canApply && applying && (
                        <ApplyForm
                            jobId={job.id}
                            companyName={job.company.name}
                        />
                    )}
                </>
            )}
        </article>
    );
}
