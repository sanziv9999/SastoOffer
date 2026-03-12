import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { router, usePage } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import AdminPagination from '@/components/AdminPagination';
import React from 'react';

type Props = {
  offerTypes: any;
  filters?: { search?: string };
};

const Index = ({ offerTypes, filters }: Props) => {
  const { flash } = usePage().props as any;
  const items = offerTypes?.data || [];
  const [search, setSearch] = React.useState(filters?.search || '');

  const onDelete = (id: number) => {
    if (!confirm('Delete this offer type?')) return;
    router.delete(`/admin/offer-types/${id}`, { preserveScroll: true });
  };

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/offer-types', { search: search || undefined }, { preserveState: true, replace: true });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Offer Types</h1>
          <p className="text-muted-foreground">Create, edit and delete offer types.</p>
        </div>
        <Button asChild>
          <Link href="/admin/offer-types/create">Create</Link>
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
          <CardTitle>Offer Types</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {items.length ? (
            items.map((o: any) => (
              <div key={o.id} className="flex items-center justify-between gap-3 border rounded-md p-3">
                <div className="min-w-0">
                  <div className="font-medium truncate">{o.display_name || o.name}</div>
                  <div className="text-xs text-muted-foreground">Name: {o.name} · Slug: {o.slug || '-'}</div>
                </div>
                <div className="flex items-center gap-2 flex-shrink-0">
                  <Button variant="outline" size="sm" asChild>
                    <Link href={`/admin/offer-types/${o.id}/edit`}>Edit</Link>
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => onDelete(o.id)}>
                    Delete
                  </Button>
                </div>
              </div>
            ))
          ) : (
            <p className="text-sm text-muted-foreground">No offer types found.</p>
          )}
        </CardContent>
      </Card>

      <AdminPagination links={offerTypes?.links} />
    </div>
  );
};

Index.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Index;

