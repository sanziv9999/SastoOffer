import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Search, UserPlus } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import DashboardLayout from '@/layouts/DashboardLayout';
import { toast } from 'sonner';
import { router } from '@inertiajs/react';
import AdminPagination from '@/components/AdminPagination';

interface AdminUsersProps {
  users: any;
  filters?: { search?: string };
}

const AdminUsers = ({ users: initialUsers, filters }: AdminUsersProps) => {
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const [users, setUsers] = useState<any[]>(initialUsers?.data || initialUsers || []);

  const [isAddUserOpen, setIsAddUserOpen] = useState(false);
  const [isEditUserOpen, setIsEditUserOpen] = useState(false);
  const [currentUser, setCurrentUser] = useState<any>(null);

  const [formData, setFormData] = useState({ name: '', email: '', role: 'customer' });

  const normalizeRole = (role?: string) => {
    const raw = String(role || '').toLowerCase();
    if (raw === 'user') return 'customer';
    return raw || 'customer';
  };

  const roleLabel = (role?: string) => {
    const normalized = normalizeRole(role);
    return normalized.charAt(0).toUpperCase() + normalized.slice(1);
  };

  const filteredUsers = users?.filter(u =>
    u.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    u.email?.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];

  const handleServerSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/users', { search: searchTerm || undefined }, { preserveState: true, replace: true });
  };

  const handleAddUser = () => {
    if (!formData.name || !formData.email) return;
    const newUser = {
      id: Math.random().toString(36).substr(2, 9),
      name: formData.name,
      email: formData.email,
      role: normalizeRole(formData.role),
      createdAt: new Date().toISOString()
    };
    setUsers([newUser, ...users]);
    setIsAddUserOpen(false);
    setFormData({ name: '', email: '', role: 'customer' });
    toast.success('User added successfully');
  };

  const openEditModal = (user: any) => {
    setCurrentUser(user);
    setFormData({ name: user.name, email: user.email, role: normalizeRole(user.role) });
    setIsEditUserOpen(true);
  };

  const handleEditUser = () => {
    if (!currentUser) return;

    router.patch(
      route('admin.users.update', currentUser.id),
      {
        name: formData.name,
        email: formData.email,
        role: normalizeRole(formData.role),
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          setIsEditUserOpen(false);
          setCurrentUser(null);
          toast.success('User updated successfully');
        },
        onError: () => {
          toast.error('Failed to update user.');
        },
      },
    );
  };

  const handleSuspend = (id: string) => {
    if (confirm('Are you sure you want to suspend this user?')) {
      router.patch(
        route('admin.users.suspend', id),
        {},
        {
          preserveScroll: true,
          onSuccess: () => toast.success('User suspended'),
          onError: () => toast.error('Failed to suspend user'),
        },
      );
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">User Management</h1>
          <p className="text-muted-foreground">Manage all registered users on the platform</p>
        </div>
        <Button onClick={() => setIsAddUserOpen(true)}><UserPlus className="mr-2 h-4 w-4" />Add User</Button>
      </div>

      <Dialog open={isAddUserOpen} onOpenChange={setIsAddUserOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Add New User</DialogTitle>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="space-y-2">
              <Label>Name</Label>
              <Input placeholder="John Doe" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} />
            </div>
            <div className="space-y-2">
              <Label>Email</Label>
              <Input placeholder="john@example.com" type="email" value={formData.email} onChange={e => setFormData({ ...formData, email: e.target.value })} />
            </div>
            <div className="space-y-2">
              <Label>Role</Label>
              <Select value={formData.role} onValueChange={(val) => setFormData({ ...formData, role: val })}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="customer">Customer</SelectItem>
                  <SelectItem value="vendor">Vendor</SelectItem>
                  <SelectItem value="admin">Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsAddUserOpen(false)}>Cancel</Button>
            <Button onClick={handleAddUser}>Add User</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={isEditUserOpen} onOpenChange={setIsEditUserOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit User</DialogTitle>
          </DialogHeader>
          <div className="space-y-4 py-4">
            <div className="space-y-2">
              <Label>Name</Label>
              <Input value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} />
            </div>
            <div className="space-y-2">
              <Label>Email</Label>
              <Input value={formData.email} onChange={e => setFormData({ ...formData, email: e.target.value })} />
            </div>
            <div className="space-y-2">
              <Label>Role</Label>
              <Select value={formData.role} onValueChange={(val) => setFormData({ ...formData, role: val })}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="customer">Customer</SelectItem>
                  <SelectItem value="vendor">Vendor</SelectItem>
                  <SelectItem value="admin">Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsEditUserOpen(false)}>Cancel</Button>
            <Button onClick={handleEditUser}>Save Changes</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>All Users</CardTitle>
              <CardDescription>{initialUsers?.total || users?.length || 0} total users</CardDescription>
            </div>
            <form onSubmit={handleServerSearch} className="flex w-full md:w-auto">
              <Input placeholder="Search users..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button type="submit" size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </form>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border">
            <div className="relative w-full overflow-auto">
              <table className="w-full caption-bottom text-sm">
                <thead className="border-b">
                  <tr>
                    <th className="h-12 px-4 text-left align-middle font-medium">User</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Email</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Role</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Joined</th>
                    <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredUsers.length > 0 ? filteredUsers.map(u => (
                    <tr key={u.id} className="border-b transition-colors hover:bg-muted/50">
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-3">
                          {u.avatar ? (
                            <img src={u.avatar} alt={u.name} className="h-8 w-8 rounded-full object-cover" />
                          ) : (
                            <div className="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-medium">{u.name?.charAt(0)}</div>
                          )}
                          <span className="font-medium">{u.name}</span>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{u.email}</td>
                      <td className="p-4 align-middle">
                        <Badge variant={normalizeRole(u.role) === 'admin' ? 'default' : normalizeRole(u.role) === 'vendor' ? 'secondary' : 'outline'}>
                          {roleLabel(u.role)}
                        </Badge>
                      </td>
                      <td className="p-4 align-middle">{(u.created_at || u.createdAt) ? new Date(u.created_at || u.createdAt).toLocaleDateString() : 'N/A'}</td>
                      <td className="p-4 align-middle text-right">
                        <Button variant="ghost" size="sm" onClick={() => openEditModal(u)}>Edit</Button>
                        <Button variant="ghost" size="sm" className="text-destructive" onClick={() => handleSuspend(u.id)}>Suspend</Button>
                      </td>
                    </tr>
                  )) : (
                    <tr>
                      <td colSpan={5} className="p-8 text-center text-muted-foreground">
                        No users found matching your search.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
          <div className="pt-4">
            <AdminPagination links={initialUsers?.links} />
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

AdminUsers.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminUsers;
