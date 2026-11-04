import Tag from '@/components/jobs/tag';
import { jobTags } from '@/lib/jobs';
import { cn } from '@/lib/utils';
import type { Job } from '@/types/jobs';

type Props = {
    job: Job;
    selected: boolean;
    onSelect: () => void;
};

export default function JobRow({ job, selected, onSelect }: Props) {
    const tags = jobTags(job);

    return (
        <button
            type="button"
            onClick={onSelect}
            aria-expanded={selected}
            className={cn(
                'grid w-full grid-cols-[1fr_auto] gap-x-4 gap-y-1 px-4 py-4 text-left transition-colors hover:bg-secondary/60 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset sm:px-6',
                selected &&
                    'bg-secondary shadow-[inset_3px_0_0_0_var(--primary)]',
            )}
        >
            <span className="font-serif text-lg leading-snug font-medium">
                {job.title}
            </span>
            <span className="text-right text-sm font-semibold tabular-nums">
                {job.salary}
            </span>
            <span className="text-sm text-muted-foreground">
                {job.company.name}
                {job.location && ` · ${job.location}`}
            </span>
            <span className="text-right text-sm text-muted-foreground">
                {job.working_time}
            </span>
            <span className="col-span-full mt-1 line-clamp-2 text-sm">
                {job.short_description}
            </span>
            <span className="col-span-full mt-1 flex flex-wrap items-center gap-1.5">
                {tags.map((tag) => (
                    <Tag key={tag}>{tag}</Tag>
                ))}
                <span className="ml-auto text-xs font-semibold text-primary">
                    {selected ? 'Selected' : 'Learn more'}
                </span>
            </span>
        </button>
    );
}
