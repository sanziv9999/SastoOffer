import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router, useForm } from '@inertiajs/react';

type Props = {
  subCategory: any;
  primaryCategories: Array<{ id: number; name: string }>;
};

const Edit = ({ subCategory, primaryCategories }: Props) => {
  const { data, setData, processing, errors } = useForm({
    primary_category_id: (subCategory?.primary_category_id ?? '').toString(),
    name: subCategory?.name || '',
    slug: subCategory?.slug || '',
    description: subCategory?.description || '',
    display_order: subCategory?.display_order ?? '',
    is_active: !!subCategory?.is_active,
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.put(`/admin/sub-categories/${subCategory.id}`, data as any);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Edit Sub Category</h1>
          <p className="text-muted-foreground">{subCategory?.name}</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/sub-categories">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="space-y-4 max-w-2xl">
            <div className="space-y-1.5">
              <label className="text-sm font-medium">Primary category</label>
              <Select value={data.primary_category_id} onValueChange={(v) => setData('primary_category_id', v)}>
                <SelectTrigger>
                  <SelectValue placeholder="Select primary category" />
                </SelectTrigger>
                <SelectContent>
                  {primaryCategories.map((c) => (
                    <SelectItem key={c.id} value={c.id.toString()}>
                      {c.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {errors.primary_category_id && <p className="text-xs text-destructive">{errors.primary_category_id}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Name</label>
              <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
              {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Slug (optional)</label>
              <Input value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
              {errors.slug && <p className="text-xs text-destructive">{errors.slug}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Description (optional)</label>
              <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} />
              {errors.description && <p className="text-xs text-destructive">{errors.description}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Display order (optional)</label>
              <Input type="number" value={data.display_order} onChange={(e) => setData('display_order', e.target.value)} />
              {errors.display_order && <p className="text-xs text-destructive">{errors.display_order}</p>}
            </div>

            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" checked={!!data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
              Active
            </label>
            {errors.is_active && <p className="text-xs text-destructive">{errors.is_active}</p>}

            <div className="flex gap-2">
              <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : 'Save'}
              </Button>
              <Button type="button" variant="outline" asChild>
                <Link href="/admin/sub-categories">Cancel</Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
};

Edit.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Edit;

