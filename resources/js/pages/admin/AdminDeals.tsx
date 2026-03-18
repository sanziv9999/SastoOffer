
import { useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Search, CheckCircle, XCircle } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { router } from '@inertiajs/react';
import AdminPagination from '@/components/AdminPagination';

interface AdminDealsProps {
  deals: any;
  displayTypes: Array<{ id: number; name: string }>;
  filters?: { search?: string; status?: string };
}

const AdminDeals = ({ deals, displayTypes, filters }: AdminDealsProps) => {
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const status = filters?.status || 'all';
  const items = deals?.data || [];

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/deals', { search: searchTerm || undefined, status: status !== 'all' ? status : undefined }, { preserveState: true, replace: true });
  };

  const goStatus = (s: string) => {
    router.get('/admin/deals', { status: s !== 'all' ? s : undefined, search: searchTerm || undefined }, { preserveState: true, replace: true });
  };

  const saveOfferDisplayTypes = (offerPivotId: number, ids: number[]) => {
    router.patch(
      `/admin/deals/offers/${offerPivotId}/display-types`,
      { display_type_ids: ids },
      { preserveScroll: true, preserveState: true }
    );
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
              <th className="h-12 px-4 text-left align-middle font-medium">Expires</th>
              <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            {dealsList.length > 0 ? dealsList.map(deal => (
              <tr key={deal.id} className="border-b transition-colors hover:bg-muted/50">
                <td className="p-4 align-middle">
                  <div className="flex items-center gap-3">
                    {deal.image && <img src={deal.image} alt={deal.title} className="h-10 w-10 rounded object-cover" />}
                    <div>
                      <div className="font-medium">{deal.title?.length > 25 ? `${deal.title.substring(0, 25)}...` : deal.title}</div>
                      <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                      {Array.isArray(deal.offers) && deal.offers.length > 0 ? (
                        <div className="mt-2 space-y-2">
                          {deal.offers.map((offer: any) => (
                            <div key={offer.id} className="rounded border p-2">
                              <div className="flex items-center justify-between gap-2">
                                <div className="text-xs font-medium">
                                  {offer.offerTypeTitle}
                                  <span className="ml-2 text-muted-foreground">#{offer.id}</span>
                                </div>
                                <Badge variant={offer.status === 'active' ? 'default' : 'outline'} className="text-[10px]">
                                  {offer.status}
                                </Badge>
                              </div>
                              <div className="mt-1 text-xs text-muted-foreground">
                                {offer.discountedPrice !== null ? `Rs. ${offer.discountedPrice}` : 'N/A'}
                                {offer.originalPrice !== null ? ` / Rs. ${offer.originalPrice}` : ''}
                              </div>
                              <div className="mt-2 flex flex-wrap gap-1">
                                {displayTypes.map((dt) => {
                                  const selected = (offer.displayTypeIds || []).includes(dt.id);
                                  const nextIds = selected
                                    ? (offer.displayTypeIds || []).filter((id: number) => id !== dt.id)
                                    : [...(offer.displayTypeIds || []), dt.id];
                                  return (
                                    <Button
                                      key={dt.id}
                                      variant={selected ? 'default' : 'outline'}
                                      size="sm"
                                      className="h-6 px-2 text-[10px]"
                                      onClick={() => saveOfferDisplayTypes(offer.id, nextIds)}
                                    >
                                      {dt.name}
                                    </Button>
                                  );
                                })}
                              </div>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <div className="text-xs text-muted-foreground mt-1">No child offers attached</div>
                      )}
                    </div>
                  </div>
                </td>
                <td className="p-4 align-middle">{deal.vendorName || 'Unknown'}</td>
                <td className="p-4 align-middle">
                  <div className="font-medium">${deal.discountedPrice?.toFixed(2)}</div>
                  <div className="text-xs text-muted-foreground line-through">${deal.originalPrice?.toFixed(2)}</div>
                </td>
                <td className="p-4 align-middle">
                  <Badge variant={deal.status === 'active' ? 'default' : deal.status === 'pending' ? 'destructive' : 'outline'}
                    className={deal.status === 'active' ? 'bg-green-500' : undefined}>
                    {deal.status ? deal.status.charAt(0).toUpperCase() + deal.status.slice(1) : 'Unknown'}
                  </Badge>
                </td>
                <td className="p-4 align-middle">{deal.endDate ? formatDistanceToNow(new Date(deal.endDate), { addSuffix: true }) : 'N/A'}</td>
                <td className="p-4 align-middle text-right">
                  <div className="flex justify-end gap-2">
                    {deal.status === 'pending' && (
                      <>
                        <Button size="sm" className="bg-green-500 hover:bg-green-600"><CheckCircle className="h-4 w-4 mr-1" />Approve</Button>
                        <Button variant="destructive" size="sm"><XCircle className="h-4 w-4 mr-1" />Reject</Button>
                      </>
                    )}
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={deal.offerPivotId ? `/deals/${deal.offerPivotId}` : `/deals/deal/${deal.id}`}>View</Link>
                    </Button>
                  </div>
                </td>
              </tr>
            )) : (
              <tr>
                <td colSpan={6} className="p-8 text-center text-muted-foreground">No deals found.</td>
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
