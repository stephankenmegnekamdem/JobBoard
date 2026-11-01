import { useCallback, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiFetch } from '@/lib/api';

export type Field = {
    name: string;
    label: string;
    type?: 'text' | 'textarea' | 'number' | 'email' | 'password' | 'checkbox';
};

export type ResourceConfig = {
    title: string;
    endpoint: string;
    columns: string[];
    fields: Field[];
};

type Row = Record<string, unknown> & { id: number };
type Page = {
    data: Row[];
    meta: { current_page: number; last_page: number; total: number };
};

// Cell values come from JSON, so they can be anything; render them safely as text.
function text(value: unknown): string {
    if (value === null || value === undefined) {
        return '';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    return String(value as string | number | boolean);
}

export default function DataTable({ config }: { config: ResourceConfig }) {
    const [page, setPage] = useState(1);
    const [result, setResult] = useState<Page | null>(null);
    const [editing, setEditing] = useState<Row | 'new' | null>(null);
    const [errors, setErrors] = useState<Record<string, string[]>>({});

    const load = useCallback(async () => {
        const res = await apiFetch(`${config.endpoint}?page=${page}`);
        setResult((await res.json()) as Page);
    }, [config.endpoint, page]);

    useEffect(() => {
        void load();
    }, [load]);

    async function save(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const form = new FormData(event.currentTarget);
        const body: Record<string, unknown> = {};
        for (const field of config.fields) {
            body[field.name] =
                field.type === 'checkbox'
                    ? form.has(field.name)
                    : form.get(field.name);
        }
        const isNew = editing === 'new';
        const url = isNew
            ? config.endpoint
            : `${config.endpoint}/${(editing as Row).id}`;
        const res = await apiFetch(url, {
            method: isNew ? 'POST' : 'PUT',
            body: JSON.stringify(body),
        });
        if (res.status === 422) {
            setErrors(
                ((await res.json()) as { errors: Record<string, string[]> })
                    .errors,
            );
            return;
        }
        setEditing(null);
        setErrors({});
        await load();
    }

    async function remove(row: Row) {
        await apiFetch(`${config.endpoint}/${row.id}`, { method: 'DELETE' });
        await load();
    }

    const current = editing === 'new' ? null : editing;

    return (
        <div className="flex flex-col gap-4">
            <div className="flex items-center justify-between">
                <h2 className="text-lg font-semibold">
                    {config.title} {result && `(${result.meta.total})`}
                </h2>
                <Button onClick={() => setEditing('new')}>New</Button>
            </div>

            {editing && (
                <form
                    onSubmit={save}
                    className="grid gap-3 rounded-lg border p-4"
                >
                    {config.fields.map((field) => (
                        <div key={field.name} className="grid gap-1">
                            <Label htmlFor={field.name}>{field.label}</Label>
                            {field.type === 'textarea' ? (
                                <textarea
                                    id={field.name}
                                    name={field.name}
                                    defaultValue={text(current?.[field.name])}
                                    className="min-h-24 rounded-md border bg-transparent p-2 text-sm"
                                />
                            ) : field.type === 'checkbox' ? (
                                <input
                                    id={field.name}
                                    name={field.name}
                                    type="checkbox"
                                    defaultChecked={Boolean(
                                        current?.[field.name],
                                    )}
                                />
                            ) : (
                                <Input
                                    id={field.name}
                                    name={field.name}
                                    type={field.type ?? 'text'}
                                    defaultValue={
                                        field.type === 'password'
                                            ? ''
                                            : text(current?.[field.name])
                                    }
                                />
                            )}
                            {errors[field.name] && (
                                <p className="text-sm text-red-600">
                                    {errors[field.name][0]}
                                </p>
                            )}
                        </div>
                    ))}
                    <div className="flex gap-2">
                        <Button type="submit">Save</Button>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => setEditing(null)}
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            )}

            <div className="overflow-x-auto rounded-lg border">
                <table className="w-full text-sm">
                    <thead className="bg-muted text-left">
                        <tr>
                            {config.columns.map((column) => (
                                <th key={column} className="p-2 font-medium">
                                    {column}
                                </th>
                            ))}
                            <th className="p-2" />
                        </tr>
                    </thead>
                    <tbody>
                        {result?.data.map((row) => (
                            <tr key={row.id} className="border-t">
                                {config.columns.map((column) => (
                                    <td key={column} className="p-2">
                                        {text(row[column])}
                                    </td>
                                ))}
                                <td className="flex gap-2 p-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() => setEditing(row)}
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        onClick={() => void remove(row)}
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {result && (
                <div className="flex items-center gap-3">
                    <Button
                        variant="outline"
                        disabled={page <= 1}
                        onClick={() => setPage(page - 1)}
                    >
                        Previous
                    </Button>
                    <span className="text-sm">
                        Page {result.meta.current_page} of{' '}
                        {result.meta.last_page}
                    </span>
                    <Button
                        variant="outline"
                        disabled={page >= result.meta.last_page}
                        onClick={() => setPage(page + 1)}
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}
