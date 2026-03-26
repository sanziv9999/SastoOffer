import { useEffect, useMemo, useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { ChevronRight, ImageIcon, Package, ExternalLink } from 'lucide-react';

const toUIType = (offerTypeName: string): 'percentage' | 'fixed' | 'bogo' | 'flash' | 'bundle' | 'first_x' => {
  if (offerTypeName === 'percentage_discount') return 'percentage';
  if (offerTypeName === 'first_x_customers_percentage_discount') return 'first_x';
  if (offerTypeName === 'fixed_amount_discount') return 'fixed';
  if (offerTypeName === 'bogo') return 'bogo';
  if (offerTypeName === 'flash_sale') return 'flash';
  if (offerTypeName === 'bundle') return 'bundle';
  return 'fixed';
};

const getPivotParamNumber = (pivot: any, key: string): number | null => {
  if (!pivot) return null;
  if (typeof pivot[key] === 'number') return pivot[key];
  if (typeof pivot[key] === 'string' && pivot[key].trim() !== '') return Number(pivot[key]);
  const params = pivot.params;
  if (params && typeof params === 'object') {
    const v = (params as any)[key];
    if (typeof v === 'number') return v;
    if (typeof v === 'string' && v.trim() !== '') return Number(v);
  }
  return null;
};

interface DealImageRow {
  id: number;
  url: string;
  label?: string | null;
}

interface DealContext {
  id: number;
  title: string;
  slug?: string;
  basePrice: number | null;
  status?: string;
  shortDescription?: string | null;
  featuredImage?: string;
  images?: DealImageRow[];
}

const formatRs = (n: number | null | undefined) => {
  if (n == null || Number.isNaN(Number(n))) return '—';
  return `Rs. ${Number(n).toFixed(2)}`;
};

const DealOffers = () => {
  const { deal: dealProp, offerTypes, attachedOffers } = usePage().props as {
    deal: DealContext;
    offerTypes: any[];
    attachedOffers: any[];
  };
  const deal = dealProp;
  const basePrice = Number(deal?.basePrice ?? 0);

  const dealImages = useMemo(() => {
    const list = deal?.images;
    if (Array.isArray(list) && list.length > 0) {
      return list.filter((im: DealImageRow) => im?.url);
    }
    if (deal?.featuredImage) {
      return [{ id: 0, url: deal.featuredImage, label: 'Featured' }];
    }
    return [];
  }, [deal]);

  const [activeImageIdx, setActiveImageIdx] = useState(0);

  useEffect(() => {
    setActiveImageIdx(0);
  }, [deal?.id]);

  const activeImageUrl = dealImages[activeImageIdx]?.url ?? '';

  const offerTypesById = useMemo(() => {
    const map = new Map<number, any>();
    (offerTypes || []).forEach((o: any) => map.set(Number(o.id), o));
      return map;
  }, [offerTypes]);

  const { data, setData, post, processing, errors, reset } = useForm({
    offer_type_id: offerTypes?.[0]?.id?.toString() || '',
    // original_price is derived from deal.basePrice (no manual entry)
    original_price: deal?.basePrice?.toString?.() || '',
    currency_code: 'NPR',
    status: 'active',
    starts_at: '',
    ends_at: '',
    // simplified params
    discount_percent: '',
    offer_price: '',
    first_x_customers: '',
  });

  const selectedOfferType = offerTypesById.get(Number(data.offer_type_id));
  const uiType = toUIType(String(selectedOfferType?.name ?? ''));

  const [editingOfferTypeId, setEditingOfferTypeId] = useState<number | null>(null);
  const { data: editData, setData: setEditData, processing: editProcessing, errors: editErrors, put: putEdit, reset: resetEdit } = useForm({
    offer_type_id: '',
    original_price: deal?.basePrice?.toString?.() || '',
    currency_code: 'NPR',
    status: 'active',
    starts_at: '',
    ends_at: '',
    discount_percent: '',
    offer_price: '',
    first_x_customers: '',
  });

  const editingOfferType = offerTypesById.get(Number(editData.offer_type_id));
  const editingUiType = toUIType(String(editingOfferType?.name ?? ''));

  const buildParams = () => {
    if (uiType === 'percentage') {
      const pct = Number(data.discount_percent || 0);
      return { discount_percent: pct };
    }
    if (uiType === 'first_x') {
      const pct = Number(data.discount_percent || 0);
      const firstX = Number(data.first_x_customers || 0);
      return {
        discount_percent: pct,
        first_x_customers: firstX,
      };
    }
    if (uiType === 'fixed' || uiType === 'flash' || uiType === 'bundle') {
      const offerPrice = Number(data.offer_price || 0);
      const disc = Math.max(0, basePrice - offerPrice);
      return { discount_amount: disc };
    }
    if (uiType === 'bogo') {
      return { buy_quantity: 1, get_quantity: 1, get_discount_percent: 100 };
    }
    return {};
  };

  const buildEditParams = () => {
    if (editingUiType === 'percentage') {
      const pct = Number(editData.discount_percent || 0);
      return { discount_percent: pct };
    }
    if (editingUiType === 'first_x') {
      const pct = Number(editData.discount_percent || 0);
      const firstX = Number(editData.first_x_customers || 0);
      return {
        discount_percent: pct,
        first_x_customers: firstX,
      };
    }
    if (editingUiType === 'fixed' || editingUiType === 'flash' || editingUiType === 'bundle') {
      const offerPrice = Number(editData.offer_price || 0);
      const disc = Math.max(0, basePrice - offerPrice);
      return { discount_amount: disc };
    }
    if (editingUiType === 'bogo') {
      return { buy_quantity: 1, get_quantity: 1, get_discount_percent: 100 };
    }
    return {};
  };

  const onAdd = (e: React.FormEvent) => {
    e.preventDefault();
    post(`/vendor/deals/${deal.id}/offers`, {
      preserveScroll: true,
      data: {
        ...data,
        // ensure derived params are posted
        params: buildParams(),
      },
      onSuccess: () => {
        toast.success('Offer added.');
        reset('discount_percent', 'offer_price', 'first_x_customers');
      },
    } as any);
  };

  const startEdit = (o: any) => {
    const pivot = o?.pivot ?? {};
    const offerTypeId = Number(o?.id);
    const ui = toUIType(String(offerTypesById.get(offerTypeId)?.name ?? o?.name ?? ''));

    // Derive edit field values from pivot:
    const discountPercent = getPivotParamNumber(pivot, 'discount_percent');
    const firstXCustomers = getPivotParamNumber(pivot, 'first_x_customers');
    const finalPrice = pivot?.final_price != null ? Number(pivot.final_price) : null;

    setEditingOfferTypeId(offerTypeId);
    setEditData({
      offer_type_id: String(offerTypeId),
      original_price: (pivot?.original_price ?? deal?.basePrice ?? '').toString(),
      currency_code: (pivot?.currency_code ?? 'NPR').toString(),
      status: (pivot?.status ?? 'active').toString(),
      starts_at: (pivot?.starts_at ?? '').toString(),
      ends_at: (pivot?.ends_at ?? '').toString(),
      discount_percent: ui === 'percentage' && discountPercent != null ? String(discountPercent) : '',
      first_x_customers: ui === 'first_x' && firstXCustomers != null ? String(firstXCustomers) : '',
      offer_price: (ui === 'fixed' || ui === 'flash' || ui === 'bundle') && finalPrice != null ? String(finalPrice) : '',
    } as any);
  };

  const cancelEdit = () => {
    setEditingOfferTypeId(null);
    resetEdit();
  };

  const onUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    const offerTypeId = Number(editData.offer_type_id);
    if (!offerTypeId) return;
    const currentId = Number(editingOfferTypeId);

    // Always update the CURRENT attached offer route; backend will replace if offer_type_id changed.
    if (!currentId) return;

    putEdit(`/vendor/deals/${deal.id}/offers/${currentId}`, {
      preserveScroll: true,
      data: {
        ...editData,
        offer_type_id: String(offerTypeId),
        params: buildEditParams(),
      },
      onSuccess: () => {
        toast.success(offerTypeId !== currentId ? 'Offer type replaced.' : 'Offer updated.');
        setEditingOfferTypeId(null);
        resetEdit();
      },
    } as any);
  };

  const onRemove = (offerTypeId: number) => {
    if (!confirm('Remove this offer from the deal?')) return;
    router.delete(`/vendor/deals/${deal.id}/offers/${offerTypeId}`, {
      preserveScroll: true,
      onSuccess: () => toast.success('Offer removed.'),
    });
  };

  const dealStatus = deal?.status ?? 'draft';
  const statusVariant =
    dealStatus === 'active' ? 'default' : dealStatus === 'draft' ? 'secondary' : 'outline';

  return (
    <div className="space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-2 min-w-0">
          <nav className="flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
            <Link href="/vendor/deals" className="hover:text-foreground transition-colors">
              Manage deals
            </Link>
            <ChevronRight className="h-3.5 w-3.5 shrink-0 opacity-60" />
            <span className="text-foreground font-medium truncate max-w-[min(100%,28rem)]" title={deal?.title}>
              {deal?.title}
            </span>
            <ChevronRight className="h-3.5 w-3.5 shrink-0 opacity-60" />
            <span className="text-foreground">Offers</span>
          </nav>
          <h1 className="text-2xl font-bold tracking-tight">Offer management</h1>
          <p className="text-muted-foreground text-sm max-w-2xl">
            Configure promotion types and prices for this deal. Your catalog base price is the anchor for all calculations below.
          </p>
        </div>
        <Button variant="outline" className="shrink-0" asChild>
          <Link href="/vendor/deals">Back to deals</Link>
        </Button>
      </div>

      <Card className="overflow-hidden border-border/80 shadow-md">
        <CardContent className="p-0">
          <div className="grid lg:grid-cols-12 gap-0">
            <div className="lg:col-span-5 border-b lg:border-b-0 lg:border-r border-border/80 bg-muted/25">
              <div className="p-5 sm:p-6 lg:p-8 space-y-4">
                <div className="relative aspect-[4/3] w-full overflow-hidden rounded-2xl border border-border/60 bg-muted shadow-inner">
                  {activeImageUrl ? (
                    <img
                      src={activeImageUrl}
                      alt={deal?.title ? `${deal.title} — product` : 'Deal product'}
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <div className="flex h-full w-full flex-col items-center justify-center gap-2 text-muted-foreground p-6 text-center">
                      <Package className="h-12 w-12 opacity-40" />
                      <p className="text-sm">No product images yet</p>
                      <Button variant="secondary" size="sm" asChild>
                        <Link href={route('vendor.deals.edit', deal.id)}>Add images in deal editor</Link>
                      </Button>
                    </div>
                  )}
                </div>
                {dealImages.length > 1 && (
                  <div className="space-y-2">
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground flex items-center gap-1.5">
                      <ImageIcon className="h-3.5 w-3.5" />
                      Gallery
                    </p>
                    <div className="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
                      {dealImages.map((im, idx) => (
                        <button
                          key={`${im.id}-${idx}`}
                          type="button"
                          title={im.label ? String(im.label) : `Image ${idx + 1}`}
                          onClick={() => setActiveImageIdx(idx)}
                          className={cn(
                            'relative h-14 w-14 shrink-0 overflow-hidden rounded-lg border-2 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            idx === activeImageIdx
                              ? 'border-primary ring-2 ring-primary/20'
                              : 'border-transparent opacity-80 hover:opacity-100'
                          )}
                        >
                          <img src={im.url} alt="" className="h-full w-full object-cover" />
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>

            <div className="lg:col-span-7 p-5 sm:p-6 lg:p-8 flex flex-col justify-center gap-5">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5">
                  Parent product
                </p>
                <h2 className="text-xl sm:text-2xl font-semibold tracking-tight text-foreground leading-tight">
                  {deal?.title}
                </h2>
                {deal?.shortDescription && (
                  <p className="mt-2 text-sm text-muted-foreground leading-relaxed line-clamp-3">
                    {deal.shortDescription}
                  </p>
                )}
              </div>

              <div className="flex flex-wrap items-center gap-2">
                <Badge variant={statusVariant} className="capitalize">
                  {dealStatus}
                </Badge>
                <span className="text-xs text-muted-foreground font-mono">ID #{deal?.id}</span>
              </div>

              <div className="rounded-2xl border border-border/80 bg-gradient-to-br from-muted/60 via-muted/30 to-background p-5 sm:p-6 shadow-sm">
                <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">
                  Catalog base price
                </p>
                <p className="text-3xl sm:text-4xl font-bold tabular-nums tracking-tight text-foreground">
                  {formatRs(deal?.basePrice)}
                </p>
                <p className="mt-3 text-xs text-muted-foreground leading-relaxed max-w-md">
                  Percentage discounts and final offer prices are calculated from this figure. Update it in deal details if your product price changes.
                </p>
                {deal?.basePrice == null && (
                  <p className="mt-3 text-sm text-amber-700 dark:text-amber-300 font-medium">
                    Set a base price on the deal before offers can compute correctly.
                  </p>
                )}
              </div>

              <div className="flex flex-wrap gap-2 pt-1">
                <Button variant="default" asChild>
                  <Link href={route('vendor.deals.edit', deal.id)}>Edit deal details</Link>
                </Button>
                <Button variant="outline" asChild>
                  <Link href={route('vendor.deals.view', deal.id)} className="inline-flex items-center gap-1.5">
                    Preview deal
                    <ExternalLink className="h-3.5 w-3.5 opacity-70" />
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Attached offers</CardTitle>
          <CardDescription>All offer types applied to this deal.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {Array.isArray(attachedOffers) && attachedOffers.length > 0 ? (
            <>
              <div className="rounded-md border overflow-hidden">
                <div className="relative w-full overflow-auto">
                  <table className="w-full caption-bottom text-sm">
                    <thead className="border-b bg-muted/20">
                      <tr>
                        <th className="h-12 px-4 text-left align-middle font-medium">Offer</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Original</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Final</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Validity</th>
                        <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {attachedOffers.map((o: any) => (
                        <tr key={o.id} className="border-b transition-colors hover:bg-muted/50">
                          <td className="p-4 align-middle">
                            <div className="font-medium truncate">{o.display_name}</div>
                            <div className="text-xs text-muted-foreground">#{o.id}</div>
                          </td>
                          <td className="p-4 align-middle">
                            <Badge variant={o.pivot?.status === 'active' ? 'default' : 'secondary'}>
                              {o.pivot?.status || 'active'}
                            </Badge>
                          </td>
                          <td className="p-4 align-middle text-muted-foreground">
                            {o.pivot?.original_price != null ? `Rs. ${Number(o.pivot.original_price).toFixed(2)}` : '-'}
                          </td>
                          <td className="p-4 align-middle">
                            {o.pivot?.final_price != null ? `Rs. ${Number(o.pivot.final_price).toFixed(2)}` : '-'}
                          </td>
                          <td className="p-4 align-middle text-xs text-muted-foreground">
                            {o.pivot?.starts_at ? `From ${o.pivot.starts_at}` : 'From -'} ·{' '}
                            {o.pivot?.ends_at ? `To ${o.pivot.ends_at}` : 'To -'}
                          </td>
                          <td className="p-4 align-middle text-right">
                            <div className="flex justify-end gap-2">
                              <Button variant="outline" size="sm" onClick={() => startEdit(o)}>
                                Edit
                              </Button>
                              <Button variant="destructive" size="sm" onClick={() => onRemove(o.id)}>
                                Remove
                              </Button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

              {editingOfferTypeId !== null && (
                <div className="bg-muted/30 border rounded-md p-4">
                  <div className="flex items-start justify-between gap-4 mb-3">
                    <div>
                      <div className="font-medium">Edit attached offer</div>
                      <div className="text-xs text-muted-foreground">
                        Editing: {editingOfferType?.display_name || editingOfferType?.name || `Offer #${editingOfferTypeId}`}
                      </div>
                    </div>
                    <Badge variant="outline">{editingUiType}</Badge>
                  </div>

                  <form onSubmit={onUpdate} className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Offer type</Label>
                        <Select value={editData.offer_type_id} onValueChange={(v) => setEditData('offer_type_id', v)}>
                          <SelectTrigger>
                            <SelectValue placeholder="Select offer type" />
                          </SelectTrigger>
                          <SelectContent>
                            {offerTypes?.map((ot: any) => (
                              <SelectItem key={ot.id} value={ot.id.toString()}>
                                {ot.display_name}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                        {editErrors.offer_type_id && <p className="text-xs text-destructive">{editErrors.offer_type_id}</p>}
                        <p className="text-[10px] text-muted-foreground">
                          Changing this will replace the attached offer type.
                        </p>
                      </div>
                      <div />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Base price (Rs.) (from deal)</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-2.5 text-muted-foreground">Rs.</span>
                          <Input className="pl-12" value={editData.original_price} disabled />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label>Status</Label>
                        <Select value={editData.status} onValueChange={(v) => setEditData('status', v)}>
                          <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                          </SelectContent>
                        </Select>
                        {editErrors.status && <p className="text-xs text-destructive">{editErrors.status}</p>}
                      </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>Offer starts (optional)</Label>
                        <Input type="date" value={editData.starts_at} onChange={(e) => setEditData('starts_at', e.target.value)} />
                        {editErrors.starts_at && <p className="text-xs text-destructive">{editErrors.starts_at}</p>}
                      </div>
                      <div className="space-y-2">
                        <Label>Offer ends (optional)</Label>
                        <Input type="date" value={editData.ends_at} onChange={(e) => setEditData('ends_at', e.target.value)} />
                        {editErrors.ends_at && <p className="text-xs text-destructive">{editErrors.ends_at}</p>}
                      </div>
                    </div>

                    <Separator />

                    {editingUiType === 'percentage' && (
                      <div className="space-y-2">
                        <Label>Discount percent (%)</Label>
                        <Input
                          type="number"
                          value={editData.discount_percent}
                          onChange={(e) => setEditData('discount_percent', e.target.value)}
                        />
                      </div>
                    )}

                    {editingUiType === 'first_x' && (
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-2">
                          <Label>Discount percent (%)</Label>
                          <Input
                            type="number"
                            value={editData.discount_percent}
                            onChange={(e) => setEditData('discount_percent', e.target.value)}
                          />
                        </div>
                        <div className="space-y-2">
                          <Label>First X users</Label>
                          <Input
                            type="number"
                            min={1}
                            value={editData.first_x_customers}
                            onChange={(e) => setEditData('first_x_customers', e.target.value)}
                          />
                          <p className="text-xs text-muted-foreground">
                            Only the first N fulfilled customers get this discount.
                          </p>
                        </div>
                      </div>
                    )}

                    {(editingUiType === 'fixed' || editingUiType === 'flash' || editingUiType === 'bundle') && (
                      <div className="space-y-2">
                        <Label>Offer price (Rs.)</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-2.5 text-muted-foreground">Rs.</span>
                          <Input
                            type="number"
                            className="pl-12"
                            value={editData.offer_price}
                            onChange={(e) => setEditData('offer_price', e.target.value)}
                          />
                        </div>
                        <p className="text-xs text-muted-foreground">
                          Discount will be computed as (base price − offer price).
                        </p>
                      </div>
                    )}

                    {editingUiType === 'bogo' && (
                      <p className="text-sm text-muted-foreground">
                        BOGO uses fixed defaults (buy 1, get 1 free).
                      </p>
                    )}

                    <div className="flex gap-2">
                      <Button type="submit" disabled={editProcessing}>
                        {editProcessing ? 'Saving...' : 'Save'}
                      </Button>
                      <Button type="button" variant="outline" onClick={cancelEdit}>
                        Cancel
                      </Button>
                    </div>
                  </form>
                </div>
              )}
            </>
          ) : (
            <p className="text-sm text-muted-foreground">No offers attached yet. Add one below.</p>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Add an offer</CardTitle>
          <CardDescription>Add another offer type without duplicating the deal.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={onAdd} className="space-y-4 max-w-2xl">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Offer type</Label>
                <Select value={data.offer_type_id} onValueChange={(v) => setData('offer_type_id', v)}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select offer type" />
                  </SelectTrigger>
                  <SelectContent>
                    {offerTypes?.map((ot: any) => (
                      <SelectItem key={ot.id} value={ot.id.toString()}>
                        {ot.display_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.offer_type_id && <p className="text-xs text-destructive">{errors.offer_type_id}</p>}
              </div>

              <div className="space-y-2">
                <Label>Base price (Rs.) (from deal)</Label>
                <div className="relative">
                  <span className="absolute left-3 top-2.5 text-muted-foreground">Rs.</span>
                  <Input className="pl-12" value={data.original_price} disabled />
                </div>
                <p className="text-xs text-muted-foreground">
                  Set this in the deal details. Offers are calculated from it.
                </p>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Offer starts (optional)</Label>
                <Input type="date" value={data.starts_at} onChange={(e) => setData('starts_at', e.target.value)} />
                {errors.starts_at && <p className="text-xs text-destructive">{errors.starts_at}</p>}
              </div>
              <div className="space-y-2">
                <Label>Offer ends (optional)</Label>
                <Input type="date" value={data.ends_at} onChange={(e) => setData('ends_at', e.target.value)} />
                {errors.ends_at && <p className="text-xs text-destructive">{errors.ends_at}</p>}
              </div>
            </div>

            <Separator />

            {uiType === 'percentage' && (
              <div className="space-y-2">
                <Label>Discount percent (%)</Label>
                <Input
                  type="number"
                  value={data.discount_percent}
                  onChange={(e) => setData('discount_percent', e.target.value)}
                />
              </div>
            )}

            {uiType === 'first_x' && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Discount percent (%)</Label>
                  <Input
                    type="number"
                    value={data.discount_percent}
                    onChange={(e) => setData('discount_percent', e.target.value)}
                  />
                </div>
                <div className="space-y-2">
                  <Label>First X users</Label>
                  <Input
                    type="number"
                    min={1}
                    value={data.first_x_customers}
                    onChange={(e) => setData('first_x_customers', e.target.value)}
                  />
                  <p className="text-xs text-muted-foreground">
                    Offer will expire after these many fulfilled customers.
                  </p>
                </div>
              </div>
            )}

            {(uiType === 'fixed' || uiType === 'flash' || uiType === 'bundle') && (
              <div className="space-y-2">
                <Label>Offer price (Rs.)</Label>
                <div className="relative">
                  <span className="absolute left-3 top-2.5 text-muted-foreground">Rs.</span>
                  <Input
                    type="number"
                    className="pl-12"
                    value={data.offer_price}
                    onChange={(e) => setData('offer_price', e.target.value)}
                  />
                </div>
                <p className="text-xs text-muted-foreground">
                  Discount will be computed as (base price − offer price).
                </p>
              </div>
            )}

            {uiType === 'bogo' && (
              <p className="text-sm text-muted-foreground">
                BOGO uses fixed defaults (buy 1, get 1 free). Add the original price above.
              </p>
            )}

            <div className="flex gap-2">
              <Button
                type="submit"
                disabled={processing}
              >
                {processing ? 'Adding...' : 'Add Offer'}
              </Button>
              <Button type="button" variant="outline" asChild>
                <Link href={route('vendor.deals.edit', deal.id)}>Edit deal details</Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
};

DealOffers.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default DealOffers;

