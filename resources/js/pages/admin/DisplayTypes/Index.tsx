import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminPagination from '@/components/AdminPagination';
import { router, usePage } from '@inertiajs/react';
import React from 'react';

type Props = {
  displayTypes: any;
  filters?: { search?: string };
};

const Index = ({ displayTypes, filters }: Props) => {
  const { flash } = usePage().props as any;
  const items = displayTypes?.data || [];
  const [search, setSearch] = React.useState(filters?.search || '');

  const onDelete = (id: number) => {
    if (!confirm('Delete this display type?')) return;
    router.delete(`/admin/display-types/${id}`, { preserveScroll: true });
  };

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/display-types', { search: search || undefined }, { preserveState: true, replace: true });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Display Types</h1>
          <p className="text-muted-foreground">Manage display tags for deal offers.</p>
        </div>
        <Button asChild>
          <Link href="/admin/display-types/create">Create</Link>
        </Button>
      </div>

      <form onSubmit={onSearch} className="flex gap-2 max-w-md">
        <Input placeholder="Search..." value={search} onChange={(e) => setSearch(e.target.value)} />
        <Button type="submit" variant="outline">Search</Button>
      </form>

      {(flash?.success || flash?.error) && (
        <div className={`rounded-md border px-4 py-2 text-sm ${flash?.error ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'}`}>
          {flash?.error || flash?.success}
        </div>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Display Types</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {items.length ? (
            items.map((item: any) => (
              <div key={item.id} className="flex items-center justify-between gap-3 border rounded-md p-3">
                <div className="min-w-0">
                  <div className="font-medium truncate">{item.name}</div>
                  <div className="text-xs text-muted-foreground">ID: {item.id}</div>
                </div>
                <div className="flex items-center gap-2 flex-shrink-0">
                  <Button variant="outline" size="sm" asChild>
                    <Link href={`/admin/display-types/${item.id}/edit`}>Edit</Link>
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => onDelete(item.id)}>Delete</Button>
                </div>
              </div>
            ))
          ) : (
            <p className="text-sm text-muted-foreground">No display types found.</p>
          )}
        </CardContent>
      </Card>

      <AdminPagination links={displayTypes?.links} />
    </div>
  );
};

Index.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Index;

