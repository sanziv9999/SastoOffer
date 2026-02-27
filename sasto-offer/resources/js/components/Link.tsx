
import React from 'react';
import { Link as InertiaLink, usePage } from '@inertiajs/react';
import { Link as RouterLink } from 'react-router-dom';

interface LinkProps extends React.AnchorHTMLAttributes<HTMLAnchorElement> {
    href: string;
    className?: string;
    children: React.ReactNode;
}

const Link = ({ href, className, children, ...props }: LinkProps) => {
    let isInertia = false;
    try {
        usePage();
        isInertia = true;
    } catch (e) {
        isInertia = false;
    }

    if (isInertia) {
        return (
            <InertiaLink href={href} className={className} {...(props as any)}>
                {children}
            </InertiaLink>
        );
    }

    return (
        <RouterLink to={href} className={className} {...(props as any)}>
            {children}
        </RouterLink>
    );
};

export default Link;
