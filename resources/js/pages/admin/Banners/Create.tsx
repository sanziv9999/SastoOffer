import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useForm } from '@inertiajs/react';
import BannerFields, { BannerFormData, CategoryOption } from './BannerFields';

type PageProps = {
  categoryOptions: CategoryOption[];
};

const Create = ({ categoryOptions }: PageProps) => {
  const { data, setData, post, processing, errors } = useForm<BannerFormData>({
    title: '',
    text: '',
    is_featured: true,
    sort_order: 0,
    category_id: '',
    image: null,
    remove_image: false,
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admin/banners');
  };

  return (
    <div className="mx-auto max-w-5xl space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Create banner</h1>
          <p className="text-muted-foreground">Add a slide for the homepage hero carousel.</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/banners">Back to list</Link>
        </Button>
      </div>

      <form onSubmit={onSubmit} className="space-y-6">
        <Card className="overflow-hidden border-muted/60 shadow-sm">
          <CardHeader className="border-b bg-muted/20">
            <CardTitle>Banner details</CardTitle>
            <CardDescription>Title and copy appear over the image on wide screens; keep text short for readability.</CardDescription>
          </CardHeader>
          <CardContent className="pt-6">
            <BannerFields
              data={data}
              setData={setData}
              errors={errors}
              categoryOptions={categoryOptions}
            />
          </CardContent>
        </Card>

        <div className="flex flex-wrap gap-3">
          <Button type="submit" size="lg" disabled={processing}>
            {processing ? 'Saving…' : 'Create banner'}
          </Button>
          <Button type="button" variant="outline" asChild>
            <Link href="/admin/banners">Cancel</Link>
          </Button>
        </div>
      </form>
    </div>
  );
};

Create.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Create;
