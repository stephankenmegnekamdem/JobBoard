import { Head, Link, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { statusVariant } from '@/types/jobs';

type Props = {
    team: { name: string; slug: string };
    application: {
        id: number;
        name: string;
        email: string;
        telNum: string | null;
        message: string;
        status: string;
        appliedAt: string | null;
        jobTitle: string;
    };
    applicant: {
        name: string;
        about: string | null;
        address: string | null;
        avatarUrl: string | null;
        hasCv: boolean;
    } | null;
    statuses: string[];
    /** Only the company owner and admins may change the status. */
    canUpdateStatus: boolean;
};

export default function CompanyApplication({
    team,
    application,
    applicant,
    statuses,
    canUpdateStatus,
}: Props) {
    const base = `/${team.slug}/applications/${application.id}`;

    function changeStatus(status: string) {
        router.patch(`${base}/status`, { status }, { preserveScroll: true });
    }

    return (
        <>
            <Head title={`Application: ${application.name}`} />

            <div className="space-y-8">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        variant="small"
                        title={application.name}
                        description={`Applied for ${application.jobTitle}${
                            application.appliedAt
                                ? ` on ${new Date(application.appliedAt).toLocaleDateString()}`
                                : ''
                        }`}
                    />
                    <Button asChild variant="outline">
                        <Link href={`/${team.slug}/dashboard`}>
                            Back to dashboard
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-6 md:grid-cols-[2fr_1fr]">
                    <section className="space-y-4 rounded-lg border p-4">
                        <h2 className="font-medium">
                            Message from the applicant
                        </h2>
                        <p className="text-sm whitespace-pre-line">
                            {application.message}
                        </p>

                        <dl className="grid grid-cols-[8rem_1fr] gap-y-1 text-sm">
                            <dt className="text-muted-foreground">Email</dt>
                            <dd>
                                <a
                                    href={`mailto:${application.email}`}
                                    className="underline"
                                >
                                    {application.email}
                                </a>
                            </dd>
                            <dt className="text-muted-foreground">Phone</dt>
                            <dd>{application.telNum ?? 'Not given'}</dd>
                        </dl>
                    </section>

                    <aside className="space-y-4">
                        <div className="space-y-2 rounded-lg border p-4">
                            <Label htmlFor="status">Status</Label>
                            {canUpdateStatus ? (
                                <select
                                    id="status"
                                    defaultValue={application.status}
                                    onChange={(event) =>
                                        changeStatus(event.target.value)
                                    }
                                    className="w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                                >
                                    {statuses.map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <p
                                    id="status"
                                    className="text-sm text-muted-foreground"
                                >
                                    Only the company owner and admins can change
                                    the status.
                                </p>
                            )}
                            <Badge
                                variant={
                                    statusVariant[application.status] ??
                                    'secondary'
                                }
                            >
                                {application.status}
                            </Badge>
                        </div>

                        <div className="space-y-3 rounded-lg border p-4">
                            <h2 className="font-medium">Profile</h2>
                            {applicant ? (
                                <>
                                    <div className="flex items-center gap-3">
                                        <Avatar className="size-12">
                                            {applicant.avatarUrl && (
                                                <AvatarImage
                                                    src={applicant.avatarUrl}
                                                    alt={applicant.name}
                                                />
                                            )}
                                            <AvatarFallback>
                                                {applicant.name.charAt(0)}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div className="font-medium">
                                                {applicant.name}
                                            </div>
                                            {applicant.address && (
                                                <div className="text-sm text-muted-foreground">
                                                    {applicant.address}
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {applicant.about && (
                                        <p className="text-sm whitespace-pre-line">
                                            {applicant.about}
                                        </p>
                                    )}

                                    {applicant.hasCv ? (
                                        <Button asChild size="sm">
                                            <a href={`${base}/cv`}>
                                                Download CV
                                            </a>
                                        </Button>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">
                                            No CV uploaded.
                                        </p>
                                    )}
                                </>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Applied as a guest, without an account.
                                </p>
                            )}
                        </div>
                    </aside>
                </div>
            </div>
        </>
    );
}

CompanyApplication.layout = (props: {
    homeUrl: string;
    application: { name: string };
}) => ({
    breadcrumbs: [
        { title: 'Dashboard', href: props.homeUrl },
        { title: props.application.name, href: '#' },
    ],
});
