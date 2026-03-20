import { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { DollarSign, ShoppingBag, Search, TrendingUp, Calendar } from 'lucide-react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DashboardLayout from '@/layouts/DashboardLayout';

interface SalesHistoryProps {
  sales: any[];
}

const SalesHistory = ({ sales }: SalesHistoryProps) => {
  const [searchTerm, setSearchTerm] = useState('');

  const salesList = useMemo(() => sales || [], [sales]);

  const dealSummary = useMemo(() => {
    return salesList.reduce((acc: any, sale: any) => {
      const dealTitle = sale.deal || 'Unknown Deal';
      if (!acc[dealTitle]) acc[dealTitle] = { deal: dealTitle, totalSold: 0, totalRevenue: 0, transactions: 0 };
      acc[dealTitle].totalSold += (sale.quantity || 0);
      acc[dealTitle].totalRevenue += (sale.total || 0);
      acc[dealTitle].transactions += 1;
      return acc;
    }, {});
  }, [salesList]);

  const totalRevenue = useMemo(
    () => salesList.filter((s: any) => !['cancelled', 'refunded'].includes(String(s.status))).reduce((s: number, d: any) => s + (d.total || 0), 0),
    [salesList],
  );
  const totalSold = useMemo(
    () => salesList.filter((s: any) => !['cancelled', 'refunded'].includes(String(s.status))).reduce((s: number, d: any) => s + (d.quantity || 0), 0),
    [salesList],
  );

  const filtered = useMemo(() => salesList.filter((s: any) =>
    s.deal?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.customer?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.id?.toLowerCase().includes(searchTerm.toLowerCase())
  ), [salesList, searchTerm]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'fulfilled': return 'bg-green-500';
      case 'paid': return 'bg-blue-500';
      case 'pending': return 'bg-amber-500';
      case 'refunded': return 'bg-orange-500';
      case 'cancelled': return 'bg-red-500';
      default: return 'bg-muted';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Sales History</h1>
        <p className="text-muted-foreground">Complete record of all sales transactions</p>
      </div>

      <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Sales</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{totalSold}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Revenue</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">Rs. {totalRevenue.toLocaleString()}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Transactions</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{salesList.length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Refunds</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-orange-600">{salesList.filter((s: any) => s.status === 'refunded').length}</div></CardContent>
        </Card>
      </div>

      {/* Per-deal summary */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Sales per Deal</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Deal</TableHead>
                  <TableHead className="text-right">Units Sold</TableHead>
                  <TableHead className="text-right">Transactions</TableHead>
                  <TableHead className="text-right">Revenue</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {Object.values(dealSummary).length > 0 ? Object.values(dealSummary).sort((a: any, b: any) => b.totalSold - a.totalSold).map((row: any) => (
                  <TableRow key={row.deal}>
                    <TableCell className="font-medium">{row.deal}</TableCell>
                    <TableCell className="text-right">{row.totalSold}</TableCell>
                    <TableCell className="text-right">{row.transactions}</TableCell>
                    <TableCell className="text-right font-medium">Rs. {row.totalRevenue.toLocaleString()}</TableCell>
                  </TableRow>
                )) : (
                  <TableRow><TableCell colSpan={4} className="text-center py-4 text-muted-foreground">No deal summary available</TableCell></TableRow>
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* All transactions */}
      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <CardTitle className="text-base">All Transactions</CardTitle>
            <div className="relative w-full sm:w-72">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Search sales..." className="pl-9" value={searchTerm} onChange={e => setSearchTerm(e.target.value)} />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Sale ID</TableHead>
                  <TableHead>Deal</TableHead>
                  <TableHead className="hidden md:table-cell">Customer</TableHead>
                  <TableHead className="text-right">Qty</TableHead>
                  <TableHead className="text-right">Unit Price</TableHead>
                  <TableHead className="text-right">Total</TableHead>
                  <TableHead className="hidden sm:table-cell">Date</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filtered.map((sale: any) => (
                  <TableRow key={sale.id}>
                    <TableCell className="font-mono text-xs">{sale.id}</TableCell>
                    <TableCell className="font-medium max-w-[150px] truncate">{sale.deal}</TableCell>
                    <TableCell className="hidden md:table-cell">{sale.customer}</TableCell>
                    <TableCell className="text-right">{sale.quantity}</TableCell>
                    <TableCell className="text-right">Rs. {sale.unitPrice?.toFixed(2)}</TableCell>
                    <TableCell className="text-right font-medium">Rs. {sale.total?.toFixed(2)}</TableCell>
                    <TableCell className="hidden sm:table-cell text-muted-foreground">{sale.date}</TableCell>
                    <TableCell>
                      <Badge className={`${getStatusColor(sale.status)} text-white`}>{sale.status}</Badge>
                    </TableCell>
                  </TableRow>
                ))}
                {filtered.length === 0 && (
                  <TableRow><TableCell colSpan={8} className="text-center py-8 text-muted-foreground">No sales found</TableCell></TableRow>
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

SalesHistory.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default SalesHistory;
