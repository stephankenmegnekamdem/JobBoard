import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiFetch } from '@/lib/api';
import type { User } from '@/types';
export default function ApplyForm({ jobId }: { jobId: number }) {
    const { auth } = usePage().props;
    const user = auth.user as User | null;
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [sent, setSent] = useState(false);
    const [sending, setSending] = useState(false);
    async function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
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
        setErrors({});
        setSent(true);
    }
    if (sent) {
        return (
            <p className="border-t pt-3 text-sm font-medium">
                Application sent. Thank you!
            </p>
        );
    }

    const fullName = user ? `${user.name} ${user.last_name}`.trim() : '';

    return (
        <form onSubmit={submit} className="grid gap-3 border-t pt-3">
            <div className="grid gap-1">
                <Label htmlFor={`name-${jobId}`}>Name</Label>
                <Input
                    id={`name-${jobId}`}
                    name="name"
                    defaultValue={fullName}
                    readOnly={!!user}
                    required
                />
                {errors.name && (
                    <p className="text-sm text-red-600">{errors.name[0]}</p>
                )}
            </div>
            <div className="grid gap-1">
                <Label htmlFor={`email-${jobId}`}>Email</Label>
                <Input
                    id={`email-${jobId}`}
                    name="email"
                    type="email"
                    defaultValue={user?.email ?? ''}
                    readOnly={!!user}
                    required
                />
                {errors.email && (
                    <p className="text-sm text-red-600">{errors.email[0]}</p>
                )}
            </div>
            <div className="grid gap-1">
                <Label htmlFor={`tel-${jobId}`}>Phone</Label>
                <Input
                    id={`tel-${jobId}`}
                    name="tel_num"
                    defaultValue={user?.tel_num ?? ''}
                    readOnly={!!user}
                />
                {errors.tel_num && (
                    <p className="text-sm text-red-600">{errors.tel_num[0]}</p>
                )}
            </div>
            <div className="grid gap-1">
                <Label htmlFor={`message-${jobId}`}>
                    Message to the employer
                </Label>
                <textarea
                    id={`message-${jobId}`}
                    name="message"
                    required
                    className="min-h-24 rounded-md border bg-transparent p-2 text-sm"
                />
                {errors.message && (
                    <p className="text-sm text-red-600">{errors.message[0]}</p>
                )}
            </div>
            <Button
                type="submit"
                disabled={sending}
                className="justify-self-start"
            >
                Send application
            </Button>
        </form>
    );
}
