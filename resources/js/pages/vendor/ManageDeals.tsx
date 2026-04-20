
import { useMemo, useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Plus, Search, Package, Layers3, CircleDollarSign, TrendingUp, Clock3 } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'sonner';

interface ManageDealsProps {
  deals: any[];
}

const ManageDeals = ({ deals }: ManageDealsProps) => {
  const [searchTerm, setSearchTerm] = useState('');
  const { flash } = usePage().props as any;
  const allDeals = deals || [];

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success);
    }
    if (flash?.error) {
      toast.error(flash.error);
    }
  }, [flash]);

  const filterDeals = (status?: string) => {
    let filtered = allDeals;
    if (status && status !== 'all') filtered = filtered.filter((d: any) => d.status === status);
    if (searchTerm) filtered = filtered.filter((d: any) => d.title.toLowerCase().includes(searchTerm.toLowerCase()));
    return filtered;
  };

  const stats = useMemo(() => {
    const totalDeals = allDeals.length;
    const activeDeals = allDeals.filter((d: any) => d.status === 'active').length;
    const pendingDeals = allDeals.filter((d: any) => d.status === 'pending').length;
    const totalSold = allDeals.reduce((sum: number, d: any) => sum + Number(d.quantitySold || 0), 0);
    const estRevenue = allDeals.reduce(
      (sum: number, d: any) => sum + (Number(d.quantitySold || 0) * Number(d.price || 0)),
      0,
    );

    return { totalDeals, activeDeals, pendingDeals, totalSold, estRevenue };
  }, [allDeals]);

  const getStatusBadge = (status: string) => {
    const normalized = String(status || '').toLowerCase();
    if (normalized === 'active') return 'bg-green-100 text-green-700 border-green-200';
    if (normalized === 'pending') return 'bg-amber-100 text-amber-700 border-amber-200';
    if (normalized === 'expired') return 'bg-slate-100 text-slate-700 border-slate-200';
    if (normalized === 'draft') return 'bg-zinc-100 text-zinc-700 border-zinc-200';
    return 'bg-muted text-muted-foreground border-border';
  };

  const renderTable = (dealsList: any[]) => (
    dealsList.length > 0 ? (
      <div className="space-y-3">
        {/* Mobile: stacked cards */}
        <div className="md:hidden space-y-3">
          {dealsList.map((deal) => {
            const sold = Number(deal.quantitySold || 0);
            const maxQty = deal.maxQuantity !== null && deal.maxQuantity !== undefined
              ? Number(deal.maxQuantity)
              : null;
            const totalQty = maxQty !== null ? sold + maxQty : null;

            return (
              <Card key={deal.id} className="rounded-lg">
                <CardContent className="p-3 space-y-3">
                  <div className="flex items-start gap-3">
                    {deal.image && (
                      <img src={deal.image} alt={deal.title} className="h-12 w-12 rounded object-cover shrink-0" />
                    )}
                    <div className="min-w-0 flex-1">
                      <div className="font-medium text-sm line-clamp-2">{deal.title}</div>
                      <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                    </div>
                    <Badge variant="outline" className={getStatusBadge(deal.status)}>
                      {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
                    </Badge>
                  </div>

                  <div className="grid grid-cols-2 gap-2 text-xs">
                    <div className="rounded-md border p-2">
                      <p className="text-muted-foreground">Price</p>
                      <p className="font-semibold text-sm">Rs. {Number(deal.price ?? 0).toFixed(2)}</p>
                    </div>
                    <div className="rounded-md border p-2">
                      <p className="text-muted-foreground">Sales</p>
                      <p className="font-semibold text-sm">{sold} sold</p>
                    </div>
                    <div className="rounded-md border p-2">
                      <p className="text-muted-foreground">Est. Revenue</p>
                      <p className="font-semibold text-sm">Rs. {(sold * Number(deal.price || 0)).toFixed(2)}</p>
                    </div>
                    <div className="rounded-md border p-2">
                      <p className="text-muted-foreground">Inventory</p>
                      <p className="font-semibold text-sm">
                        {totalQty !== null ? `${sold}/${totalQty}` : 'Unlimited'}
                      </p>
                    </div>
                  </div>

                  <div className="grid grid-cols-3 gap-2">
                    <Button variant="outline" size="sm" asChild className="h-8 text-xs">
                      <Link href={`/vendor/deals/${deal.id}/edit`}>Edit</Link>
                    </Button>
                    <Button variant="outline" size="sm" asChild className="h-8 text-xs">
                      <Link href={`/vendor/deals/${deal.id}/offers`}>Offers</Link>
                    </Button>
                    <Button variant="ghost" size="sm" asChild className="h-8 text-xs">
                      <Link href={`/vendor/deals/${deal.id}`}>View</Link>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Desktop/tablet: table */}
        <div className="hidden md:block rounded-md border">
          <div className="relative w-full overflow-auto">
            <table className="w-full caption-bottom text-sm">
              <thead className="border-b bg-muted/20">
                <tr>
                  <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
                  <th className="h-12 px-4 text-left align-middle font-medium">Price</th>
                  <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                  <th className="h-12 px-4 text-left align-middle font-medium">Sales</th>
                  <th className="h-12 px-4 text-left align-middle font-medium">Inventory</th>
                  <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                {dealsList.map(deal => (
                  <tr key={deal.id} className="border-b transition-colors hover:bg-muted/50">
                    <td className="p-4 align-middle">
                      <div className="flex items-center gap-3">
                        {deal.image && <img src={deal.image} alt={deal.title} className="h-10 w-10 rounded object-cover" />}
                        <div>
                          <div className="font-medium">{deal.title.length > 30 ? `${deal.title.substring(0, 30)}...` : deal.title}</div>
                          <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                        </div>
                      </div>
                    </td>
                    <td className="p-4 align-middle">
                      <div className="font-medium">Rs. {Number(deal.price ?? 0).toFixed(2)}</div>
                    </td>
                    <td className="p-4 align-middle">
                      <Badge variant="outline" className={getStatusBadge(deal.status)}>
                        {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
                      </Badge>
                    </td>
                    <td className="p-4 align-middle">
                      <div className="font-medium">{deal.quantitySold || 0} sold</div>
                      <div className="text-xs text-muted-foreground">
                        Est. Rs. {(Number(deal.quantitySold || 0) * Number(deal.price || 0)).toFixed(2)}
                      </div>
                    </td>
                    <td className="p-4 align-middle">
                      <div className="text-sm text-muted-foreground">
                        {deal.maxQuantity !== null && deal.maxQuantity !== undefined ? (
                          <span className="text-foreground">
                            {Number(deal.quantitySold || 0)}/{Number(deal.quantitySold || 0) + Number(deal.maxQuantity)}
                          </span>
                        ) : (
                          <span className="text-foreground">Unlimited</span>
                        )}
                      </div>
                    </td>
                    <td className="p-4 align-middle text-right">
                      <div className="flex justify-end items-center gap-2">
                        <Button variant="outline" size="sm" asChild>
                          <Link href={`/vendor/deals/${deal.id}/edit`}>Edit</Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                          <Link href={`/vendor/deals/${deal.id}/offers`}>Offers</Link>
                        </Button>
                        <Button variant="ghost" size="sm" asChild>
                          <Link href={`/vendor/deals/${deal.id}`}>View</Link>
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    ) : (
      <div className="border rounded-md p-8 text-center">
        <Package className="h-10 w-10 mx-auto mb-4 text-muted-foreground" />
        <h3 className="text-lg font-semibold mb-2">No deals found</h3>
        <p className="text-muted-foreground mb-4">No deals match your criteria.</p>
        <Button asChild><Link href="/vendor/deals/create"><Plus className="mr-2 h-4 w-4" />Create Deal</Link></Button>
      </div>
    )
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Manage Deals</h1>
          <p className="text-muted-foreground">Manager view for deal performance, status, and actions</p>
        </div>
        <Button asChild><Link href="/vendor/deals/create"><Plus className="mr-2 h-4 w-4" />Create Deal</Link></Button>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-5">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <p className="text-xs uppercase tracking-wide text-muted-foreground">Total Deals</p>
              <Layers3 className="h-4 w-4 text-muted-foreground" />
            </div>
            <p className="text-2xl font-bold mt-1">{stats.totalDeals}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <p className="text-xs uppercase tracking-wide text-muted-foreground">Active</p>
              <TrendingUp className="h-4 w-4 text-green-600" />
            </div>
            <p className="text-2xl font-bold mt-1 text-green-700">{stats.activeDeals}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <p className="text-xs uppercase tracking-wide text-muted-foreground">Pending</p>
              <Clock3 className="h-4 w-4 text-amber-600" />
            </div>
            <p className="text-2xl font-bold mt-1 text-amber-700">{stats.pendingDeals}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <p className="text-xs uppercase tracking-wide text-muted-foreground">Units Sold</p>
              <Package className="h-4 w-4 text-muted-foreground" />
            </div>
            <p className="text-2xl font-bold mt-1">{stats.totalSold}</p>
          </CardContent>
        </Card>
        <Card className="col-span-2 md:col-span-1">
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <p className="text-xs uppercase tracking-wide text-muted-foreground">Est. Revenue</p>
              <CircleDollarSign className="h-4 w-4 text-muted-foreground" />
            </div>
            <p className="text-xl font-bold mt-1">Rs. {stats.estRevenue.toFixed(2)}</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>Deal Portfolio</CardTitle>
              <CardDescription>{allDeals.length || 0} total deals across all statuses</CardDescription>
            </div>
            <div className="flex w-full md:w-auto">
              <Input placeholder="Search deals..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="all" className="space-y-4">
            <TabsList className="w-full max-w-full overflow-x-auto flex-nowrap whitespace-nowrap justify-start">
              <TabsTrigger value="all">All ({allDeals.length})</TabsTrigger>
              <TabsTrigger value="active">Active ({allDeals.filter((d: any) => d.status === 'active').length})</TabsTrigger>
              <TabsTrigger value="draft">Draft ({allDeals.filter((d: any) => d.status === 'draft').length})</TabsTrigger>
              <TabsTrigger value="pending">Pending ({allDeals.filter((d: any) => d.status === 'pending').length})</TabsTrigger>
              <TabsTrigger value="expired">Expired ({allDeals.filter((d: any) => d.status === 'expired').length})</TabsTrigger>
            </TabsList>
            <TabsContent value="all">{renderTable(filterDeals())}</TabsContent>
            <TabsContent value="active">{renderTable(filterDeals('active'))}</TabsContent>
            <TabsContent value="draft">{renderTable(filterDeals('draft'))}</TabsContent>
            <TabsContent value="pending">{renderTable(filterDeals('pending'))}</TabsContent>
            <TabsContent value="expired">{renderTable(filterDeals('expired'))}</TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

ManageDeals.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default ManageDeals;
