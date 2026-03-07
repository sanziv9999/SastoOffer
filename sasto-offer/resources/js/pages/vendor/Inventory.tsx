
import { useState } from 'react';
import {
    Package,
    AlertTriangle,
    History,
    TrendingDown,
    RefreshCw,
    Search,
    AlertCircle
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import DashboardLayout from '@/layouts/DashboardLayout';

interface VendorInventoryProps {
    deals: any[];
}

const VendorInventory = ({ deals }: VendorInventoryProps) => {
    const [searchTerm, setSearchTerm] = useState('');

    const inventorySummary = {
        totalStock: deals?.reduce((acc, d) => acc + (d.maxQuantity || 0), 0) || 0,
        soldCount: deals?.reduce((acc, d) => acc + (d.quantitySold || 0), 0) || 0,
        lowStockCount: deals?.filter(d => {
            const remaining = (d.maxQuantity || 0) - (d.quantitySold || 0);
            return remaining > 0 && remaining < 10;
        }).length || 0,
        outOfStockCount: deals?.filter(d => (d.maxQuantity || 0) - (d.quantitySold || 0) <= 0).length || 0,
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Inventory & Stock</h1>
                    <p className="text-muted-foreground">Monitor deal availability and track coupon supplies.</p>
                </div>
                <Button variant="outline">
                    <RefreshCw className="mr-2 h-4 w-4" />
                    Sync Inventory
                </Button>
            </div>

            {/* Summary Cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Coupons</CardTitle>
                        <Package className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{inventorySummary.totalStock}</div>
                        <p className="text-xs text-muted-foreground text-green-600 font-medium">
                            Across {deals?.length || 0} active deals
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Remaining</CardTitle>
                        <History className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{inventorySummary.totalStock - inventorySummary.soldCount}</div>
                        <p className="text-xs text-muted-foreground">
                            {(((inventorySummary.totalStock - inventorySummary.soldCount) / inventorySummary.totalStock) * 100).toFixed(1)}% available
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Low Stock</CardTitle>
                        <AlertTriangle className="h-4 w-4 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{inventorySummary.lowStockCount}</div>
                        <p className="text-xs text-muted-foreground">
                            Deals with &lt; 10 units left
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Out of Stock</CardTitle>
                        <TrendingDown className="h-4 w-4 text-destructive" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{inventorySummary.outOfStockCount}</div>
                        <p className="text-xs text-muted-foreground">
                            Requires immediate restock
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Deal Stock Status</CardTitle>
                    <CardDescription>Detailed inventory breakdown per listing.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="relative mb-6">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            placeholder="Search by deal name or ID..."
                            className="pl-9"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>

                    <div className="space-y-6">
                        {deals?.map((deal) => {
                            const remaining = (deal.maxQuantity || 0) - (deal.quantitySold || 0);
                            const soldPercentage = ((deal.quantitySold || 0) / (deal.maxQuantity || 1)) * 100;
                            const isLow = remaining > 0 && remaining < 10;
                            const isOut = remaining <= 0;

                            return (
                                <div key={deal.id} className="space-y-3">
                                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-2">
                                        <div className="flex items-center gap-3">
                                            <div className="h-10 w-10 bg-muted rounded overflow-hidden">
                                                <img src={deal.image} alt={deal.title} className="w-full h-full object-cover" />
                                            </div>
                                            <div>
                                                <h4 className="font-semibold text-sm line-clamp-1">{deal.title}</h4>
                                                <div className="text-xs text-muted-foreground flex items-center gap-2">
                                                    <span>ID: {deal.id}</span>
                                                    <Separator orientation="vertical" className="h-3" />
                                                    <span>Ends: {new Date(deal.endDate).toLocaleDateString()}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <div className="text-right">
                                                <div className="text-sm font-bold">
                                                    {remaining} <span className="text-xs font-normal text-muted-foreground">/ {deal.maxQuantity} Left</span>
                                                </div>
                                                <div className="text-[10px] text-muted-foreground uppercase tracking-tight">
                                                    {deal.quantitySold} Sold
                                                </div>
                                            </div>
                                            <Badge
                                                variant={isOut ? "destructive" : isLow ? "outline" : "outline"}
                                                className={isOut ? "" : isLow ? "border-amber-400 text-amber-600 bg-amber-50" : "text-green-600 border-green-200 bg-green-50"}
                                            >
                                                {isOut ? "Sold Out" : isLow ? "Low Stock" : "Healthy"}
                                            </Badge>
                                        </div>
                                    </div>
                                    <div className="space-y-1">
                                        <Progress value={soldPercentage} className={`h-2 ${isOut ? 'bg-destructive/20' : ''}`} />
                                        <div className="flex justify-between text-[10px] text-muted-foreground">
                                            <span>0 sold</span>
                                            <span>{deal.maxQuantity} max</span>
                                        </div>
                                    </div>

                                    {isLow && (
                                        <div className="flex items-center gap-2 text-xs text-amber-600 bg-amber-50 p-2 rounded border border-amber-200 animate-pulse">
                                            <AlertCircle className="h-3.5 w-3.5" />
                                            Critical: Only {remaining} coupons remaining for this deal.
                                            <Button variant="link" size="sm" className="h-auto p-0 ml-auto text-amber-700 font-bold">Increase Limit</Button>
                                        </div>
                                    )}

                                    <Separator />
                                </div>
                            );
                        })}
                    </div>
                </CardContent>
                <CardFooter className="bg-muted/30 flex justify-center py-3">
                    <Button variant="ghost" size="sm">Download Detailed Report</Button>
                </CardFooter>
            </Card>

            {/* Restock Alerts Settings */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-base flex items-center gap-2">
                        <AlertTriangle className="h-4 w-4" />
                        Stock Alert Settings
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="flex items-center justify-between p-3 border rounded-lg">
                        <div>
                            <div className="font-medium text-sm">Low Stock Threshold</div>
                            <div className="text-xs text-muted-foreground">Notify me when a deal has fewer than this many units left.</div>
                        </div>
                        <Input type="number" className="w-20" defaultValue="10" />
                    </div>
                    <div className="flex items-center justify-between p-3 border rounded-lg">
                        <div>
                            <div className="font-medium text-sm">Out of Stock Notifications</div>
                            <div className="text-xs text-muted-foreground">Send immediate email and push alert when a deal sells out.</div>
                        </div>
                        <div className="flex h-6 w-10 items-center rounded-full bg-primary px-1">
                            <div className="h-4 w-4 rounded-full bg-white ml-auto" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

VendorInventory.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorInventory;
