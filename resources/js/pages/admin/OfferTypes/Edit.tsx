import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { router, useForm } from '@inertiajs/react';

type Props = {
  offerType: any;
  formData: {
    formula_final_price?: string;
    rule_type?: string;
    display_template?: string;
    required_params_str?: string;
    default_values_json?: string;
  };
};

const Edit = ({ offerType, formData }: Props) => {
  const { data, setData, processing, errors } = useForm({
    name: offerType?.name || '',
    display_name: offerType?.display_name || '',
    slug: offerType?.slug || '',
    description: offerType?.description || '',
    formula_final_price: formData?.formula_final_price || '',
    rule_type: formData?.rule_type || '',
    display_template: formData?.display_template || '',
    required_params_str: formData?.required_params_str || '',
    default_values_json: formData?.default_values_json || '',
    is_active: !!offerType?.is_active,
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.put(`/admin/offer-types/${offerType.id}`, data as any);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Edit Offer Type</h1>
          <p className="text-muted-foreground">{offerType?.display_name || offerType?.name}</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/offer-types">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Details</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="space-y-4 max-w-2xl">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1.5">
                <label className="text-sm font-medium">Name</label>
                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
              </div>
              <div className="space-y-1.5">
                <label className="text-sm font-medium">Display name</label>
                <Input value={data.display_name} onChange={(e) => setData('display_name', e.target.value)} />
                {errors.display_name && <p className="text-xs text-destructive">{errors.display_name}</p>}
              </div>
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
              <label className="text-sm font-medium">Formula final price (optional)</label>
              <Input value={data.formula_final_price} onChange={(e) => setData('formula_final_price', e.target.value)} />
              {errors.formula_final_price && <p className="text-xs text-destructive">{errors.formula_final_price}</p>}
            </div>

            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1.5">
                <label className="text-sm font-medium">Rule type (optional)</label>
                <Input value={data.rule_type} onChange={(e) => setData('rule_type', e.target.value)} />
                {errors.rule_type && <p className="text-xs text-destructive">{errors.rule_type}</p>}
              </div>
              <div className="space-y-1.5">
                <label className="text-sm font-medium">Display template (optional)</label>
                <Input value={data.display_template} onChange={(e) => setData('display_template', e.target.value)} />
                {errors.display_template && <p className="text-xs text-destructive">{errors.display_template}</p>}
              </div>
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Required params (comma-separated)</label>
              <Input value={data.required_params_str} onChange={(e) => setData('required_params_str', e.target.value)} />
              {errors.required_params_str && <p className="text-xs text-destructive">{errors.required_params_str}</p>}
            </div>

            <div className="space-y-1.5">
              <label className="text-sm font-medium">Default values JSON (optional)</label>
              <Textarea
                value={data.default_values_json}
                onChange={(e) => setData('default_values_json', e.target.value)}
                placeholder='{"discount_percent": 10}'
              />
              {errors.default_values_json && <p className="text-xs text-destructive">{errors.default_values_json}</p>}
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
                <Link href="/admin/offer-types">Cancel</Link>
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

