
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
import { Star } from 'lucide-react';
import { Flame, Trophy, Sparkles } from 'lucide-react';

interface AdminDealsProps {
  deals: any;
  filters?: { search?: string; status?: string };
}

const AdminDeals = ({ deals, filters }: AdminDealsProps) => {
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

  const patchFlags = (dealId: number, payload: Record<string, any>) => {
    router.patch(`/admin/deals/${dealId}/flags`, payload, { preserveScroll: true });
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
                    <Button
                      variant={deal.is_featured ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => router.post(`/admin/deals/${deal.id}/toggle-featured`, {}, { preserveScroll: true })}
                      title="Toggle featured"
                    >
                      <Star className="h-4 w-4 mr-1" />
                      {deal.is_featured ? 'Featured' : 'Feature'}
                    </Button>

                    <Button
                      variant={deal.is_deal_of_day ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => patchFlags(deal.id, { is_deal_of_day: !deal.is_deal_of_day })}
                      title="Deal of the day"
                    >
                      <Flame className="h-4 w-4 mr-1" />
                      {deal.is_deal_of_day ? 'Day' : 'Day'}
                    </Button>

                    <Button
                      variant={deal.is_best_seller ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => patchFlags(deal.id, { is_best_seller: !deal.is_best_seller })}
                      title="Best seller"
                    >
                      <Trophy className="h-4 w-4 mr-1" />
                      {deal.is_best_seller ? 'Best' : 'Best'}
                    </Button>

                    <Button
                      variant={deal.is_new_arrival ? 'default' : 'outline'}
                      size="sm"
                      onClick={() => patchFlags(deal.id, { is_new_arrival: !deal.is_new_arrival })}
                      title="New arrival"
                    >
                      <Sparkles className="h-4 w-4 mr-1" />
                      {deal.is_new_arrival ? 'New' : 'New'}
                    </Button>

                    {deal.status === 'pending' && (
                      <>
                        <Button size="sm" className="bg-green-500 hover:bg-green-600"><CheckCircle className="h-4 w-4 mr-1" />Approve</Button>
                        <Button variant="destructive" size="sm"><XCircle className="h-4 w-4 mr-1" />Reject</Button>
                      </>
                    )}
                    <Button variant="ghost" size="sm" asChild>
                      <Link href={`/deal/${deal.id}`}>View</Link>
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
