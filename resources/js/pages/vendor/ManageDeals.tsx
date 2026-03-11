
import { useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Plus, Search, Package } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
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

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success);
    }
    if (flash?.error) {
      toast.error(flash.error);
    }
  }, [flash]);

  const filterDeals = (status?: string) => {
    let filtered = deals || [];
    if (status && status !== 'all') filtered = filtered.filter((d: any) => d.status === status);
    if (searchTerm) filtered = filtered.filter((d: any) => d.title.toLowerCase().includes(searchTerm.toLowerCase()));
    return filtered;
  };

  const renderTable = (dealsList: any[]) => (
    dealsList.length > 0 ? (
      <div className="rounded-md border">
        <div className="relative w-full overflow-auto">
          <table className="w-full caption-bottom text-sm">
            <thead className="border-b">
              <tr>
                <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
                <th className="h-12 px-4 text-left align-middle font-medium">Price</th>
                <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                <th className="h-12 px-4 text-left align-middle font-medium">Sales</th>
                <th className="h-12 px-4 text-left align-middle font-medium">Expires</th>
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
                    <div className="font-medium">${deal.discountedPrice?.toFixed(2)}</div>
                    <div className="text-xs text-muted-foreground line-through">${deal.originalPrice?.toFixed(2)}</div>
                  </td>
                  <td className="p-4 align-middle">
                    <Badge variant={deal.status === 'active' ? 'default' : deal.status === 'expired' ? 'secondary' : 'outline'}
                      className={deal.status === 'active' ? 'bg-green-500' : undefined}>
                      {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
                    </Badge>
                  </td>
                  <td className="p-4 align-middle">{deal.quantitySold || 0} sold</td>
                  <td className="p-4 align-middle">
                    {deal.endDate ? formatDistanceToNow(new Date(deal.endDate), { addSuffix: true }) : 'N/A'}
                  </td>
                  <td className="p-4 align-middle text-right">
                    <div className="flex justify-end gap-2">
                      <Button variant="outline" size="sm" asChild>
                        <a href={`/vendor/deals/${deal.id}/edit`}>Edit</a>
                      </Button>
                      <Button variant="ghost" size="sm" asChild>
                        <a href={`/deals/${deal.id}`}>View</a>
                      </Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
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
          <p className="text-muted-foreground">View and manage all your deals</p>
        </div>
        <Button asChild><Link href="/vendor/deals/create"><Plus className="mr-2 h-4 w-4" />Create Deal</Link></Button>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>All Deals</CardTitle>
              <CardDescription>{deals?.length || 0} total deals</CardDescription>
            </div>
            <div className="flex w-full md:w-auto">
              <Input placeholder="Search deals..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="all" className="space-y-4">
            <TabsList>
              <TabsTrigger value="all">All</TabsTrigger>
              <TabsTrigger value="active">Active</TabsTrigger>
              <TabsTrigger value="draft">Draft</TabsTrigger>
              <TabsTrigger value="pending">Pending</TabsTrigger>
              <TabsTrigger value="expired">Expired</TabsTrigger>
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
