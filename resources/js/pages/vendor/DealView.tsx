import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

const DealView = () => {
  const { deal } = usePage().props as any;

  const feature = (deal?.images || []).find((i: any) => i.attribute_name === 'feature_photo');
  const gallery = (deal?.images || []).filter((i: any) => i.attribute_name === 'gallery');

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">{deal?.title}</h1>
          <p className="text-muted-foreground">
            Vendor preview • Deal ID: {deal?.id}
          </p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" asChild>
            <Link href={`/vendor/deals/${deal?.id}/edit`}>Edit</Link>
          </Button>
          <Button asChild>
            <Link href={`/vendor/deals/${deal?.id}/offers`}>Manage offers</Link>
          </Button>
          <Button variant="ghost" asChild>
            <Link href="/vendor/deals">Back</Link>
          </Button>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Deal details</CardTitle>
          <CardDescription>These fields come from the `deals` table.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap items-center gap-2">
            <Badge variant={deal?.status === 'active' ? 'default' : 'secondary'}>
              {deal?.status || 'draft'}
            </Badge>
            {deal?.category?.parent?.name && (
              <Badge variant="outline">{deal.category.parent.name}</Badge>
            )}
            {deal?.category?.name && (
              <Badge variant="outline">{deal.category.name}</Badge>
            )}
          </div>

          <div className="text-sm">
            <span className="text-muted-foreground">Base price:</span>{' '}
            <span className="font-medium">{deal?.basePrice ?? '-'}</span>
          </div>

          {deal?.shortDesc && (
            <div>
              <div className="text-sm font-medium mb-1">Summary</div>
              <div className="text-sm text-muted-foreground" dangerouslySetInnerHTML={{ __html: deal.shortDesc }} />
            </div>
          )}

          {deal?.description && (
            <div>
              <div className="text-sm font-medium mb-1">Description</div>
              <div className="text-sm text-muted-foreground" dangerouslySetInnerHTML={{ __html: deal.description }} />
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Offers</CardTitle>
          <CardDescription>These fields come from `deal_offer_type`.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {Array.isArray(deal?.offers) && deal.offers.length > 0 ? (
            deal.offers.map((o: any) => (
              <div key={o.id} className="border rounded-md p-3 space-y-2">
                <div className="flex items-center justify-between gap-3">
                  <div className="font-medium">{o.display_name}</div>
                  <Badge variant={o.pivot?.status === 'active' ? 'default' : 'secondary'}>
                    {o.pivot?.status || 'active'}
                  </Badge>
                </div>
                <div className="text-sm text-muted-foreground">
                  Original: {o.pivot?.original_price ?? '-'} • Final: {o.pivot?.final_price ?? '-'}
                  {o.pivot?.discountPercentage ? ` • ${o.pivot.discountPercentage}% off` : ''}
                </div>
                <div className="text-xs text-muted-foreground">
                  {o.pivot?.starts_at ? `From: ${o.pivot.starts_at}` : 'From: -'} •{' '}
                  {o.pivot?.ends_at ? `To: ${o.pivot.ends_at}` : 'To: -'}
                </div>
              </div>
            ))
          ) : (
            <p className="text-sm text-muted-foreground">No offers attached yet.</p>
          )}
        </CardContent>
      </Card>

      {(feature || gallery.length) ? (
        <Card>
          <CardHeader>
            <CardTitle>Images</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {feature && (
              <div>
                <div className="text-sm font-medium mb-2">Feature photo</div>
                <img src={feature.image_url} alt="Feature" className="w-full max-w-3xl rounded-md border object-cover" />
              </div>
            )}
            {gallery.length > 0 && (
              <>
                <Separator />
                <div className="text-sm font-medium">Gallery</div>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  {gallery.map((img: any) => (
                    <img key={img.id} src={img.image_url} alt="Gallery" className="aspect-square rounded-md border object-cover" />
                  ))}
                </div>
              </>
            )}
          </CardContent>
        </Card>
      ) : null}
    </div>
  );
};

DealView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default DealView;

