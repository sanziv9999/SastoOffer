import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { router, usePage } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import AdminPagination from '@/components/AdminPagination';
import React from 'react';
import { ChevronDown, ChevronUp } from 'lucide-react';

type Props = {
  primaryCategories: any;
  filters?: { search?: string };
};

const Index = ({ primaryCategories, filters }: Props) => {
  const { flash } = usePage().props as any;
  const items = primaryCategories?.data || [];
  const [search, setSearch] = React.useState(filters?.search || '');
  const [expandedIds, setExpandedIds] = React.useState<Set<number>>(new Set());

  const onDelete = (id: number) => {
    if (!confirm('Delete this primary category?')) return;
    router.delete(`/admin/primary-categories/${id}`, { preserveScroll: true });
  };

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/primary-categories', { search: search || undefined }, { preserveState: true, replace: true });
  };

  const toggleChildren = (id: number) => {
    setExpandedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Primary Categories</h1>
          <p className="text-muted-foreground">Create, edit and delete primary categories.</p>
        </div>
        <Button asChild>
          <Link href="/admin/primary-categories/create">Create</Link>
        </Button>
      </div>

      <form onSubmit={onSearch} className="flex gap-2 max-w-md">
        <Input placeholder="Search..." value={search} onChange={(e) => setSearch(e.target.value)} />
        <Button type="submit" variant="outline">
          Search
        </Button>
      </form>

      {flash?.success && (
        <div className="rounded-md border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
          {flash.success}
        </div>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Categories</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {items.length ? (
            items.map((c: any) => {
              const children = Array.isArray(c.children) ? c.children : [];
              const hasChildren = children.length > 0;
              const isExpanded = expandedIds.has(c.id);

              return (
              <div key={c.id} className="space-y-1">
                <div className="flex items-center justify-between gap-3 border rounded-md p-3">
                  <div className="min-w-0 flex items-center gap-3">
                    {hasChildren ? (
                      <button
                        type="button"
                        onClick={() => toggleChildren(c.id)}
                        className="inline-flex h-7 w-7 items-center justify-center rounded-md border bg-background hover:bg-muted"
                        aria-label={isExpanded ? 'Collapse child categories' : 'Expand child categories'}
                        title={isExpanded ? 'Collapse child categories' : 'Expand child categories'}
                      >
                        {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                      </button>
                    ) : (
                      <div className="h-7 w-7" />
                    )}
                    <div className="h-10 w-10 rounded-md border bg-muted overflow-hidden shrink-0">
                      {c.image_url ? (
                        <img src={c.image_url} alt={c.name} className="h-full w-full object-cover" />
                      ) : null}
                    </div>
                    <div className="min-w-0">
                      <div className="font-medium truncate">{c.name}</div>
                      <div className="text-xs text-muted-foreground">
                        Slug: {c.slug || '-'} {hasChildren ? `· ${children.length} child ${children.length === 1 ? 'category' : 'categories'}` : ''}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 flex-shrink-0">
                    <Button variant="outline" size="sm" asChild>
                      <Link href={`/admin/primary-categories/${c.id}/edit`}>Edit</Link>
                    </Button>
                    <Button variant="destructive" size="sm" onClick={() => onDelete(c.id)}>
                      Delete
                    </Button>
                  </div>
                </div>

                {hasChildren && isExpanded && (
                  <div className="ml-6 space-y-1">
                    {children.map((child: any) => (
                      <div
                        key={child.id}
                        className="flex items-center justify-between gap-3 border rounded-md p-2 bg-muted/40"
                      >
                        <div className="min-w-0 flex items-center gap-3">
                          <div className="h-8 w-8 rounded-md border bg-muted overflow-hidden shrink-0">
                            {child.image_url ? (
                              <img src={child.image_url} alt={child.name} className="h-full w-full object-cover" />
                            ) : null}
                          </div>
                          <div className="min-w-0">
                            <div className="text-sm font-medium truncate">{child.name}</div>
                            <div className="text-[11px] text-muted-foreground">
                              Slug: {child.slug || '-'}
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-2 flex-shrink-0">
                          <Button variant="outline" size="sm" asChild>
                            <Link href={`/admin/primary-categories/${child.id}/edit`}>Edit</Link>
                          </Button>
                          <Button variant="destructive" size="sm" onClick={() => onDelete(child.id)}>
                            Delete
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            );
            })
          ) : (
            <p className="text-sm text-muted-foreground">No categories found.</p>
          )}
        </CardContent>
      </Card>

      <AdminPagination links={primaryCategories?.links} />
    </div>
  );
};

Index.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Index;

