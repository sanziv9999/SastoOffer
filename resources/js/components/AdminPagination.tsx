import Link from '@/components/Link';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';

type LinkItem = { url: string | null; label: string; active: boolean };

export default function AdminPagination({ links }: { links?: LinkItem[] }) {
  if (!links || links.length <= 3) return null;

  const prev = links[0];
  const next = links[links.length - 1];
  const middle = links.slice(1, -1);

  const renderMiddle = (l: LinkItem, i: number) => {
    const label = l.label.replace(/&laquo;|&raquo;/g, '').trim();
    if (label === '...' || label.includes('...')) {
      return (
        <PaginationItem key={`ellipsis-${i}`}>
          <PaginationEllipsis />
        </PaginationItem>
      );
    }

    if (!l.url) {
      return (
        <PaginationItem key={`disabled-${i}`}>
          <PaginationLink aria-disabled="true" tabIndex={-1} className="pointer-events-none opacity-50">
            {label}
          </PaginationLink>
        </PaginationItem>
      );
    }

    return (
      <PaginationItem key={l.url}>
        <PaginationLink asChild isActive={l.active}>
          <Link href={l.url}>{label}</Link>
        </PaginationLink>
      </PaginationItem>
    );
  };

  return (
    <Pagination>
      <PaginationContent>
        <PaginationItem>
          <PaginationPrevious asChild className={!prev?.url ? 'pointer-events-none opacity-50' : undefined}>
            <Link href={prev?.url || '#'} />
          </PaginationPrevious>
        </PaginationItem>

        {middle.map(renderMiddle)}

        <PaginationItem>
          <PaginationNext asChild className={!next?.url ? 'pointer-events-none opacity-50' : undefined}>
            <Link href={next?.url || '#'} />
          </PaginationNext>
        </PaginationItem>
      </PaginationContent>
    </Pagination>
  );
}

