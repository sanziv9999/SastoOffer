import { useMemo, useState } from 'react';
import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { ChevronRight, ExternalLink, ImageIcon, Package, Tag } from 'lucide-react';

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
  const previewImages = useMemo(() => (feature ? [feature, ...gallery] : gallery), [feature, gallery]);
  const [selectedImageId, setSelectedImageId] = useState<number | null>(feature?.id ?? previewImages[0]?.id ?? null);
  const selectedImage = previewImages.find((img: any) => img.id === selectedImageId) ?? previewImages[0] ?? null;
  const activeOffers = Array.isArray(deal?.offers)
    ? deal.offers.filter((o: any) => (o.pivot?.status || 'active') === 'active').length
    : 0;

  return (
    <div className="space-y-8">
      <div className="space-y-3">
        <nav className="flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
          <Link href="/vendor/deals" className="hover:text-foreground transition-colors">
            Deals
          </Link>
          <ChevronRight className="h-3.5 w-3.5 opacity-60" />
          <span className="text-foreground font-medium truncate max-w-[min(100%,28rem)]">{deal?.title}</span>
        </nav>
        <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
          <div className="space-y-2">
            <div className="flex flex-wrap items-center gap-2">
              <Badge variant={deal?.status === 'active' ? 'default' : 'secondary'} className="capitalize">
                {deal?.status || 'draft'}
              </Badge>
              {deal?.category?.parent?.name && (
                <Badge variant="outline">{deal.category.parent.name}</Badge>
              )}
              {deal?.category?.name && (
                <Badge variant="outline">{deal.category.name}</Badge>
              )}
            </div>
            <h1 className="text-2xl sm:text-3xl font-bold tracking-tight">{deal?.title}</h1>
            <p className="text-muted-foreground text-sm">
              Vendor preview • Deal ID: <span className="font-medium text-foreground">{deal?.id}</span>
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" asChild>
              <Link href={`/vendor/deals/${deal?.id}/edit`}>Edit details</Link>
            </Button>
            <Button asChild>
              <Link href={`/vendor/deals/${deal?.id}/offers`}>Manage offers</Link>
            </Button>
            <Button variant="ghost" asChild>
              <Link href="/vendor/deals">Back</Link>
            </Button>
          </div>
        </div>
      </div>

      <Card className="overflow-hidden border-border/80 shadow-sm">
        <CardContent className="p-0">
          <div className="grid lg:grid-cols-12 gap-0">
            <div className="lg:col-span-5 border-b lg:border-b-0 lg:border-r border-border/70 bg-muted/20 p-5 sm:p-6">
              <div className="space-y-4">
                <div className="relative aspect-[4/3] w-full overflow-hidden rounded-xl border bg-muted">
                  {selectedImage ? (
                    <img src={selectedImage.image_url} alt={deal?.title || 'Deal'} className="h-full w-full object-cover" />
                  ) : (
                    <div className="h-full w-full flex flex-col items-center justify-center gap-2 text-muted-foreground">
                      <ImageIcon className="h-8 w-8 opacity-50" />
                      <span className="text-sm">No image uploaded</span>
                    </div>
                  )}
                </div>
                {previewImages.length > 1 && (
                  <div className="flex gap-2 overflow-x-auto pb-1">
                    {previewImages.map((img: any) => (
                      <button
                        key={img.id}
                        type="button"
                        onClick={() => setSelectedImageId(img.id)}
                        className={cn(
                          'h-14 w-14 rounded-lg border-2 overflow-hidden shrink-0 transition-all',
                          selectedImage?.id === img.id
                            ? 'border-primary ring-2 ring-primary/20'
                            : 'border-transparent opacity-80 hover:opacity-100'
                        )}
                      >
                        <img src={img.image_url} alt="" className="h-full w-full object-cover" />
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div className="lg:col-span-7 p-5 sm:p-6 flex flex-col justify-between gap-5">
              <div className="space-y-3">
                <div className="text-xs font-semibold tracking-wide uppercase text-muted-foreground">
                  Deal overview
                </div>
                <div className="rounded-xl border bg-muted/20 p-4">
                  <div className="text-[11px] uppercase tracking-wide text-muted-foreground">Base price</div>
                  <div className="text-3xl font-bold tracking-tight tabular-nums">{formatRs(deal?.basePrice)}</div>
                  <div className="text-xs text-muted-foreground mt-1">This is the source amount for offer calculations.</div>
                </div>
              </div>

              <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                <div className="rounded-lg border p-3 bg-background">
                  <div className="text-xs text-muted-foreground">Total offers</div>
                  <div className="text-xl font-semibold">{Array.isArray(deal?.offers) ? deal.offers.length : 0}</div>
                </div>
                <div className="rounded-lg border p-3 bg-background">
                  <div className="text-xs text-muted-foreground">Active offers</div>
                  <div className="text-xl font-semibold">{activeOffers}</div>
                </div>
                <div className="rounded-lg border p-3 bg-background col-span-2 sm:col-span-1">
                  <div className="text-xs text-muted-foreground">Images</div>
                  <div className="text-xl font-semibold">{previewImages.length}</div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Description</CardTitle>
          <CardDescription>Content customers see before purchasing.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-5">
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

      <Card className="border-border/80 shadow-sm">
        <CardHeader>
          <div className="flex items-start justify-between gap-4">
            <div>
              <CardTitle>Offers</CardTitle>
              <CardDescription>Attached offer types and computed pricing.</CardDescription>
            </div>
            <Badge variant="outline">{Array.isArray(deal?.offers) ? deal.offers.length : 0} attached</Badge>
          </div>
        </CardHeader>
        <CardContent>
          {Array.isArray(deal?.offers) && deal.offers.length > 0 ? (
            <div className="space-y-3">
              {/* Mobile: stacked offer cards */}
              <div className="md:hidden space-y-3">
                {deal.offers.map((o: any) => (
                  <div key={o.id} className="rounded-lg border p-3 space-y-3">
                    <div className="flex items-start justify-between gap-2">
                      <div className="min-w-0">
                        <div className="font-medium text-sm flex items-center gap-1.5">
                          <Tag className="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                          <span className="truncate">{o.display_name}</span>
                        </div>
                        <div className="text-xs text-muted-foreground mt-1">#{o.id}</div>
                      </div>
                      <Badge variant={o.pivot?.status === 'active' ? 'default' : 'secondary'}>
                        {o.pivot?.status || 'active'}
                      </Badge>
                    </div>

                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Original</p>
                        <p className="font-semibold tabular-nums">{formatRs(o.pivot?.original_price)}</p>
                      </div>
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Final</p>
                        <p className="font-semibold tabular-nums">{formatRs(o.pivot?.final_price)}</p>
                      </div>
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Discount</p>
                        {o.pivot?.discountPercentage !== null &&
                        o.pivot?.discountPercentage !== undefined &&
                        String(o.pivot?.discountPercentage) !== '' ? (
                          <p className="font-semibold text-emerald-600">{o.pivot.discountPercentage}%</p>
                        ) : (
                          <p className="text-muted-foreground">-</p>
                        )}
                      </div>
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Validity</p>
                        <p className="text-[11px] text-foreground">
                          {o.pivot?.starts_at ? `From ${o.pivot.starts_at}` : 'From -'}
                        </p>
                        <p className="text-[11px] text-foreground">
                          {o.pivot?.ends_at ? `To ${o.pivot.ends_at}` : 'To -'}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Desktop/tablet: table */}
              <div className="hidden md:block rounded-md border overflow-hidden">
                <div className="relative w-full overflow-auto">
                  <table className="w-full caption-bottom text-sm min-w-[760px]">
                    <thead className="border-b bg-muted/30">
                      <tr>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Offer</th>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Status</th>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Original</th>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Final</th>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Discount</th>
                        <th className="h-12 px-4 text-left align-middle font-medium text-xs uppercase tracking-wide text-muted-foreground">Validity</th>
                      </tr>
                    </thead>
                    <tbody>
                      {deal.offers.map((o: any) => (
                        <tr key={o.id} className="border-b transition-colors hover:bg-muted/50">
                          <td className="p-4 align-middle">
                            <div className="font-medium flex items-center gap-2">
                              <Tag className="h-3.5 w-3.5 text-muted-foreground" />
                              {o.display_name}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">#{o.id}</div>
                          </td>
                          <td className="p-4 align-middle">
                            <Badge variant={o.pivot?.status === 'active' ? 'default' : 'secondary'}>
                              {o.pivot?.status || 'active'}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle font-medium tabular-nums">{formatRs(o.pivot?.original_price)}</td>
                          <td className="p-4 align-middle font-semibold tabular-nums">{formatRs(o.pivot?.final_price)}</td>
                          <td className="p-4 align-middle">
                            {o.pivot?.discountPercentage !== null &&
                            o.pivot?.discountPercentage !== undefined &&
                            String(o.pivot?.discountPercentage) !== '' ? (
                              <span className="text-sm font-medium text-emerald-600">{o.pivot.discountPercentage}%</span>
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
            </div>
          ) : (
            <p className="text-sm text-muted-foreground">No offers attached yet.</p>
          )}
          <div className="mt-4">
            <Button asChild>
              <Link href={`/vendor/deals/${deal?.id}/offers`} className="inline-flex items-center gap-1.5">
                Open offer management
                <ExternalLink className="h-3.5 w-3.5" />
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>

      {(feature || gallery.length) ? (
        <Card>
          <CardHeader>
            <CardTitle>All images</CardTitle>
            <CardDescription>Quick visual check for media quality and coverage.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <div className="text-sm font-medium mb-2">Feature photo</div>
                {feature ? (
                  <div className="overflow-hidden rounded-md border bg-muted">
                    <img src={feature.image_url} alt={deal?.title || 'Feature'} className="w-full aspect-video object-cover" />
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
                        <img src={img.image_url} alt={deal?.title || 'Gallery'} className="aspect-square w-full object-cover" />
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

