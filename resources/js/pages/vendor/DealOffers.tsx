import { useMemo, useState } from 'react';
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

const toUIType = (offerTypeName: string): 'percentage' | 'fixed' | 'bogo' | 'flash' | 'bundle' => {
  if (offerTypeName === 'percentage_discount') return 'percentage';
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

const DealOffers = () => {
  const { deal, offerTypes, attachedOffers } = usePage().props as any;
  const basePrice = Number(deal?.basePrice ?? 0);

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
  });

  const editingOfferType = offerTypesById.get(Number(editData.offer_type_id));
  const editingUiType = toUIType(String(editingOfferType?.name ?? ''));

  const buildParams = () => {
    if (uiType === 'percentage') {
      const pct = Number(data.discount_percent || 0);
      return { discount_percent: pct };
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
        reset('discount_percent', 'offer_price');
      },
    } as any);
  };

  const startEdit = (o: any) => {
    const pivot = o?.pivot ?? {};
    const offerTypeId = Number(o?.id);
    const ui = toUIType(String(offerTypesById.get(offerTypeId)?.name ?? o?.name ?? ''));

    // Derive edit field values from pivot:
    const discountPercent = getPivotParamNumber(pivot, 'discount_percent');
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

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Manage Offers</h1>
          <p className="text-muted-foreground">
            Deal: <span className="font-medium text-foreground">{deal?.title}</span>
          </p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/vendor/deals">Back</Link>
        </Button>
      </div>

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
                <Link href={`/vendor/deals/${deal.id}/edit`}>Edit deal details</Link>
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

