import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
  Gift,
  Utensils,
  Scissors,
  Coffee,
  Plane,
  Smartphone,
  Heart,
  BookOpen,
} from 'lucide-react';

type PageProps = {
  parentOptions: Array<{ id: number; name: string }>;
};

const Create = () => {
  const { parentOptions } = usePage<PageProps>().props;

  const { data, setData, post, processing, errors } = useForm({
    name: '',
    slug: '',
    icon_key: 'gift',
    description: '',
    display_order: '',
    is_active: true,
    parent_id: '' as number | '' | null,
    image: null as File | null,
  });
  const [imagePreview, setImagePreview] = useState<string | null>(null);

  const iconOptions: Array<{ key: string; label: string; icon: React.ReactNode }> = [
    { key: 'gift', label: 'Default (Gift)', icon: <Gift className="h-4 w-4" /> },
    { key: 'utensils', label: 'Food', icon: <Utensils className="h-4 w-4" /> },
    { key: 'scissors', label: 'Beauty', icon: <Scissors className="h-4 w-4" /> },
    { key: 'coffee', label: 'Activities', icon: <Coffee className="h-4 w-4" /> },
    { key: 'plane', label: 'Travel', icon: <Plane className="h-4 w-4" /> },
    { key: 'smartphone', label: 'Electronics', icon: <Smartphone className="h-4 w-4" /> },
    { key: 'heart', label: 'Health', icon: <Heart className="h-4 w-4" /> },
    { key: 'book', label: 'Education', icon: <BookOpen className="h-4 w-4" /> },
  ];

  useEffect(() => {
    if (!data.image) {
      setImagePreview(null);
      return;
    }
    const objectUrl = URL.createObjectURL(data.image);
    setImagePreview(objectUrl);
    return () => URL.revokeObjectURL(objectUrl);
  }, [data.image]);

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admin/primary-categories', { forceFormData: true });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Create Category</h1>
          <p className="text-muted-foreground">
            Create a top-level category or assign a parent to make it a sub-category.
          </p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/primary-categories">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="space-y-4 max-w-2xl">
            <div className="space-y-1.5">
              <label className="text-sm font-medium">Parent category (optional)</label>
              <select
                className="border rounded-md px-3 py-2 text-sm w-full"
                value={data.parent_id ?? ''}
                onChange={(e) =>
                  setData('parent_id', e.target.value ? Number(e.target.value) : '')
                }
              >
                <option value="">— No parent (top-level) —</option>
                {parentOptions?.map((opt) => (
                  <option key={opt.id} value={opt.id}>
                    {opt.name}
                  </option>
                ))}
              </select>
              {errors.parent_id && (
                <p className="text-xs text-destructive">{errors.parent_id}</p>
              )}
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
              <label className="text-sm font-medium">Icon</label>
              <div className="flex items-center gap-3">
                <div className="h-9 w-9 rounded-md border bg-muted flex items-center justify-center">
                  {iconOptions.find((o) => o.key === data.icon_key)?.icon ?? <Gift className="h-4 w-4" />}
                </div>
                <select
                  className="border rounded-md px-3 py-2 text-sm w-full"
                  value={data.icon_key}
                  onChange={(e) => setData('icon_key', e.target.value)}
                >
                  {iconOptions.map((opt) => (
                    <option key={opt.key} value={opt.key}>
                      {opt.label}
                    </option>
                  ))}
                </select>
              </div>
              {errors.icon_key && <p className="text-xs text-destructive">{errors.icon_key}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Description (optional)</label>
              <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} />
              {errors.description && <p className="text-xs text-destructive">{errors.description}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Category image (optional)</label>
              <Input
                type="file"
                accept="image/*"
                onChange={(e) => setData('image', e.target.files?.[0] || null)}
              />
              {imagePreview && (
                <img
                  src={imagePreview}
                  alt="Category preview"
                  className="h-20 w-20 rounded-md border object-cover"
                />
              )}
              {errors.image && <p className="text-xs text-destructive">{errors.image}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Display order (optional)</label>
              <Input
                type="number"
                value={data.display_order}
                onChange={(e) => setData('display_order', e.target.value)}
              />
              {errors.display_order && <p className="text-xs text-destructive">{errors.display_order}</p>}
            </div>

            <label className="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                checked={!!data.is_active}
                onChange={(e) => setData('is_active', e.target.checked)}
              />
              Active
            </label>
            {errors.is_active && <p className="text-xs text-destructive">{errors.is_active}</p>}

            <div className="flex gap-2">
              <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : 'Create'}
              </Button>
              <Button type="button" variant="outline" asChild>
                <Link href="/admin/primary-categories">Cancel</Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
};

Create.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Create;

