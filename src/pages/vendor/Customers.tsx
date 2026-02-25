import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Search, Mail, Phone, MapPin, Star } from 'lucide-react';

const mockCustomers = [
  { id: '1', name: 'Sarah Johnson', email: 'sarah@example.com', phone: '+1 555-0101', city: 'New York', totalOrders: 12, totalSpent: 489.50, rating: 4.8, lastOrder: '2024-01-15', status: 'active' },
  { id: '2', name: 'Mike Chen', email: 'mike@example.com', phone: '+1 555-0102', city: 'Los Angeles', totalOrders: 8, totalSpent: 320.00, rating: 4.5, lastOrder: '2024-01-10', status: 'active' },
  { id: '3', name: 'Emily Davis', email: 'emily@example.com', phone: '+1 555-0103', city: 'Chicago', totalOrders: 15, totalSpent: 675.25, rating: 4.9, lastOrder: '2024-01-18', status: 'active' },
  { id: '4', name: 'James Wilson', email: 'james@example.com', phone: '+1 555-0104', city: 'Houston', totalOrders: 3, totalSpent: 89.99, rating: 4.2, lastOrder: '2023-12-20', status: 'inactive' },
  { id: '5', name: 'Lisa Anderson', email: 'lisa@example.com', phone: '+1 555-0105', city: 'Phoenix', totalOrders: 22, totalSpent: 1250.00, rating: 5.0, lastOrder: '2024-01-19', status: 'active' },
];

const Customers = () => {
  const [searchTerm, setSearchTerm] = useState('');

  const filtered = mockCustomers.filter(c =>
    c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    c.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Customers</h1>
        <p className="text-muted-foreground">Manage and view your customer base</p>
      </div>

      <div className="flex gap-4 flex-wrap">
        <Card className="flex-1 min-w-[150px]">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Total Customers</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">{mockCustomers.length}</div></CardContent>
        </Card>
        <Card className="flex-1 min-w-[150px]">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Active</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{mockCustomers.filter(c => c.status === 'active').length}</div></CardContent>
        </Card>
        <Card className="flex-1 min-w-[150px]">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Avg. Spend</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold">${(mockCustomers.reduce((s, c) => s + c.totalSpent, 0) / mockCustomers.length).toFixed(0)}</div></CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <CardTitle>Customer List</CardTitle>
              <CardDescription>View and manage all customers</CardDescription>
            </div>
            <div className="relative w-full sm:w-72">
              <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Search customers..." className="pl-9" value={searchTerm} onChange={e => setSearchTerm(e.target.value)} />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="border-b bg-muted/50">
                <tr>
                  <th className="h-10 px-4 text-left font-medium">Customer</th>
                  <th className="h-10 px-4 text-left font-medium hidden md:table-cell">Contact</th>
                  <th className="h-10 px-4 text-left font-medium">Orders</th>
                  <th className="h-10 px-4 text-left font-medium">Spent</th>
                  <th className="h-10 px-4 text-left font-medium hidden sm:table-cell">Rating</th>
                  <th className="h-10 px-4 text-left font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map(customer => (
                  <tr key={customer.id} className="border-b hover:bg-muted/50 transition-colors">
                    <td className="p-4">
                      <div className="font-medium">{customer.name}</div>
                      <div className="text-xs text-muted-foreground flex items-center gap-1 md:hidden">
                        <Mail className="h-3 w-3" />{customer.email}
                      </div>
                    </td>
                    <td className="p-4 hidden md:table-cell">
                      <div className="flex items-center gap-1 text-xs"><Mail className="h-3 w-3" />{customer.email}</div>
                      <div className="flex items-center gap-1 text-xs text-muted-foreground"><MapPin className="h-3 w-3" />{customer.city}</div>
                    </td>
                    <td className="p-4 font-medium">{customer.totalOrders}</td>
                    <td className="p-4 font-medium">${customer.totalSpent.toFixed(2)}</td>
                    <td className="p-4 hidden sm:table-cell">
                      <div className="flex items-center gap-1"><Star className="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />{customer.rating}</div>
                    </td>
                    <td className="p-4">
                      <Badge variant={customer.status === 'active' ? 'default' : 'secondary'} className={customer.status === 'active' ? 'bg-green-500' : ''}>
                        {customer.status}
                      </Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Customers;
