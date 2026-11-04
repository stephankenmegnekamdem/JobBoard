import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiFetch } from '@/lib/api';
import type { User } from '@/types';

type Props = {
    jobId: number;
    companyName: string;
};

export default function ApplyForm({ jobId, companyName }: Props) {
    const { auth } = usePage().props;
    const user = auth.user as User | null;
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [sent, setSent] = useState(false);
    const [sending, setSending] = useState(false);
    const [failed, setFailed] = useState(false);

    async function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setFailed(false);
        const body = Object.fromEntries(
            new FormData(event.currentTarget).entries(),
        );
        const res = await apiFetch(`/api/jobs/${jobId}/applications`, {
            method: 'POST',
            body: JSON.stringify(body),
        });
        setSending(false);
        if (res.status === 422) {
            setErrors(
                ((await res.json()) as { errors: Record<string, string[]> })
                    .errors,
            );
            return;
        }
        if (!res.ok) {
            setFailed(true);
            return;
        }
        setErrors({});
        setSent(true);
    }

    if (sent) {
        return (
            <p
                role="status"
                className="border-l-[3px] border-primary pl-4 text-sm"
            >
                <span className="font-serif text-base font-medium">
                    Application sent.
                </span>
                <br />
                {companyName} has your details and will reply by email.
            </p>
        );
    }

    const fullName = user ? `${user.name} ${user.last_name}`.trim() : '';

    return (
        <form onSubmit={submit} className="grid gap-4 border-t pt-4">
            <p className="text-sm text-muted-foreground">
                {user
                    ? 'Prefilled from your profile. Edit anything that is missing or out of date.'
                    : 'No account needed. Your details go straight to the employer.'}
            </p>
            <Field id={`name-${jobId}`} label="Name" error={errors.name}>
                <Input
                    id={`name-${jobId}`}
                    name="name"
                    defaultValue={fullName}
                    autoComplete="name"
                    required
                />
            </Field>
            <Field id={`email-${jobId}`} label="Email" error={errors.email}>
                <Input
                    id={`email-${jobId}`}
                    name="email"
                    type="email"
                    defaultValue={user?.email ?? ''}
                    autoComplete="email"
                    required
                />
            </Field>
            <Field id={`tel-${jobId}`} label="Phone" error={errors.tel_num}>
                <Input
                    id={`tel-${jobId}`}
                    name="tel_num"
                    type="tel"
                    defaultValue={user?.tel_num ?? ''}
                    autoComplete="tel"
                />
            </Field>
            <Field
                id={`message-${jobId}`}
                label="Message to the employer"
                error={errors.message}
            >
                <textarea
                    id={`message-${jobId}`}
                    name="message"
                    required
                    rows={5}
                    className="min-h-28 rounded-md border border-input bg-transparent p-2 text-sm focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
                />
            </Field>
            {failed && (
                <p className="text-sm text-destructive">
                    Something went wrong and the application was not sent. Try
                    again.
                </p>
            )}
            <Button
                type="submit"
                disabled={sending}
                className="justify-self-start"
            >
                {sending ? 'Sending…' : 'Send application'}
            </Button>
        </form>
    );
}

function Field({
    id,
    label,
    error,
    children,
}: {
    id: string;
    label: string;
    error?: string[];
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-1.5">
            <Label htmlFor={id}>{label}</Label>
            {children}
            {error && <p className="text-sm text-destructive">{error[0]}</p>}
        </div>
    );
}
