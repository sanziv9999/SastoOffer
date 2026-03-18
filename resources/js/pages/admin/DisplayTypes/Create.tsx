import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/react';

const Create = () => {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admin/display-types');
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Create Display Type</h1>
          <p className="text-muted-foreground">Add a new display type name.</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/display-types">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="space-y-4 max-w-xl">
            <div className="space-y-1.5">
              <label className="text-sm font-medium">Name</label>
              <Input value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="featured" />
              {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
            </div>

            <div className="flex gap-2">
              <Button type="submit" disabled={processing}>{processing ? 'Saving...' : 'Create'}</Button>
              <Button type="button" variant="outline" asChild>
                <Link href="/admin/display-types">Cancel</Link>
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

