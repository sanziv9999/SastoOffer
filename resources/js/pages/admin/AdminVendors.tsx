
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Search, Star } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { router } from '@inertiajs/react';
import AdminPagination from '@/components/AdminPagination';

interface AdminVendorsProps {
  vendors: any;
  filters?: { search?: string };
}

const AdminVendors = ({ vendors, filters }: AdminVendorsProps) => {
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const items = vendors?.data || [];

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/vendors', { search: searchTerm || undefined }, { preserveState: true, replace: true });
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Vendor Management</h1>
        <p className="text-muted-foreground">View and manage all platform vendors</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>All Vendors</CardTitle>
              <CardDescription>{vendors?.total || items.length || 0} registered vendors</CardDescription>
            </div>
            <form onSubmit={onSearch} className="flex w-full md:w-auto">
              <Input placeholder="Search vendors..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button type="submit" size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </form>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border">
            <div className="relative w-full overflow-auto">
              <table className="w-full caption-bottom text-sm">
                <thead className="border-b">
                  <tr>
                    <th className="h-12 px-4 text-left align-middle font-medium">Business</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Contact</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Category</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Rating</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Joined</th>
                    <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {items.length > 0 ? items.map((v: any) => (
                    <tr key={v.id} className="border-b transition-colors hover:bg-muted/50">
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-3">
                          {v.logo ? (
                            <img src={v.logo} alt={v.business_name} className="h-8 w-8 rounded object-cover" />
                          ) : (
                            <div className="h-8 w-8 rounded bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">{v.business_name?.charAt(0)}</div>
                          )}
                          <div>
                            <span className="font-medium">{v.business_name}</span>
                            <p className="text-xs text-muted-foreground">{v.description?.substring(0, 50)}...</p>
                          </div>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{v.public_email}</td>
                      <td className="p-4 align-middle">
                        <Badge variant="outline" className="capitalize">
                          {v.primary_category?.name || 'Uncategorized'}
                        </Badge>
                      </td>
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-1">
                          <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                          <span>{v.averageRating?.toFixed(1) || '0.0'}</span>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{v.created_at ? new Date(v.created_at).toLocaleDateString() : 'N/A'}</td>
                      <td className="p-4 align-middle text-right">
                        <Button variant="ghost" size="sm">View</Button>
                        <Button variant="ghost" size="sm" className="text-destructive">Suspend</Button>
                      </td>
                    </tr>
                  )) : (
                    <tr>
                      <td colSpan={5} className="p-8 text-center text-muted-foreground">
                        No vendors found matching your search.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
          <div className="pt-4">
            <AdminPagination links={vendors?.links} />
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

AdminVendors.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminVendors;
