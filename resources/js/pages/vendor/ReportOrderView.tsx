import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Calendar, Mail, Receipt, ShoppingBag, User } from 'lucide-react';
import { format } from 'date-fns';

interface OrderItemDetail {
  id: number;
  title: string;
  quantity: number;
  unitPrice: number;
  lineTotal: number;
  image: string;
  offerType: string;
}

interface VendorOrderDetail {
  id: string;
  orderId: number;
  customer: string;
  customerEmail: string;
  subtotal: number;
  discountTotal: number;
  taxTotal: number;
  total: number;
  status: string;
  date: string;
  items: OrderItemDetail[];
}

interface ReportOrderViewProps {
  order: VendorOrderDetail;
}

const formatRs = (n: number) => `Rs. ${Number(n || 0).toFixed(2)}`;

const ReportOrderView = ({ order }: ReportOrderViewProps) => {
  return (
    <div className="space-y-6 max-w-6xl">
      <div className="space-y-3">
        <Button variant="ghost" size="sm" className="-ml-2 gap-1.5" asChild>
          <Link href={route('vendor.reports')}>
            <ArrowLeft className="h-4 w-4" />
            Back to Reports
          </Link>
        </Button>
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 className="text-2xl font-bold tracking-tight">Sales Report Detail</h1>
            <p className="text-muted-foreground text-sm">Order #{order.id}</p>
          </div>
          <Badge variant="outline" className="w-fit capitalize">{order.status}</Badge>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm flex items-center gap-2"><User className="h-4 w-4" /> Customer</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="font-semibold">{order.customer}</p>
            <p className="text-xs text-muted-foreground mt-1 inline-flex items-center gap-1"><Mail className="h-3.5 w-3.5" />{order.customerEmail || 'No email'}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm flex items-center gap-2"><Calendar className="h-4 w-4" /> Report Date</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="font-semibold">{order.date ? format(new Date(order.date), 'PPP p') : '-'}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm flex items-center gap-2"><Receipt className="h-4 w-4" /> Order Summary</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="font-semibold">{order.items?.length || 0} line items</p>
            <p className="text-xs text-muted-foreground mt-1">Total qty: {order.items?.reduce((s, i) => s + Number(i.quantity || 0), 0)}</p>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Items Purchased</CardTitle>
          <CardDescription>Detailed product-level breakdown for this order</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          <div className="md:hidden space-y-2">
            {order.items?.map((item) => (
              <div key={item.id} className="rounded-md border p-3">
                <p className="text-sm font-semibold line-clamp-1">{item.title}</p>
                <p className="text-xs text-muted-foreground mt-0.5">{item.offerType}</p>
                <div className="mt-1 flex items-center justify-between text-xs">
                  <span>Qty: {item.quantity}</span>
                  <span>{formatRs(item.unitPrice)}</span>
                  <span className="font-semibold">{formatRs(item.lineTotal)}</span>
                </div>
              </div>
            ))}
          </div>
          <div className="hidden md:block rounded-md border overflow-auto">
            <table className="w-full min-w-[760px] text-sm">
              <thead className="bg-muted/30 border-b">
                <tr>
                  <th className="text-left p-3 font-medium">Product</th>
                  <th className="text-left p-3 font-medium">Offer Type</th>
                  <th className="text-right p-3 font-medium">Qty</th>
                  <th className="text-right p-3 font-medium">Unit Price</th>
                  <th className="text-right p-3 font-medium">Line Total</th>
                </tr>
              </thead>
              <tbody>
                {order.items?.map((item) => (
                  <tr key={item.id} className="border-b">
                    <td className="p-3">
                      <div className="flex items-center gap-3">
                        {item.image ? (
                          <img src={item.image} alt="" className="h-10 w-10 rounded object-cover border" />
                        ) : (
                          <div className="h-10 w-10 rounded border bg-muted flex items-center justify-center">
                            <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                          </div>
                        )}
                        <span className="font-medium">{item.title}</span>
                      </div>
                    </td>
                    <td className="p-3">{item.offerType}</td>
                    <td className="p-3 text-right">{item.quantity}</td>
                    <td className="p-3 text-right">{formatRs(item.unitPrice)}</td>
                    <td className="p-3 text-right font-semibold">{formatRs(item.lineTotal)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <Separator />
          <div className="max-w-sm ml-auto space-y-2 text-sm">
            <div className="flex justify-between"><span className="text-muted-foreground">Subtotal</span><span>{formatRs(order.subtotal)}</span></div>
            <div className="flex justify-between"><span className="text-muted-foreground">Discount</span><span>- {formatRs(order.discountTotal)}</span></div>
            <div className="flex justify-between"><span className="text-muted-foreground">Tax</span><span>{formatRs(order.taxTotal)}</span></div>
            <Separator />
            <div className="flex justify-between text-base font-semibold"><span>Grand Total</span><span>{formatRs(order.total)}</span></div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

ReportOrderView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default ReportOrderView;

