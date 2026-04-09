import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useForm } from '@inertiajs/react';
import BannerFields, { BannerFormData, CategoryOption } from './BannerFields';
import type { BannerRow } from './Index';

type Props = {
  banner: BannerRow;
  categoryOptions: CategoryOption[];
};

const Edit = ({ banner, categoryOptions }: Props) => {
  const { data, setData, put, processing, errors } = useForm<BannerFormData>({
    title: banner.title,
    text: banner.text ?? '',
    is_featured: banner.is_featured,
    sort_order: banner.sort_order,
    category_id: banner.category_id ?? '',
    image: null,
    remove_image: false,
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/admin/banners/${banner.id}`, { preserveScroll: true });
  };

  return (
    <div className="mx-auto max-w-5xl space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Edit banner</h1>
          <p className="text-muted-foreground">Update copy, visibility, or replace the hero image.</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/banners">Back to list</Link>
        </Button>
      </div>

      <form onSubmit={onSubmit} className="space-y-6">
        <Card className="overflow-hidden border-muted/60 shadow-sm">
          <CardHeader className="border-b bg-muted/20">
            <CardTitle>Banner details</CardTitle>
            <CardDescription>Changes apply to the landing page as soon as you save.</CardDescription>
          </CardHeader>
          <CardContent className="pt-6">
            <BannerFields
              data={data}
              setData={setData}
              errors={errors}
              existingImageUrl={banner.image_url}
              categoryOptions={categoryOptions}
            />
          </CardContent>
        </Card>

        <div className="flex flex-wrap gap-3">
          <Button type="submit" size="lg" disabled={processing}>
            {processing ? 'Saving…' : 'Save changes'}
          </Button>
          <Button type="button" variant="outline" asChild>
            <Link href="/admin/banners">Cancel</Link>
          </Button>
        </div>
      </form>
    </div>
  );
};

Edit.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Edit;
