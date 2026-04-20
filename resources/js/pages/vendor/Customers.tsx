
import { useState } from 'react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Search, Mail, MapPin } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

interface CustomersProps {
  customers: any[];
}

const Customers = ({ customers }: CustomersProps) => {
  const [searchTerm, setSearchTerm] = useState('');

  const filtered = (customers || []).filter((c: any) =>
    c.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    c.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    c.city?.toLowerCase().includes(searchTerm.toLowerCase())
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
          <CardContent><div className="text-2xl font-bold">{customers?.length || 0}</div></CardContent>
        </Card>
        <Card className="flex-1 min-w-[150px]">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Active</CardTitle></CardHeader>
          <CardContent><div className="text-2xl font-bold text-green-600">{customers?.filter((c: any) => c.status === 'active').length}</div></CardContent>
        </Card>
        <Card className="flex-1 min-w-[150px]">
          <CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Avg. Spend</CardTitle></CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rs. {customers?.length > 0 ? (customers.reduce((s: number, c: any) => s + (c.totalSpent || 0), 0) / customers.length).toFixed(0) : '0'}
            </div>
          </CardContent>
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
          <div className="space-y-3">
            {/* Mobile: stacked customer cards */}
            <div className="md:hidden space-y-3">
              {filtered.length > 0 ? (
                filtered.map((customer: any) => (
                  <div key={customer.id} className="rounded-lg border p-3 space-y-2.5">
                    <div className="flex items-start justify-between gap-2">
                      <div className="min-w-0">
                        <p className="font-medium truncate">{customer.name}</p>
                        <p className="text-xs text-muted-foreground flex items-center gap-1 truncate">
                          <Mail className="h-3 w-3 shrink-0" />
                          {customer.email}
                        </p>
                        <p className="text-xs text-muted-foreground flex items-center gap-1 truncate mt-0.5">
                          <MapPin className="h-3 w-3 shrink-0" />
                          {customer.city || 'N/A'}
                        </p>
                      </div>
                      <Badge
                        variant={customer.status === 'active' ? 'default' : 'secondary'}
                        className={customer.status === 'active' ? 'bg-green-500' : ''}
                      >
                        {customer.status}
                      </Badge>
                    </div>

                    <div className="grid grid-cols-3 gap-2 text-xs">
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Orders</p>
                        <p className="font-semibold">{customer.totalOrders || 0}</p>
                      </div>
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Deals</p>
                        <p className="font-semibold">{customer.dealsPurchased || 0}</p>
                      </div>
                      <div className="rounded-md border p-2">
                        <p className="text-muted-foreground">Spent</p>
                        <p className="font-semibold">Rs. {customer.totalSpent?.toFixed(2) || '0.00'}</p>
                      </div>
                    </div>

                    <div className="rounded-md border p-2.5 text-xs">
                      <div className="flex items-center justify-between gap-2 mb-1">
                        <p className="text-muted-foreground">Bought / Claimed</p>
                        <Link
                          href={route('vendor.customers.history.show', customer.id)}
                          className="text-[11px] font-medium text-primary hover:underline"
                        >
                          View list
                        </Link>
                      </div>
                      {Array.isArray(customer.boughtItems) && customer.boughtItems.length > 0 ? (
                        <div className="space-y-0.5">
                          {customer.boughtItems.map((item: string, idx: number) => (
                            <p key={`${customer.id}-item-${idx}`} className="truncate text-foreground">{item}</p>
                          ))}
                          {(customer.boughtItemsCount || customer.boughtItems.length) > customer.boughtItems.length && (
                            <p className="text-muted-foreground">
                              +{(customer.boughtItemsCount || customer.boughtItems.length) - customer.boughtItems.length} more
                            </p>
                          )}
                        </div>
                      ) : (
                        <p className="text-muted-foreground">No product/service history yet.</p>
                      )}
                      <div className="mt-1.5">
                        <p className="text-muted-foreground mb-0.5">
                          Claimed: <span className="font-semibold text-foreground">{customer.claimedCount || 0}</span>
                        </p>
                        {Array.isArray(customer.claimedItems) && customer.claimedItems.length > 0 && (
                          <div className="space-y-0.5">
                            {customer.claimedItems.map((item: string, idx: number) => (
                              <p key={`${customer.id}-claimed-${idx}`} className="truncate text-foreground">
                                - {item}
                              </p>
                            ))}
                            {(customer.claimedItemsCount || customer.claimedItems.length) > customer.claimedItems.length && (
                              <p className="text-muted-foreground">
                                +{(customer.claimedItemsCount || customer.claimedItems.length) - customer.claimedItems.length} more claimed
                              </p>
                            )}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="rounded-md border p-8 text-center text-muted-foreground">
                  No customers found.
                </div>
              )}
            </div>

            {/* Desktop/tablet: table */}
            <div className="hidden md:block rounded-md border overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="border-b bg-muted/50">
                  <tr>
                    <th className="h-10 px-4 text-left font-medium">Customer</th>
                    <th className="h-10 px-4 text-left font-medium hidden md:table-cell">Contact</th>
                    <th className="h-10 px-4 text-left font-medium">Orders</th>
                    <th className="h-10 px-4 text-left font-medium">Spent</th>
                    <th className="h-10 px-4 text-left font-medium hidden sm:table-cell">Deals</th>
                    <th className="h-10 px-4 text-left font-medium hidden lg:table-cell">Bought / Claimed</th>
                    <th className="h-10 px-4 text-left font-medium">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {filtered.map((customer: any) => (
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
                      <td className="p-4 font-medium">{customer.totalOrders || 0}</td>
                      <td className="p-4 font-medium">Rs. {customer.totalSpent?.toFixed(2) || '0.00'}</td>
                      <td className="p-4 hidden sm:table-cell font-medium">{customer.dealsPurchased || 0}</td>
                      <td className="p-4 hidden lg:table-cell">
                        {Array.isArray(customer.boughtItems) && customer.boughtItems.length > 0 ? (
                          <div className="text-xs">
                            <div className="space-y-0.5 max-w-[220px]">
                              {customer.boughtItems.map((item: string, idx: number) => (
                                <p key={`${customer.id}-desktop-bought-${idx}`} className="truncate" title={item}>
                                  - {item}
                                </p>
                              ))}
                              {(customer.boughtItemsCount || customer.boughtItems.length) > customer.boughtItems.length && (
                                <p className="text-muted-foreground">
                                  +{(customer.boughtItemsCount || customer.boughtItems.length) - customer.boughtItems.length} more
                                </p>
                              )}
                            </div>
                            <p className="text-muted-foreground mt-1">
                              Claimed: <span className="font-medium text-foreground">{customer.claimedCount || 0}</span>
                            </p>
                            <Link
                              href={route('vendor.customers.history.show', customer.id)}
                              className="inline-block mt-1 text-[11px] font-medium text-primary hover:underline"
                            >
                              View list
                            </Link>
                          </div>
                        ) : (
                          <div>
                            <p className="text-xs text-muted-foreground">No product/service history</p>
                            <Link
                              href={route('vendor.customers.history.show', customer.id)}
                              className="inline-block mt-1 text-[11px] font-medium text-primary hover:underline"
                            >
                              View list
                            </Link>
                          </div>
                        )}
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
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

Customers.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Customers;
