import type { Job, JobDetail } from '@/types/jobs';

const WORDS = [
    'No',
    'One',
    'Two',
    'Three',
    'Four',
    'Five',
    'Six',
    'Seven',
    'Eight',
    'Nine',
    'Ten',
    'Eleven',
    'Twelve',
];

/** "Twelve" for small counts, digits after that, so the headline reads like prose. */
export function countWord(count: number): string {
    return count < WORDS.length ? WORDS[count] : String(count);
}

/** Days since an ISO date, or null when the date is missing or unreadable. */
export function daysSince(iso: string | null): number | null {
    if (!iso) {
        return null;
    }
    const time = new Date(iso).getTime();
    if (Number.isNaN(time)) {
        return null;
    }
    return Math.max(0, Math.floor((Date.now() - time) / 86_400_000));
}

export function postedAgo(iso: string | null): string | null {
    const days = daysSince(iso);
    if (days === null) {
        return null;
    }
    if (days === 0) {
        return 'Posted today';
    }
    if (days === 1) {
        return 'Posted yesterday';
    }
    if (days < 30) {
        return `Posted ${days} days ago`;
    }
    return `Posted ${new Date(iso as string).toLocaleDateString()}`;
}

export function closesOn(iso: string | null): string | null {
    if (!iso) {
        return null;
    }
    const date = new Date(iso);
    return Number.isNaN(date.getTime()) ? null : date.toLocaleDateString();
}

/** Short reasons to look at a listing, shown as square tags on each row. */
export function jobTags(job: Job): string[] {
    const tags: string[] = [];
    if (job.working_time) {
        tags.push(job.working_time);
    }
    if (job.location && /remote/i.test(job.location)) {
        tags.push('Remote');
    }
    const days = daysSince(job.published_at);
    if (days !== null && days <= 7) {
        tags.push('New');
    }
    return tags;
}

export function keywordList(job: JobDetail): string[] {
    return (job.keywords ?? '')
        .split(',')
        .map((keyword) => keyword.trim())
        .filter((keyword) => keyword !== '');
}
