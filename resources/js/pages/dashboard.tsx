import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/teams';
import type { DashboardInvitation } from '@/types';

type RecentApplication = {
    id: number;
    name: string;
    email: string;
    status: string;
    jobTitle: string;
    createdAt: string;
};

type Props = {
    team: { name: string; slug: string };
    stats: {
        openJobs: number;
        totalApplications: number;
        newApplications: number;
        members: number;
        pendingInvites: number;
    };
    recentApplications: RecentApplication[];
    pendingInvitations?: DashboardInvitation[];
};

export default function Dashboard({
    team,
    stats,
    recentApplications,
    pendingInvitations = [],
}: Props) {
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    const cards = [
        { label: 'Open listings', value: stats.openJobs },
        { label: 'Applications', value: stats.totalApplications },
        { label: 'New applications', value: stats.newApplications },
        { label: 'Members', value: stats.members },
        { label: 'Pending invites', value: stats.pendingInvites },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />

            <div className="space-y-8">
                <div className="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title={team.name}
                        description="Your company at a glance"
                    />
                    <Button asChild variant="outline">
                        <Link href={edit(team.slug)} data-test="manage-team-link">
                            Manage team
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {cards.map((card) => (
                        <div
                            key={card.label}
                            data-test="company-stat"
                            className="rounded-lg border p-4"
                        >
                            <div className="text-sm text-muted-foreground">
                                {card.label}
                            </div>
                            <div className="text-2xl font-semibold">
                                {card.value}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="space-y-4">
                    <h2 className="text-base font-medium">
                        Recent applications
                    </h2>

                    {recentApplications.length === 0 ? (
                        <p
                            data-test="no-applications"
                            className="rounded-lg border py-8 text-center text-muted-foreground"
                        >
                            No applications yet. They will appear here when
                            candidates apply to your listings.
                        </p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {recentApplications.map((application) => (
                                <div
                                    key={application.id}
                                    data-test="application-row"
                                    className="flex items-center justify-between gap-4 p-4"
                                >
                                    <div className="min-w-0">
                                        <div className="truncate font-medium">
                                            {application.name}
                                        </div>
                                        <div className="truncate text-sm text-muted-foreground">
                                            {application.jobTitle} ·{' '}
                                            {application.email}
                                        </div>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-3">
                                        <Badge variant="secondary">
                                            {application.status}
                                        </Badge>
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(
                                                application.createdAt,
                                            ).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

Dashboard.layout = (props: { homeUrl: string }) => ({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: props.homeUrl,
        },
    ],
});
