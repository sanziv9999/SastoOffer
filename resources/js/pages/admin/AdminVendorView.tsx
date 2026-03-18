import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link, router } from '@inertiajs/react';

const AdminVendorView = ({ vendor }: { vendor: any }) => {
  const getCategoryLabel = () => {
    const parent = vendor?.category?.parent?.name;
    const name = vendor?.category?.name;
    if (parent && name) return `${parent} / ${name}`;
    return name || parent || 'Uncategorized';
  };

  const updateVerifiedStatus = (verified_status: string) => {
    router.patch(
      `/admin/vendors/${vendor.id}/verified-status`,
      { verified_status },
      { preserveScroll: true, preserveState: true, replace: true },
    );
  };

  const address = vendor?.default_address;
  const addressLabel = address
    ? [address.district, address.tole].filter(Boolean).join(', ')
    : 'N/A';

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">{vendor.business_name}</h1>
          <p className="text-muted-foreground">Vendor details</p>
        </div>
        <Button asChild variant="outline">
          <Link href="/admin/vendors">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <CardTitle>Overview</CardTitle>
          <div className="flex items-center gap-3">
            <Badge variant="outline" className="capitalize">{getCategoryLabel()}</Badge>
            <Select value={vendor.verified_status || 'pending'} onValueChange={updateVerifiedStatus}>
              <SelectTrigger className="w-44">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="verified">Verified</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
                <SelectItem value="suspended">Suspended</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-1">
              <div className="text-sm text-muted-foreground">Owner</div>
              <div className="font-medium">{vendor.user?.name || 'N/A'}</div>
              <div className="text-sm text-muted-foreground">{vendor.user?.email || ''}</div>
            </div>

            <div className="space-y-1">
              <div className="text-sm text-muted-foreground">Public contact</div>
              <div className="font-medium">{vendor.public_email || 'N/A'}</div>
              <div className="text-sm text-muted-foreground">{vendor.public_phone || ''}</div>
            </div>

            <div className="space-y-1">
              <div className="text-sm text-muted-foreground">Business type</div>
              <div className="font-medium capitalize">{vendor.business_type || 'N/A'}</div>
            </div>

            <div className="space-y-1">
              <div className="text-sm text-muted-foreground">Address</div>
              <div className="font-medium">{addressLabel}</div>
            </div>

            <div className="space-y-1 md:col-span-2">
              <div className="text-sm text-muted-foreground">Description</div>
              <div className="text-sm">{vendor.description || 'N/A'}</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

AdminVendorView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminVendorView;

