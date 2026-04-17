
import { Fragment, useState } from 'react';
import { useRemember } from '@inertiajs/react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, CheckCircle, XCircle, ChevronDown, ChevronUp, Star } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { router } from '@inertiajs/react';
import AdminPagination from '@/components/AdminPagination';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';

interface AdminDealsProps {
  deals: any;
  featuredDisplayType?: { id: number; name: string } | null;
  filters?: { search?: string; status?: string };
}

const AdminDeals = ({ deals, featuredDisplayType = null, filters }: AdminDealsProps) => {
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  /** Persist across Inertia visits so rows stay open after PATCH (e.g. toggling featured). */
  const [expandedDealIds, setExpandedDealIds] = useRemember<number[]>([], 'admin.deals.expandedRows');
  const status = filters?.status || 'all';
  const items = deals?.data || [];

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/deals', { search: searchTerm || undefined, status: status !== 'all' ? status : undefined }, { preserveState: true, replace: true });
  };

  const goStatus = (s: string) => {
    router.get('/admin/deals', { status: s !== 'all' ? s : undefined, search: searchTerm || undefined }, { preserveState: true, replace: true });
  };

  const saveOfferDisplayTypes = (dealId: number, offerPivotId: number, ids: number[]) => {
    router.patch(
      `/admin/deals/offers/${offerPivotId}/display-types`,
      { display_type_ids: ids },
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          setExpandedDealIds((prev) => (prev.includes(dealId) ? prev : [...prev, dealId]));
        },
      }
    );
  };

  const updateDealStatus = (dealId: number, nextStatus: string) => {
    router.patch(
      `/admin/deals/${dealId}/status`,
      { status: nextStatus },
      { preserveScroll: true, preserveState: true }
    );
  };

  const updateOfferStatus = (offerPivotId: number, nextStatus: string) => {
    router.patch(
      `/admin/deals/offers/${offerPivotId}/status`,
      { status: nextStatus },
      { preserveScroll: true, preserveState: true }
    );
  };

  const toggleDealExpand = (dealId: number) => {
    setExpandedDealIds((prev) => {
      const list = Array.isArray(prev) ? prev : [];
      return list.includes(dealId) ? list.filter((id) => id !== dealId) : [...list, dealId];
    });
  };

  const renderTable = (dealsList: any[]) => (
    <div className="rounded-md border">
      <div className="relative w-full overflow-auto">
        <table className="w-full caption-bottom text-sm">
          <thead className="border-b">
            <tr>
              <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
              <th className="h-12 px-4 text-left align-middle font-medium">Vendor</th>
              <th className="h-12 px-4 text-left align-middle font-medium">Price</th>
              <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
              <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            {dealsList.length > 0 ? dealsList.map((deal) => {
              const isExpanded = expandedDealIds.includes(deal.id);
              const offers = Array.isArray(deal.offers) ? deal.offers : [];

              return (
                <Fragment key={deal.id}>
                  <tr
                    className="border-b transition-colors hover:bg-muted/50 cursor-pointer"
                    onClick={() => toggleDealExpand(deal.id)}
                  >
                    <td className="p-4 align-middle">
                      <div className="flex items-center gap-3">
                        <div className="text-muted-foreground">
                          {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                        </div>
                        {deal.image && <img src={deal.image} alt={deal.title} className="h-10 w-10 rounded object-cover" />}
                        <div>
                          <div className="font-medium">{deal.title?.length > 25 ? `${deal.title.substring(0, 25)}...` : deal.title}</div>
                          <div className="text-xs text-muted-foreground">
                            ID: {deal.id} · {offers.length} offer{offers.length !== 1 ? 's' : ''}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="p-4 align-middle">{deal.vendorName || 'Unknown'}</td>
                    <td className="p-4 align-middle">
                      <div className="font-medium">
                        {deal.basePrice !== null && deal.basePrice !== undefined
                          ? `Rs. ${Number(deal.basePrice).toFixed(2)}`
                          : 'N/A'}
                      </div>
                      <div className="text-xs text-muted-foreground">From deals.base_price</div>
                    </td>
                    <td className="p-4 align-middle" onClick={(e) => e.stopPropagation()}>
                      <select
                        value={deal.status || 'draft'}
                        onChange={(e) => updateDealStatus(deal.id, e.target.value)}
                        className="h-8 rounded border px-2 text-xs bg-background"
                      >
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                      </select>
                    </td>
                    <td className="p-4 align-middle text-right" onClick={(e) => e.stopPropagation()}>
                      <div className="flex justify-end gap-2">
                        {deal.status === 'pending' && (
                          <>
                            <Button
                              size="sm"
                              className="bg-green-500 hover:bg-green-600"
                              onClick={() => updateDealStatus(deal.id, 'active')}
                            >
                              <CheckCircle className="h-4 w-4 mr-1" />
                              Approve
                            </Button>
                            <Button
                              variant="destructive"
                              size="sm"
                              onClick={() => updateDealStatus(deal.id, 'inactive')}
                            >
                              <XCircle className="h-4 w-4 mr-1" />
                              Reject
                            </Button>
                          </>
                        )}
                        <Button variant="ghost" size="sm" asChild>
                          <Link href={`/admin/deals/${deal.id}/view`}>View</Link>
                        </Button>
                      </div>
                    </td>
                  </tr>
                  {isExpanded && (
                    <tr key={`child-${deal.id}`}>
                      <td colSpan={5} className="p-0" onClick={(e) => e.stopPropagation()}>
                        <div className="border-b bg-gradient-to-b from-muted/40 to-muted/15 px-4 py-5 sm:px-6">
                          <div className="mb-4">
                            <h4 className="text-sm font-semibold tracking-tight">Offers for this deal</h4>
                            <p className="text-xs text-muted-foreground mt-0.5">
                              Manage pricing, status, and whether an offer is featured in curated placements.
                            </p>
                          </div>
                          {offers.length > 0 ? (
                            <div className="space-y-3">
                              {offers.map((offer: any) => {
                                const isFeatured =
                                  !!featuredDisplayType &&
                                  (offer.displayTypeIds || []).includes(featuredDisplayType.id);
                                return (
                                  <div
                                    key={offer.id}
                                    className="overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm ring-1 ring-border/60"
                                  >
                                    <div className="flex flex-col gap-4 p-4 sm:flex-row sm:items-start sm:justify-between">
                                      <div className="min-w-0 flex-1 space-y-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                          <span className="font-semibold leading-tight">{offer.offerTypeTitle}</span>
                                          <Badge variant="outline" className="font-mono text-[10px]">
                                            #{offer.id}
                                          </Badge>
                                        </div>
                                        {offer.endDate && (
                                          <p className="text-xs text-muted-foreground">
                                            Ends {new Date(offer.endDate).toLocaleDateString(undefined, { dateStyle: 'medium' })}
                                          </p>
                                        )}
                                      </div>
                                      <div className="flex shrink-0 flex-col items-stretch gap-2 sm:items-end">
                                        <div className="text-right">
                                          <div className="text-lg font-semibold tabular-nums">
                                            {offer.discountedPrice !== null
                                              ? `Rs. ${Number(offer.discountedPrice).toFixed(2)}`
                                              : '—'}
                                          </div>
                                          <div className="text-[11px] text-muted-foreground">Final price</div>
                                        </div>
                                        <select
                                          value={offer.status || 'active'}
                                          onChange={(e) => updateOfferStatus(offer.id, e.target.value)}
                                          className="h-9 w-full min-w-[140px] rounded-md border bg-background px-2 text-xs sm:w-auto"
                                        >
                                          <option value="draft">Draft</option>
                                          <option value="pending">Pending</option>
                                          <option value="active">Active</option>
                                          <option value="inactive">Inactive</option>
                                          <option value="expired">Expired</option>
                                        </select>
                                      </div>
                                    </div>
                                    <Separator />
                                    <div className="flex flex-col gap-4 bg-muted/25 p-4 sm:flex-row sm:items-center sm:justify-between">
                                      <div className="text-sm">
                                        <span className="text-xs text-muted-foreground">Original price</span>
                                        <div className="font-medium tabular-nums">
                                          {offer.originalPrice !== null
                                            ? `Rs. ${Number(offer.originalPrice).toFixed(2)}`
                                            : '—'}
                                        </div>
                                      </div>
                                      {featuredDisplayType && (
                                        <div className="flex w-full flex-col gap-3 rounded-lg border bg-background p-3 sm:max-w-md sm:flex-row sm:items-center sm:justify-between">
                                          <div className="flex gap-3">
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/15">
                                              <Star className="h-5 w-5 text-amber-600" fill="currentColor" />
                                            </div>
                                            <div className="space-y-0.5">
                                              <Label htmlFor={`featured-${offer.id}`} className="text-sm font-medium leading-none">
                                                Featured offer
                                              </Label>
                                              <p className="text-xs text-muted-foreground">
                                                When enabled, this offer can appear in featured placements (for example Discover).
                                              </p>
                                            </div>
                                          </div>
                                          <Switch
                                            id={`featured-${offer.id}`}
                                            checked={isFeatured}
                                            onCheckedChange={(checked) =>
                                              saveOfferDisplayTypes(
                                                deal.id,
                                                offer.id,
                                                checked ? [featuredDisplayType.id] : []
                                              )
                                            }
                                            className="shrink-0 data-[state=checked]:bg-amber-600"
                                          />
                                        </div>
                                      )}
                                    </div>
                                  </div>
                                );
                              })}
                            </div>
                          ) : (
                            <p className="text-sm text-muted-foreground">No offers attached to this deal yet.</p>
                          )}
                        </div>
                      </td>
                    </tr>
                  )}
                </Fragment>
              );
            }) : (
              <tr>
                <td colSpan={5} className="p-8 text-center text-muted-foreground">No deals found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Deal Management</h1>
        <p className="text-muted-foreground">Review, approve, and manage all platform deals</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>All Deals</CardTitle>
              <CardDescription>{deals?.total || items.length || 0} total deals</CardDescription>
            </div>
            <form onSubmit={onSearch} className="flex w-full md:w-auto">
              <Input placeholder="Search deals..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button type="submit" size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </form>
          </div>
        </CardHeader>
        <CardContent>
          <Tabs value={status} className="space-y-4" onValueChange={goStatus}>
            <TabsList>
              <TabsTrigger value="all">All</TabsTrigger>
              <TabsTrigger value="pending">Pending</TabsTrigger>
              <TabsTrigger value="active">Active</TabsTrigger>
              <TabsTrigger value="expired">Expired</TabsTrigger>
            </TabsList>
            <TabsContent value={status}>{renderTable(items)}</TabsContent>
          </Tabs>
          <div className="pt-4">
            <AdminPagination links={deals?.links} />
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

AdminDeals.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminDeals;
