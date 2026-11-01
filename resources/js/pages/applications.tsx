import { Head } from '@inertiajs/react';
import { useState } from 'react';
import PendingInvitationsModal from '@/components/pending-invitations-modal';
import Heading from '@/components/heading';
import type { DashboardInvitation } from '@/types';

type Props = {
    pendingInvitations?: DashboardInvitation[];
};

export default function Applications({ pendingInvitations = [] }: Props) {
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
                <p className="py-8 text-center text-muted-foreground">
                    You haven't applied to any jobs yet.
                </p>
            </div>
        </>
    );
}

Applications.layout = {
    breadcrumbs: [{ title: 'Applications', href: '/applications' }],
};
