import { Head, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import JobDetailPanel from '@/components/jobs/job-detail-panel';
import JobRow from '@/components/jobs/job-row';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { apiFetch } from '@/lib/api';
import { countWord } from '@/lib/jobs';
import type { User } from '@/types';
import type { Job, JobDetail, Paginated } from '@/types/jobs';

const DESKTOP = '(min-width: 64rem)';

export default function JobsIndex() {
    const user = usePage().props.auth.user as User | null;
    const canApply = !user?.is_admin;

    const [draft, setDraft] = useState('');
    const [query, setQuery] = useState('');
    const [page, setPage] = useState(1);
    const [result, setResult] = useState<Paginated<Job> | null>(null);
    const [listFailed, setListFailed] = useState(false);
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [details, setDetails] = useState<Record<number, JobDetail>>({});
    const [detailFailed, setDetailFailed] = useState<number | null>(null);

    // Search as you type, but wait for a pause so each keystroke is not a request.
    useEffect(() => {
        const timer = setTimeout(() => {
            setQuery(draft.trim());
            setPage(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [draft]);

    useEffect(() => {
        const controller = new AbortController();
        const params = new URLSearchParams({ page: String(page) });
        if (query) {
            params.set('q', query);
        }
        setListFailed(false);
        apiFetch(`/api/jobs?${params.toString()}`, {
            signal: controller.signal,
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error(res.statusText);
                }
                return res.json() as Promise<Paginated<Job>>;
            })
            .then((data) => {
                setResult(data);
                // On a wide screen the panel is visible, so open the first role.
                setSelectedId((current) => {
                    if (data.data.some((job) => job.id === current)) {
                        return current;
                    }
                    return window.matchMedia(DESKTOP).matches
                        ? (data.data[0]?.id ?? null)
                        : null;
                });
            })
            .catch((error: unknown) => {
                if (
                    !(
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    )
                ) {
                    setListFailed(true);
                }
            });
        return () => controller.abort();
    }, [page, query]);

    useEffect(() => {
        if (selectedId === null || details[selectedId]) {
            return;
        }
        const id = selectedId;
        const controller = new AbortController();
        setDetailFailed(null);
        apiFetch(`/api/jobs/${id}`, { signal: controller.signal })
            .then((res) => {
                if (!res.ok) {
                    throw new Error(res.statusText);
                }
                return res.json() as Promise<{ data: JobDetail }>;
            })
            .then((json) =>
                setDetails((cache) => ({ ...cache, [id]: json.data })),
            )
            .catch((error: unknown) => {
                if (
                    !(
                        error instanceof DOMException &&
                        error.name === 'AbortError'
                    )
                ) {
                    setDetailFailed(id);
                }
            });
        return () => controller.abort();
    }, [selectedId, details]);

    const jobs = result?.data ?? [];
    const selected = jobs.find((job) => job.id === selectedId) ?? null;
    const total = result?.meta.total;

    function panelFor(job: Job, onClose?: () => void) {
        return (
            <JobDetailPanel
                key={job.id}
                job={job}
                detail={details[job.id] ?? null}
                failed={detailFailed === job.id}
                canApply={canApply}
                onClose={onClose}
            />
        );
    }

    return (
        <>
            <Head title="Jobs" />

            <section className="grid gap-5 py-10 md:py-14">
                <p className="text-xs tracking-[0.1em] text-muted-foreground uppercase">
                    Open positions
                </p>
                <h1 className="max-w-[20ch] font-serif text-4xl leading-[1.05] font-medium md:text-5xl">
                    <Headline total={total} query={query} />
                </h1>
                <form
                    role="search"
                    onSubmit={(event) => {
                        event.preventDefault();
                        setQuery(draft.trim());
                        setPage(1);
                    }}
                    className="flex max-w-md overflow-hidden rounded-sm border-[1.5px] border-foreground focus-within:ring-2 focus-within:ring-ring/50"
                >
                    <label htmlFor="search" className="sr-only">
                        Search by title or keyword
                    </label>
                    <input
                        id="search"
                        type="search"
                        placeholder="Search by title or keyword"
                        value={draft}
                        onChange={(event) => setDraft(event.target.value)}
                        className="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none"
                    />
                    <button
                        type="submit"
                        className="bg-foreground px-4 text-sm font-semibold text-background"
                    >
                        Search
                    </button>
                </form>
            </section>

            <div className="border-t border-foreground lg:grid lg:grid-cols-[1.15fr_1fr]">
                <div className="lg:border-r lg:border-foreground">
                    <p className="border-b px-4 py-2 text-xs tracking-[0.08em] text-muted-foreground uppercase sm:px-6">
                        {result
                            ? `${result.meta.total} ${result.meta.total === 1 ? 'role' : 'roles'} · newest first`
                            : 'Loading roles'}
                    </p>

                    {listFailed && (
                        <div className="grid gap-3 px-4 py-10 sm:px-6">
                            <p className="font-serif text-xl">
                                The list could not be loaded.
                            </p>
                            <Button
                                variant="outline"
                                className="justify-self-start"
                                onClick={() => setPage((current) => current)}
                            >
                                Try again
                            </Button>
                        </div>
                    )}

                    {!result && !listFailed && (
                        <ul aria-busy="true" className="divide-y">
                            {[0, 1, 2, 3, 4].map((row) => (
                                <li
                                    key={row}
                                    className="grid gap-2 px-4 py-4 sm:px-6"
                                >
                                    <Skeleton className="h-5 w-2/3" />
                                    <Skeleton className="h-4 w-1/2" />
                                    <Skeleton className="h-4 w-5/6" />
                                </li>
                            ))}
                        </ul>
                    )}

                    {result && jobs.length === 0 && (
                        <div className="grid gap-3 px-4 py-10 sm:px-6">
                            <p className="font-serif text-xl">
                                {query
                                    ? `Nothing matches “${query}”.`
                                    : 'No open roles right now.'}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {query
                                    ? 'Try a broader word, or clear the search.'
                                    : 'New listings appear here as soon as companies publish them.'}
                            </p>
                            {query && (
                                <Button
                                    variant="outline"
                                    className="justify-self-start"
                                    onClick={() => setDraft('')}
                                >
                                    Clear search
                                </Button>
                            )}
                        </div>
                    )}

                    <ul className="divide-y">
                        {jobs.map((job) => {
                            const isSelected = job.id === selectedId;
                            return (
                                <li key={job.id}>
                                    <JobRow
                                        job={job}
                                        selected={isSelected}
                                        onSelect={() =>
                                            setSelectedId(
                                                isSelected ? null : job.id,
                                            )
                                        }
                                    />
                                    {isSelected && (
                                        <div className="border-t bg-card lg:hidden">
                                            {panelFor(job, () =>
                                                setSelectedId(null),
                                            )}
                                        </div>
                                    )}
                                </li>
                            );
                        })}
                    </ul>

                    {result && result.meta.last_page > 1 && (
                        <nav
                            aria-label="Pages"
                            className="flex items-center justify-between border-t px-4 py-3 text-sm sm:px-6"
                        >
                            <Button
                                variant="ghost"
                                size="sm"
                                disabled={page <= 1}
                                onClick={() => setPage(page - 1)}
                            >
                                Previous
                            </Button>
                            <span className="text-muted-foreground tabular-nums">
                                Page {result.meta.current_page} of{' '}
                                {result.meta.last_page}
                            </span>
                            <Button
                                variant="ghost"
                                size="sm"
                                disabled={page >= result.meta.last_page}
                                onClick={() => setPage(page + 1)}
                            >
                                Next
                            </Button>
                        </nav>
                    )}
                </div>

                <aside className="hidden lg:sticky lg:top-6 lg:block lg:max-h-[calc(100vh-3rem)] lg:self-start lg:overflow-y-auto">
                    {selected ? (
                        panelFor(selected)
                    ) : (
                        <p className="px-6 py-10 font-serif text-xl text-muted-foreground">
                            {jobs.length > 0
                                ? 'Choose a role on the left to read the full listing.'
                                : ''}
                        </p>
                    )}
                </aside>
            </div>
        </>
    );
}

function Headline({ total, query }: { total?: number; query: string }) {
    if (total === undefined) {
        return (
            <>
                Open roles. <em className="text-primary">One</em> of them is
                yours.
            </>
        );
    }
    if (query) {
        return (
            <>
                {countWord(total)} {total === 1 ? 'role' : 'roles'} for{' '}
                <em className="text-primary">{query}</em>.
            </>
        );
    }
    if (total === 0) {
        return (
            <>
                No open roles right now.{' '}
                <em className="text-primary">Check back</em> soon.
            </>
        );
    }
    if (total === 1) {
        return (
            <>
                One open role. <em className="text-primary">Maybe</em> it is
                yours.
            </>
        );
    }
    return (
        <>
            {countWord(total)} open roles. <em className="text-primary">One</em>{' '}
            of them is yours.
        </>
    );
}
