import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';

const DealView = () => {
  const { deal } = usePage().props as any;

  const formatRs = (value: any) => {
    if (value === null || value === undefined || value === '') return '-';
    const n = Number(value);
    if (Number.isNaN(n)) return '-';
    return `Rs. ${n.toFixed(2)}`;
  };

  const allImages = [...(deal?.images || [])].sort(
    (a: any, b: any) => Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0)
  );
  const feature =
    allImages.find((i: any) => i.attribute_name === 'feature_photo') ||
    allImages[0] ||
    null;
  const gallery = allImages.filter((i: any) => i.id !== feature?.id);

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
          <CardTitle>Deal overview</CardTitle>
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

          <div className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
            <div className="text-sm">
              <span className="text-muted-foreground">Base price:</span>{' '}
              <span className="font-medium">{formatRs(deal?.basePrice)}</span>
            </div>
            <div className="text-xs text-muted-foreground">
              Deal ID: <span className="text-foreground font-medium">{deal?.id}</span>
            </div>
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
          <div className="flex items-start justify-between gap-4">
            <div>
              <CardTitle>Offers</CardTitle>
              <CardDescription>These fields come from `deal_offer_type`.</CardDescription>
            </div>
            <Badge variant="outline">{Array.isArray(deal?.offers) ? deal.offers.length : 0} attached</Badge>
          </div>
        </CardHeader>
        <CardContent>
          {Array.isArray(deal?.offers) && deal.offers.length > 0 ? (
            <div className="rounded-md border overflow-hidden">
              <div className="relative w-full overflow-auto">
                <table className="w-full caption-bottom text-sm">
                  <thead className="border-b bg-muted/20">
                    <tr>
                      <th className="h-12 px-4 text-left align-middle font-medium">Offer</th>
                      <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                      <th className="h-12 px-4 text-left align-middle font-medium">Original</th>
                      <th className="h-12 px-4 text-left align-middle font-medium">Final</th>
                      <th className="h-12 px-4 text-left align-middle font-medium">Discount</th>
                      <th className="h-12 px-4 text-left align-middle font-medium">Validity</th>
                    </tr>
                  </thead>
                  <tbody>
                    {deal.offers.map((o: any) => (
                      <tr key={o.id} className="border-b transition-colors hover:bg-muted/50">
                        <td className="p-4 align-middle">
                          <div className="font-medium">{o.display_name}</div>
                          <div className="text-xs text-muted-foreground">#{o.id}</div>
                        </td>
                        <td className="p-4 align-middle">
                          <Badge variant={o.pivot?.status === 'active' ? 'default' : 'secondary'}>
                            {o.pivot?.status || 'active'}
                          </Badge>
                        </td>
                        <td className="p-4 align-middle">{formatRs(o.pivot?.original_price)}</td>
                        <td className="p-4 align-middle">{formatRs(o.pivot?.final_price)}</td>
                        <td className="p-4 align-middle">
                          {o.pivot?.discountPercentage !== null &&
                          o.pivot?.discountPercentage !== undefined &&
                          String(o.pivot?.discountPercentage) !== '' ? (
                            <span className="text-sm">{o.pivot.discountPercentage}%</span>
                          ) : (
                            <span className="text-muted-foreground">-</span>
                          )}
                        </td>
                        <td className="p-4 align-middle text-xs text-muted-foreground">
                          {o.pivot?.starts_at ? `From ${o.pivot.starts_at}` : 'From -'} ·{' '}
                          {o.pivot?.ends_at ? `To ${o.pivot.ends_at}` : 'To -'}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
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
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <div className="text-sm font-medium mb-2">Feature photo</div>
                {feature ? (
                  <div className="overflow-hidden rounded-md border bg-muted">
                    <img src={feature.image_url} alt="Feature" className="w-full aspect-video object-cover" />
                  </div>
                ) : (
                  <div className="h-[220px] rounded-md border bg-muted flex items-center justify-center text-sm text-muted-foreground">
                    No feature photo
                  </div>
                )}
              </div>
              <div>
                <div className="text-sm font-medium mb-2">Gallery</div>
                {gallery.length > 0 ? (
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {gallery.map((img: any) => (
                      <div key={img.id} className="overflow-hidden rounded-md border bg-muted">
                        <img src={img.image_url} alt="Gallery" className="aspect-square w-full object-cover" />
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-sm text-muted-foreground">No gallery images.</div>
                )}
              </div>
            </div>
            <Separator />
            <div className="text-xs text-muted-foreground">
              Tip: Use <span className="text-foreground font-medium">Edit</span> to manage deal images and content.
            </div>
          </CardContent>
        </Card>
      ) : null}
    </div>
  );
};

DealView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default DealView;

