import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import AdminPagination from '@/components/AdminPagination';
import { router, usePage } from '@inertiajs/react';
import React from 'react';
import { Sparkles, Pencil, Trash2, Search, LayoutGrid } from 'lucide-react';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

export type BannerRow = {
  id: number;
  title: string;
  text: string | null;
  is_featured: boolean;
  sort_order: number;
  category_id: number | null;
  category_label: string | null;
  image_url: string | null;
  updated_at?: string | null;
};

type Props = {
  banners: {
    data: BannerRow[];
    links: unknown;
    total?: number;
    from?: number;
    to?: number;
  };
  filters?: { search?: string };
};

const Index = ({ banners, filters }: Props) => {
  const { flash } = usePage().props as { flash?: { success?: string } };
  const items = banners?.data || [];
  const [search, setSearch] = React.useState(filters?.search || '');
  const [deleteId, setDeleteId] = React.useState<number | null>(null);

  const featuredOnPage = items.filter((b) => b.is_featured).length;

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/banners', { search: search || undefined }, { preserveState: true, replace: true });
  };

  const confirmDelete = () => {
    if (deleteId == null) return;
    router.delete(`/admin/banners/${deleteId}`, { preserveScroll: true });
    setDeleteId(null);
  };

  return (
    <div className="space-y-8">
      <div className="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-primary/10 via-background to-muted/40 p-6 sm:p-8">
        <div className="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div className="space-y-2">
            <div className="inline-flex items-center gap-2 rounded-full border bg-background/80 px-3 py-1 text-xs font-medium text-muted-foreground backdrop-blur">
              <LayoutGrid className="h-3.5 w-3.5" />
              Homepage content
            </div>
            <h1 className="text-3xl font-bold tracking-tight">Banners</h1>
            <p className="max-w-xl text-muted-foreground">
              Manage hero slides for the landing page. Mark banners as featured and order them for a polished first impression.
            </p>
          </div>
          <Button asChild size="lg" className="shrink-0 shadow-sm">
            <Link href="/admin/banners/create">Create banner</Link>
          </Button>
        </div>
      </div>

      <div className="grid gap-3 sm:grid-cols-3">
        <Card className="border-primary/15 bg-primary/5">
          <CardContent className="flex items-center gap-3 pt-6">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15">
              <LayoutGrid className="h-5 w-5 text-primary" />
            </div>
            <div>
              <p className="text-2xl font-semibold tabular-nums">{banners?.total ?? items.length}</p>
              <p className="text-xs text-muted-foreground">Total banners</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="flex items-center gap-3 pt-6">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/15">
              <Sparkles className="h-5 w-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div>
              <p className="text-2xl font-semibold tabular-nums">{featuredOnPage}</p>
              <p className="text-xs text-muted-foreground">Featured on this page</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="flex items-center gap-3 pt-6">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
              <Search className="h-5 w-5 text-muted-foreground" />
            </div>
            <div>
              <p className="text-sm font-medium leading-none">Search</p>
              <p className="text-xs text-muted-foreground">Filter by title or copy</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <form onSubmit={onSearch} className="flex flex-col gap-2 sm:flex-row sm:items-center sm:max-w-lg">
        <Input
          placeholder="Search banners..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="flex-1"
        />
        <Button type="submit" variant="secondary">
          Search
        </Button>
      </form>

      {flash?.success && (
        <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
          {flash.success}
        </div>
      )}

      {items.length ? (
        <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
          {items.map((b) => (
            <article
              key={b.id}
              className="group flex flex-col overflow-hidden rounded-xl border bg-card shadow-sm transition hover:shadow-md"
            >
              <div className="relative aspect-[21/9] overflow-hidden bg-muted">
                {b.image_url ? (
                  <img
                    src={b.image_url}
                    alt=""
                    className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                  />
                ) : (
                  <div className="flex h-full items-center justify-center text-xs text-muted-foreground">No image</div>
                )}
                <div className="absolute left-2 top-2 flex flex-wrap gap-1">
                  {b.is_featured ? (
                    <Badge className="shadow-sm">Landing</Badge>
                  ) : (
                    <Badge variant="secondary">Hidden</Badge>
                  )}
                  <Badge variant="outline" className="bg-background/90 backdrop-blur">
                    Order {b.sort_order}
                  </Badge>
                  {b.category_label ? (
                    <Badge variant="outline" className="max-w-[140px] truncate bg-background/90 backdrop-blur">
                      {b.category_label}
                    </Badge>
                  ) : null}
                </div>
              </div>
              <div className="flex flex-1 flex-col gap-3 p-4">
                <div className="min-w-0 flex-1 space-y-1">
                  <h2 className="font-semibold leading-tight line-clamp-2">{b.title}</h2>
                  {b.text ? (
                    <p className="text-sm text-muted-foreground line-clamp-2">{b.text}</p>
                  ) : (
                    <p className="text-sm italic text-muted-foreground/70">No supporting text</p>
                  )}
                </div>
                <div className="flex gap-2 pt-1">
                  <Button variant="outline" size="sm" className="flex-1" asChild>
                    <Link href={`/admin/banners/${b.id}/edit`}>
                      <Pencil className="mr-1.5 h-3.5 w-3.5" />
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                    onClick={() => setDeleteId(b.id)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </article>
          ))}
        </div>
      ) : (
        <Card className="border-dashed">
          <CardContent className="flex flex-col items-center justify-center gap-4 py-16 text-center">
            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
              <Sparkles className="h-7 w-7 text-muted-foreground" />
            </div>
            <div className="space-y-1">
              <p className="text-lg font-medium">No banners yet</p>
              <p className="max-w-sm text-sm text-muted-foreground">
                Create your first banner to replace static placeholders on the homepage.
              </p>
            </div>
            <Button asChild>
              <Link href="/admin/banners/create">Create banner</Link>
            </Button>
          </CardContent>
        </Card>
      )}

      <AdminPagination links={banners?.links} />

      <AlertDialog open={deleteId !== null} onOpenChange={(open) => !open && setDeleteId(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete this banner?</AlertDialogTitle>
            <AlertDialogDescription>
              This removes the banner and its image from storage. This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={confirmDelete}
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
};

Index.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Index;
