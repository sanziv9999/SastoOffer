import { useState } from 'react';
import { Link } from 'react-router-dom';
import { 
  BarChart, 
  DollarSign, 
  Users, 
  ShoppingBag, 
  Tag, 
  Store,
  TrendingUp,
  AlertTriangle,
  FileText,
  CheckCircle,
  XCircle,
  Package,
  Search,
  Settings,
  Database,
  Shield,
  Activity,
  CreditCard,
  Globe
} from 'lucide-react';
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { users, vendors, deals, purchases } from '@/data/mockData';

const SuperAdminDashboard = () => {
  const [searchTerm, setSearchTerm] = useState('');
  
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Searching for:', searchTerm);
  };
  
  // Platform statistics
  const totalUsers = users.filter(u => u.role === 'user').length;
  const totalAdmins = users.filter(u => u.role === 'admin').length;
  const totalVendors = vendors.length;
  const totalDeals = deals.length;
  const activeDeals = deals.filter(d => d.status === 'active').length;
  const totalRevenue = purchases.reduce((sum, purchase) => sum + purchase.totalPrice, 0);
  const platformCommission = totalRevenue * 0.15; // 15% commission
  
  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Super Admin Dashboard</h1>
          <p className="text-muted-foreground">
            Complete platform oversight and system administration
          </p>
        </div>
        
        <form onSubmit={handleSearch} className="flex w-full md:w-auto">
          <Input
            placeholder="Search platform data..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="md:w-80 rounded-r-none"
          />
          <Button type="submit" size="icon" className="rounded-l-none">
            <Search className="h-4 w-4" />
          </Button>
        </form>
      </div>
      
      {/* Key Metrics */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Platform Revenue</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${totalRevenue.toFixed(2)}</div>
            <p className="text-xs text-muted-foreground">
              Commission: ${platformCommission.toFixed(2)}
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalUsers}</div>
            <p className="text-xs text-muted-foreground">
              +{Math.floor(totalUsers * 0.12)} this month
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active Vendors</CardTitle>
            <Store className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalVendors}</div>
            <p className="text-xs text-muted-foreground">
              {totalAdmins} admins managing
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Platform Health</CardTitle>
            <Activity className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">98.5%</div>
            <p className="text-xs text-muted-foreground">
              System uptime
            </p>
          </CardContent>
        </Card>
      </div>

      {/* System Overview Cards */}
      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Database className="h-5 w-5" />
              Database Status
            </CardTitle>
            <CardDescription>Real-time database metrics</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="text-sm">Storage Used</span>
                <Badge variant="outline">2.4 GB / 10 GB</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Active Connections</span>
                <Badge variant="outline">127 / 500</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Query Performance</span>
                <Badge className="bg-green-500">Optimal</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="h-5 w-5" />
              Security Status
            </CardTitle>
            <CardDescription>Platform security overview</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="text-sm">Failed Login Attempts</span>
                <Badge variant="outline">23 today</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">SSL Certificate</span>
                <Badge className="bg-green-500">Valid</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Firewall Status</span>
                <Badge className="bg-green-500">Active</Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="h-5 w-5" />
              Payment Gateway
            </CardTitle>
            <CardDescription>Transaction processing status</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="text-sm">Today's Transactions</span>
                <Badge variant="outline">{purchases.length}</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Success Rate</span>
                <Badge className="bg-green-500">99.2%</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Gateway Status</span>
                <Badge className="bg-green-500">Online</Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Management Tabs */}
      <Card>
        <CardHeader>
          <CardTitle>System Administration</CardTitle>
          <CardDescription>
            Manage all aspects of the platform infrastructure and user management
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="admins" className="space-y-4">
            <TabsList className="grid grid-cols-2 md:grid-cols-5 gap-2">
              <TabsTrigger value="admins">
                <Shield className="h-4 w-4 mr-2" />
                Admins
              </TabsTrigger>
              <TabsTrigger value="system">
                <Settings className="h-4 w-4 mr-2" />
                System
              </TabsTrigger>
              <TabsTrigger value="analytics">
                <BarChart className="h-4 w-4 mr-2" />
                Analytics
              </TabsTrigger>
              <TabsTrigger value="payments">
                <CreditCard className="h-4 w-4 mr-2" />
                Payments
              </TabsTrigger>
              <TabsTrigger value="logs">
                <FileText className="h-4 w-4 mr-2" />
                Logs
              </TabsTrigger>
            </TabsList>
            
            <TabsContent value="admins" className="space-y-4">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold">Administrator Management</h3>
                <Button>
                  Add New Admin
                </Button>
              </div>
              
              <div className="rounded-md border">
                <div className="relative w-full overflow-auto">
                  <table className="w-full caption-bottom text-sm">
                    <thead className="border-b">
                      <tr className="border-b transition-colors hover:bg-muted/50">
                        <th className="h-12 px-4 text-left align-middle font-medium">Admin</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Email</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Last Login</th>
                        <th className="h-12 px-4 text-left align-middle font-medium">Status</th>
                        <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {users.filter(u => u.role === 'admin').map(admin => (
                        <tr key={admin.id} className="border-b transition-colors hover:bg-muted/50">
                          <td className="p-4 align-middle">
                            <div className="flex items-center gap-3">
                              <div className="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                {admin.name.charAt(0)}
                              </div>
                              <div className="font-medium">{admin.name}</div>
                            </div>
                          </td>
                          <td className="p-4 align-middle">{admin.email}</td>
                          <td className="p-4 align-middle">2 hours ago</td>
                          <td className="p-4 align-middle">
                            <Badge className="bg-green-500">Active</Badge>
                          </td>
                          <td className="p-4 align-middle text-right">
                            <Button variant="ghost" size="sm">Manage</Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </TabsContent>
            
            <TabsContent value="system" className="space-y-4">
              <div className="grid gap-4 md:grid-cols-2">
                <Card>
                  <CardHeader>
                    <CardTitle>Server Configuration</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-2">
                    <Button variant="outline" className="w-full justify-start">
                      <Settings className="h-4 w-4 mr-2" />
                      Database Settings
                    </Button>
                    <Button variant="outline" className="w-full justify-start">
                      <Globe className="h-4 w-4 mr-2" />
                      API Configuration
                    </Button>
                    <Button variant="outline" className="w-full justify-start">
                      <Shield className="h-4 w-4 mr-2" />
                      Security Settings
                    </Button>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader>
                    <CardTitle>Maintenance</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-2">
                    <Button variant="outline" className="w-full justify-start">
                      <Database className="h-4 w-4 mr-2" />
                      Backup Database
                    </Button>
                    <Button variant="outline" className="w-full justify-start">
                      <Activity className="h-4 w-4 mr-2" />
                      System Health Check
                    </Button>
                    <Button variant="outline" className="w-full justify-start">
                      <AlertTriangle className="h-4 w-4 mr-2" />
                      Maintenance Mode
                    </Button>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>
            
            <TabsContent value="analytics" className="space-y-4">
              <div className="text-center p-8 border rounded-md">
                <BarChart className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                <h3 className="text-lg font-semibold mb-2">Advanced Analytics</h3>
                <p className="text-muted-foreground">
                  Comprehensive platform analytics and reporting tools would be displayed here.
                </p>
              </div>
            </TabsContent>
            
            <TabsContent value="payments" className="space-y-4">
              <div className="text-center p-8 border rounded-md">
                <CreditCard className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                <h3 className="text-lg font-semibold mb-2">Payment Management</h3>
                <p className="text-muted-foreground">
                  Payment gateway configuration and transaction monitoring.
                </p>
              </div>
            </TabsContent>
            
            <TabsContent value="logs" className="space-y-4">
              <div className="text-center p-8 border rounded-md">
                <FileText className="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                <h3 className="text-lg font-semibold mb-2">System Logs</h3>
                <p className="text-muted-foreground">
                  View and analyze system logs, error reports, and audit trails.
                </p>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
};

export default SuperAdminDashboard;