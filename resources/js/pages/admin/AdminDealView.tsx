import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const AdminDealView = () => {
  const { deal } = usePage().props as any;

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">{deal?.title}</h1>
          <p className="text-muted-foreground">
            Admin deal preview • Deal ID: {deal?.id}
          </p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/deals">Back</Link>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Deal</CardTitle>
          <CardDescription>Parent deal fields from `deals` table.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="flex flex-wrap items-center gap-2">
            <Badge variant={deal?.status === 'active' ? 'default' : 'outline'}>
              {deal?.status || 'draft'}
            </Badge>
            {deal?.category?.parentName && <Badge variant="outline">{deal.category.parentName}</Badge>}
            {deal?.category?.name && <Badge variant="outline">{deal.category.name}</Badge>}
          </div>

          <div className="text-sm"><span className="text-muted-foreground">Vendor:</span> {deal?.vendorName || 'N/A'}</div>
          <div className="text-sm"><span className="text-muted-foreground">Base price:</span> Rs. {deal?.basePrice ?? '-'}</div>
          {deal?.shortDesc && <div className="text-sm"><span className="text-muted-foreground">Summary:</span> {deal.shortDesc}</div>}
          {deal?.description && <div className="text-sm"><span className="text-muted-foreground">Description:</span> {deal.description}</div>}

          {deal?.image && (
            <img src={deal.image} alt={deal.title} className="w-full max-w-lg rounded-md border object-cover" />
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Child Offer Types</CardTitle>
          <CardDescription>Rows from `deal_offer_type`. Featured status is managed per offer.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          {Array.isArray(deal?.offers) && deal.offers.length > 0 ? (
            deal.offers.map((o: any) => (
              <div key={o.id} className="border rounded-md p-3 space-y-2">
                <div className="flex flex-wrap items-center gap-2">
                  <div className="font-medium">{o.offerTypeTitle} <span className="text-muted-foreground text-xs">#{o.id}</span></div>
                  <Badge variant={o.status === 'active' ? 'default' : 'outline'}>{o.status}</Badge>
                </div>
                <div className="text-sm text-muted-foreground">
                  Original: Rs. {o.originalPrice ?? '-'} • Final: Rs. {o.finalPrice ?? '-'} • {o.currencyCode || 'NPR'}
                </div>
                <div className="text-xs text-muted-foreground">
                  Start: {o.startsAt || '-'} • End: {o.endsAt || '-'}
                </div>
                <div className="flex flex-wrap gap-1">
                  {(o.displayTypes || []).includes('featured') ? (
                    <Badge className="bg-amber-600 hover:bg-amber-600">Featured</Badge>
                  ) : (
                    <span className="text-xs text-muted-foreground">Not featured</span>
                  )}
                </div>
              </div>
            ))
          ) : (
            <p className="text-sm text-muted-foreground">No offers attached yet.</p>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

AdminDealView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminDealView;

