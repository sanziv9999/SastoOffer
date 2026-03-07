
import { useParams, useNavigate } from 'react-router-dom';
import {
    ArrowLeft,
    Printer,
    Download,
    MapPin,
    Clock,
    CheckCircle2,
    AlertCircle,
    QrCode,
    Tag,
    Building2,
    ShoppingBag
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { format } from 'date-fns';

interface VoucherDetailProps {
    purchases: any[];
    deals: any[];
    vendors: any[];
}

const VoucherDetail = ({ purchases, deals, vendors }: VoucherDetailProps) => {
    const { id } = useParams<{ id: string }>();

    const purchase = purchases?.find(p => p.id === id);
    const deal = deals?.find(d => d.id === purchase?.dealId);
    const vendor = vendors?.find(v => v.id === deal?.vendorId);

    if (!purchase || !deal) {
        return (
            <div className="flex flex-col items-center justify-center py-12 text-center">
                <AlertCircle className="h-12 w-12 text-muted-foreground mb-4" />
                <h2 className="text-xl font-semibold">Voucher Not Found</h2>
                <p className="text-muted-foreground mb-6">We couldn't find the voucher details you're looking for.</p>
                <Button asChild>
                    <Link href="/dashboard/purchases">Back to My Purchases</Link>
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href="/dashboard/purchases">
                            <ArrowLeft className="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Voucher Details</h1>
                        <p className="text-sm text-muted-foreground">Order ID: #{purchase.id}</p>
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" size="sm" onClick={() => window.print()}>
                        <Printer className="h-4 w-4 mr-2" />
                        Print
                    </Button>
                    <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 mr-2" />
                        Download PDF
                    </Button>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                {/* Main Voucher Card */}
                <Card className="md:col-span-2 overflow-hidden border-2 border-primary/20">
                    <div className="bg-primary/5 p-6 border-b border-primary/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div className="flex items-center gap-3">
                            <div className="h-12 w-12 bg-primary rounded-full flex items-center justify-center text-primary-foreground">
                                <Tag className="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle className="text-xl">{deal.title}</CardTitle>
                                <div className="flex items-center gap-2 mt-1">
                                    <Badge variant={purchase.redeemed ? "outline" : "default"} className={purchase.redeemed ? "bg-muted" : "bg-green-500"}>
                                        {purchase.redeemed ? "Redeemed" : "Active & Ready to Use"}
                                    </Badge>
                                    {!purchase.redeemed && (
                                        <span className="text-xs text-muted-foreground flex items-center gap-1">
                                            <Clock className="h-3 w-3" />
                                            Expires {format(new Date(deal.endDate), 'MMM dd, yyyy')}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="text-right">
                            <div className="text-xs text-muted-foreground uppercase tracking-wider font-semibold">Voucher Code</div>
                            <div className="text-2xl font-black font-mono tracking-tighter text-primary">
                                {purchase.couponCode}
                            </div>
                        </div>
                    </div>

                    <CardContent className="p-0">
                        <div className="p-6 grid gap-6 md:grid-cols-2">
                            <div className="space-y-4">
                                <div>
                                    <h4 className="font-semibold text-sm uppercase text-muted-foreground mb-2">Redemption Instructions</h4>
                                    <p className="text-sm leading-relaxed">
                                        {deal.redemptionInstructions || "Present your mobile voucher or a printed copy upon arrival. No appointment necessary unless specified."}
                                    </p>
                                </div>

                                <Separator />

                                <div>
                                    <h4 className="font-semibold text-sm uppercase text-muted-foreground mb-2">Important Information</h4>
                                    <ul className="text-sm space-y-2 list-disc pl-4">
                                        <li>Valid only during the deal duration until {format(new Date(deal.endDate), 'PP')}.</li>
                                        <li>Cannot be combined with other offers or promotions.</li>
                                        <li>No cash value. Taxes and gratuities not included.</li>
                                    </ul>
                                </div>
                            </div>

                            {/* QR Code Section */}
                            <div className="flex flex-col items-center justify-center bg-muted/30 rounded-xl p-6 border-2 border-dashed border-muted">
                                <div className="bg-white p-4 rounded-lg shadow-sm mb-4">
                                    {/* Mock QR Code using Lucide for prototype feel */}
                                    <div className="relative h-40 w-40 flex items-center justify-center border-4 border-black">
                                        <QrCode className="h-32 w-32" />
                                        <div className="absolute inset-0 flex items-center justify-center opacity-10">
                                            <Badge className="font-mono text-[8px]">SASTO OFFER</Badge>
                                        </div>
                                    </div>
                                </div>
                                <div className="text-center">
                                    <div className="font-bold text-sm">Scan at Merchant</div>
                                    <div className="text-xs text-muted-foreground mt-1">Voucher ID: {purchase.couponCode}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter className="bg-muted/10 border-t p-4 flex justify-between items-center text-xs text-muted-foreground">
                        <div className="flex items-center gap-1">
                            <CheckCircle2 className="h-3 w-3 text-green-500" />
                            Verified Transaction
                        </div>
                        <div>Purchased on {format(new Date(purchase.createdAt), 'PPP')}</div>
                    </CardFooter>
                </Card>

                {/* Sidebar Info */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-base flex items-center gap-2">
                                <Building2 className="h-4 w-4" />
                                Merchant Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-center gap-3">
                                <img
                                    src={vendor?.logo || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=120&h=120&auto=format&fit=crop'}
                                    alt={vendor?.businessName}
                                    className="h-10 w-10 rounded-full object-cover"
                                />
                                <div className="font-semibold">{vendor?.businessName}</div>
                            </div>
                            <Separator />
                            <div className="space-y-3 text-sm">
                                <div className="flex items-start gap-2">
                                    <MapPin className="h-4 w-4 text-muted-foreground shrink-0 mt-0.5" />
                                    <span>{vendor?.location?.address}, {vendor?.location?.city}, {vendor?.location?.state}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Clock className="h-4 w-4 text-muted-foreground" />
                                    <span>Open: 9:00 AM - 9:00 PM</span>
                                </div>
                            </div>
                            <Button variant="outline" className="w-full" size="sm" asChild>
                                <Link href={`/vendor-profile/${vendor?.id}`}>View Profile</Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-base flex items-center gap-2">
                                <ShoppingBag className="h-4 w-4" />
                                Purchase Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm space-y-2">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Original Price</span>
                                <span className="line-through">${deal.originalPrice.toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Quantity</span>
                                <span>{purchase.quantity}</span>
                            </div>
                            <Separator className="my-2" />
                            <div className="flex justify-between font-bold text-lg text-primary">
                                <span>Total Paid</span>
                                <span>${purchase.totalPrice.toFixed(2)}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
};

VoucherDetail.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VoucherDetail;
