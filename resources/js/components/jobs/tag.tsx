import type { PropsWithChildren } from 'react';

/** Square, outlined tag: the Editorial look uses rules, not filled pills. */
export default function Tag({ children }: PropsWithChildren) {
    return (
        <span className="inline-block rounded-[2px] border border-foreground px-1.5 text-[0.7rem] leading-5 font-medium">
            {children}
        </span>
    );
}
