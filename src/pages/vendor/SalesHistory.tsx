import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { DollarSign, ShoppingBag, Search, TrendingUp, Calendar } from 'lucide-react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const mockSales = [
  { id: 'SAL-001', deal: 'Gourmet Pizza Deal', customer: 'Sarah Johnson', quantity: 2, unitPrice: 22.99, total: 45.98, date: '2024-01-19', status: 'completed' },
  { id: 'SAL-002', deal: 'Spa Relaxation Package', customer: 'Mike Chen', quantity: 1, unitPrice: 89.00, total: 89.00, date: '2024-01-19', status: 'completed' },
  { id: 'SAL-003', deal: 'Weekend Brunch Special', customer: 'Emily Davis', quantity: 3, unitPrice: 22.50, total: 67.50, date: '2024-01-18', status: 'completed' },
  { id: 'SAL-004', deal: 'Fitness Class Pack', customer: 'Lisa Anderson', quantity: 1, unitPrice: 120.00, total: 120.00, date: '2024-01-18', status: 'refunded' },
  { id: 'SAL-005', deal: 'Coffee Lover Bundle', customer: 'James Wilson', quantity: 4, unitPrice: 8.99, total: 35.96, date: '2024-01-17', status: 'completed' },
  { id: 'SAL-006', deal: 'Gourmet Pizza Deal', customer: 'Anna Brown', quantity: 1, unitPrice: 22.99, total: 22.99, date: '2024-01-17', status: 'completed' },
  { id: 'SAL-007', deal: 'Spa Relaxation Package', customer: 'David Lee', quantity: 2, unitPrice: 89.00, total: 178.00, date: '2024-01-16', status: 'completed' },
  { id: 'SAL-008', deal: 'Weekend Brunch Special', customer: 'Sophie Turner', quantity: 2, unitPrice: 22.50, total: 45.00, date: '2024-01-16', status: 'completed' },
  { id: 'SAL-009', deal: 'Gourmet Pizza Deal', customer: 'Robert Kim', quantity: 3, unitPrice: 22.99, total: 68.97, date: '2024-01-15', status: 'completed' },
  { id: 'SAL-010', deal: 'Coffee Lover Bundle', customer: 'Maria Garcia', quantity: 2, unitPrice: 8.99, total: 17.98, date: '2024-01-15', status: 'refunded' },
];

// Aggregate sales per deal
const dealSummary = mockSales.reduce((acc, sale) => {
  if (!acc[sale.deal]) acc[sale.deal] = { deal: sale.deal, totalSold: 0, totalRevenue: 0, transactions: 0 };
  acc[sale.deal].totalSold += sale.quantity;
  acc[sale.deal].totalRevenue += sale.total;
  acc[sale.deal].transactions += 1;
  return acc;
}, {} as Record<string, { deal: string; totalSold: number; totalRevenue: number; transactions: number }>);

const SalesHistory = () => {
  const [searchTerm, setSearchTerm] = useState('');

  const totalRevenue = mockSales.filter(s => s.status === 'completed').reduce((s, d) => s + d.total, 0);
  const totalSold = mockSales.filter(s => s.status === 'completed').reduce((s, d) => s + d.quantity, 0);

  const filtered = mockSales.filter(s =>
    s.deal.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.customer.toLowerCase().includes(searchTerm.toLowerCase()) ||
    s.id.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return 'bg-green-500';
      case 'refunded': return 'bg-orange-500';
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
          <CardContent><div className="text-2xl font-bold">${totalRevenue.toLocaleString()}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Transactions</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{mockSales.length}</div></CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Refunds</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-orange-600">{mockSales.filter(s => s.status === 'refunded').length}</div></CardContent>
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
                {Object.values(dealSummary).sort((a, b) => b.totalSold - a.totalSold).map(row => (
                  <TableRow key={row.deal}>
                    <TableCell className="font-medium">{row.deal}</TableCell>
                    <TableCell className="text-right">{row.totalSold}</TableCell>
                    <TableCell className="text-right">{row.transactions}</TableCell>
                    <TableCell className="text-right font-medium">${row.totalRevenue.toLocaleString()}</TableCell>
                  </TableRow>
                ))}
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
                {filtered.map(sale => (
                  <TableRow key={sale.id}>
                    <TableCell className="font-mono text-xs">{sale.id}</TableCell>
                    <TableCell className="font-medium max-w-[150px] truncate">{sale.deal}</TableCell>
                    <TableCell className="hidden md:table-cell">{sale.customer}</TableCell>
                    <TableCell className="text-right">{sale.quantity}</TableCell>
                    <TableCell className="text-right">${sale.unitPrice.toFixed(2)}</TableCell>
                    <TableCell className="text-right font-medium">${sale.total.toFixed(2)}</TableCell>
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

export default SalesHistory;
