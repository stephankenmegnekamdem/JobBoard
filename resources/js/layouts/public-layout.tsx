import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { login, register } from '@/routes';
import type { User } from '@/types';

const linkClass =
    'text-sm text-foreground underline-offset-4 hover:underline focus-visible:underline focus-visible:outline-none';

export default function PublicLayout({ children }: PropsWithChildren) {
    const { auth, name, homeUrl } = usePage().props;
    const user = auth.user as User | null; // null for guests, even though the kit's type says otherwise

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <header className="border-b border-foreground">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                    <Link
                        href="/"
                        className="font-serif text-xl font-bold tracking-tight"
                    >
                        {name}
                    </Link>
                    <nav className="flex items-center gap-4">
                        {user ? (
                            <>
                                <span className="hidden text-sm text-muted-foreground sm:inline">
                                    {user.name}
                                </span>
                                {user.account_type === 'seeker' &&
                                    !user.is_admin && (
                                        <Link
                                            href="/applications"
                                            className={linkClass}
                                        >
                                            My applications
                                        </Link>
                                    )}
                                {user.account_type === 'company' &&
                                    !user.is_admin && (
                                        <Link
                                            href={homeUrl}
                                            className={linkClass}
                                        >
                                            Dashboard
                                        </Link>
                                    )}
                                <Link
                                    href="/settings/profile"
                                    className={linkClass}
                                >
                                    Profile
                                </Link>
                                {user.is_admin && (
                                    <Link
                                        href="/admin"
                                        className="rounded-sm bg-foreground px-3 py-1.5 text-sm font-semibold text-background"
                                    >
                                        Admin
                                    </Link>
                                )}
                            </>
                        ) : (
                            <>
                                <Link href={login()} className={linkClass}>
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="rounded-sm bg-foreground px-3 py-1.5 text-sm font-semibold text-background"
                                >
                                    Sign up
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main className="mx-auto w-full max-w-6xl flex-1 px-4 sm:px-6">
                {children}
            </main>
            <footer className="mt-16 border-t">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-4 text-xs text-muted-foreground sm:px-6">
                    <span className="font-serif text-sm text-foreground">
                        {name}
                    </span>
                    <span>
                        Listings are posted by the companies themselves.
                    </span>
                </div>
            </footer>
        </div>
    );
}
