import { useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Search, CheckCircle, XCircle } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';
import DashboardLayout from '@/layouts/DashboardLayout';
import { router } from '@inertiajs/react';
import AdminPagination from '@/components/AdminPagination';

interface AdminPendingDealsProps {
    pendingDeals: any;
    filters?: { search?: string };
}

const AdminPendingDeals = ({ pendingDeals, filters }: AdminPendingDealsProps) => {
    const [searchTerm, setSearchTerm] = useState(filters?.search || '');
    const items = pendingDeals?.data || [];

    const onSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/deals/pending', { search: searchTerm || undefined }, { preserveState: true, replace: true });
    };

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold tracking-tight">Pending Deals Review</h1>
                <p className="text-muted-foreground">Review and approve or reject newly submitted deals.</p>
            </div>

            <Card>
                <CardHeader>
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <CardTitle>Deals Awaiting Approval</CardTitle>
                            <CardDescription>{pendingDeals?.total || items.length || 0} deals require your attention</CardDescription>
                        </div>
                        <form onSubmit={onSearch} className="flex w-full md:w-auto">
                            <Input placeholder="Search pending deals..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
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
                                        <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
                                        <th className="h-12 px-4 text-left align-middle font-medium">Vendor</th>
                                        <th className="h-12 px-4 text-left align-middle font-medium">Price</th>
                                        <th className="h-12 px-4 text-left align-middle font-medium">Type</th>
                                        <th className="h-12 px-4 text-left align-middle font-medium">Submitted</th>
                                        <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {items.length > 0 ? items.map((deal: any) => (
                                        <tr key={deal.id} className="border-b transition-colors hover:bg-muted/50">
                                            <td className="p-4 align-middle">
                                                <div className="flex items-center gap-3">
                                                    {deal.image && <img src={deal.image} alt={deal.title} className="h-10 w-10 rounded object-cover" />}
                                                    <div>
                                                        <div className="font-medium">{deal.title?.length > 25 ? `${deal.title.substring(0, 25)}...` : deal.title}</div>
                                                        <div className="text-xs text-muted-foreground">ID: {deal.id}</div>
                                                        {deal.offerTypeTitle && (
                                                            <div className="text-xs text-muted-foreground">Offer: {deal.offerTypeTitle}</div>
                                                        )}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="p-4 align-middle">{deal.vendorName || 'Unknown Vendor'}</td>
                                            <td className="p-4 align-middle">
                                                <div className="font-medium">${deal.discountedPrice?.toFixed(2)}</div>
                                                <div className="text-xs text-muted-foreground line-through">${deal.originalPrice?.toFixed(2)}</div>
                                            </td>
                                            <td className="p-4 align-middle">
                                                <Badge variant="outline">{deal.offerTypeTitle || deal.type || '-'}</Badge>
                                            </td>
                                            <td className="p-4 align-middle">
                                                {deal.createdAt ? formatDistanceToNow(new Date(deal.createdAt), { addSuffix: true }) : 'N/A'}
                                            </td>
                                            <td className="p-4 align-middle text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button size="sm" className="bg-green-500 hover:bg-green-600"><CheckCircle className="h-4 w-4 mr-1" />Approve</Button>
                                                    <Button variant="destructive" size="sm"><XCircle className="h-4 w-4 mr-1" />Reject</Button>
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href={deal.offerPivotId ? `/deals/${deal.offerPivotId}` : `/deals/deal/${deal.id}`}>View</Link>
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    )) : (
                                        <tr>
                                            <td colSpan={6} className="p-8 text-center text-muted-foreground">
                                                No pending deals found. All caught up!
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="pt-4">
                        <AdminPagination links={pendingDeals?.links} />
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

AdminPendingDeals.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminPendingDeals;
