
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Search, Star } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface AdminVendorsProps {
  vendors: any[];
}

const AdminVendors = ({ vendors }: AdminVendorsProps) => {
  const [searchTerm, setSearchTerm] = useState('');

  const filteredVendors = vendors?.filter(v =>
    v.businessName?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    v.contactEmail?.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];

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
              <CardDescription>{vendors?.length || 0} registered vendors</CardDescription>
            </div>
            <div className="flex w-full md:w-auto">
              <Input placeholder="Search vendors..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </div>
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
                    <th className="h-12 px-4 text-left align-middle font-medium">Rating</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Joined</th>
                    <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredVendors.length > 0 ? filteredVendors.map(v => (
                    <tr key={v.id} className="border-b transition-colors hover:bg-muted/50">
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-3">
                          {v.logo ? (
                            <img src={v.logo} alt={v.businessName} className="h-8 w-8 rounded object-cover" />
                          ) : (
                            <div className="h-8 w-8 rounded bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">{v.businessName?.charAt(0)}</div>
                          )}
                          <div>
                            <span className="font-medium">{v.businessName}</span>
                            <p className="text-xs text-muted-foreground">{v.description?.substring(0, 50)}...</p>
                          </div>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{v.contactEmail}</td>
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-1">
                          <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                          <span>{v.averageRating?.toFixed(1) || '0.0'}</span>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{v.createdAt ? new Date(v.createdAt).toLocaleDateString() : 'N/A'}</td>
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
        </CardContent>
      </Card>
    </div>
  );
};

AdminVendors.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminVendors;
