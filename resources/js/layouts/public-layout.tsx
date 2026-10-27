import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import type { User } from '@/types';
export default function PublicLayout({ children }: PropsWithChildren) {
    const { auth, name } = usePage().props;
    const user = auth.user as User | null; // null for guests, even though the kit's type says otherwise
    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                    <Link
                        href="/"
                        className="flex items-center gap-2 font-semibold"
                    >
                        <AppLogoIcon className="size-6" />
                        {name}
                    </Link>
                    <nav className="flex items-center gap-2">
                        {user ? (
                            <>
                                <span className="text-sm">{user.name}</span>
                                <Button asChild variant="outline" size="sm">
                                    <Link href="/settings/profile">
                                        Profile
                                    </Link>
                                </Button>
                                {user.is_admin && (
                                    <Button asChild size="sm">
                                        <Link href="/admin">Admin</Link>
                                    </Button>
                                )}
                            </>
                        ) : (
                            <>
                                <Button asChild variant="outline" size="sm">
                                    <Link href={login()}>Log in</Link>
                                </Button>
                                <Button asChild size="sm">
                                    <Link href={register()}>Sign up</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main className="mx-auto max-w-5xl px-4 py-8">{children}</main>
        </div>
    );
}
