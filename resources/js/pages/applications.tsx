import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import { Badge } from '@/components/ui/badge';
import type { DashboardInvitation } from '@/types';

type Application = {
    id: number;
    jobTitle: string;
    company: string | null;
    status: string;
    appliedAt: string | null;
};

type Props = {
    applications: Application[];
    pendingInvitations?: DashboardInvitation[];
};

export default function Applications({
    applications,
    pendingInvitations = [],
}: Props) {
    const [showInvitations, setShowInvitations] = useState(
        pendingInvitations.length > 0,
    );

    return (
        <>
            <Head title="Applications" />
            <PendingInvitationsModal
                invitations={pendingInvitations}
                open={pendingInvitations.length > 0 && showInvitations}
                onOpenChange={setShowInvitations}
            />
            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Your applications"
                    description="Track the jobs you have applied to"
                />

                {applications.length === 0 ? (
                    <p className="rounded-lg border py-8 text-center text-muted-foreground">
                        You haven't applied to any jobs yet.
                    </p>
                ) : (
                    <div className="divide-y rounded-lg border">
                        {applications.map((application) => (
                            <div
                                key={application.id}
                                className="flex items-center justify-between gap-4 p-4"
                            >
                                <div className="min-w-0">
                                    <div className="truncate font-medium">
                                        {application.jobTitle}
                                    </div>
                                    <div className="truncate text-sm text-muted-foreground">
                                        {application.company ??
                                            'Company removed'}
                                    </div>
                                </div>
                                <div className="flex shrink-0 items-center gap-3">
                                    <Badge variant="secondary">
                                        {application.status}
                                    </Badge>
                                    {application.appliedAt && (
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(
                                                application.appliedAt,
                                            ).toLocaleDateString()}
                                        </span>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Applications.layout = {
    breadcrumbs: [{ title: 'Applications', href: '/applications' }],
};
