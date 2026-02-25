import { usePage } from '@inertiajs/react';
import { useAuth } from '@/context/AuthContext';
import Link from '@/components/Link';
import {
  LayoutDashboard,
  Heart,
  ShoppingBag,
  Star,
  Bell,
  Settings,
  Store,
  PlusCircle,
  BarChart3,
  Users,
  Tags,
  Shield,
  UserCheck,
  History,
  TrendingUp,
  ClipboardList
} from 'lucide-react';
import { SidebarGroup, SidebarGroupContent, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from './ui/sidebar';
import { cn } from '@/lib/utils';

const userLinks = [
  { icon: LayoutDashboard, label: 'Overview', path: '/dashboard' },
  { icon: Heart, label: 'Saved Deals', path: '/dashboard/favorites' },
  { icon: ShoppingBag, label: 'My Purchases', path: '/dashboard/purchases' },
  { icon: Star, label: 'Reviews', path: '/dashboard/reviews' },
  { icon: Bell, label: 'Notifications', path: '/dashboard/notifications' },
  { icon: Settings, label: 'Settings', path: '/dashboard/settings' },
];

const vendorLinks = [
  { icon: Store, label: 'Vendor Overview', path: '/vendor' },
  { icon: PlusCircle, label: 'Create Deal', path: '/vendor/create-deal', highlight: true },
  { icon: Tags, label: 'Manage Deals', path: '/vendor/deals' },
  { icon: ClipboardList, label: 'Orders', path: '/vendor/orders', badge: 3 },
  { icon: BarChart3, label: 'Analytics', path: '/vendor/analytics' },
  { icon: UserCheck, label: 'Customers', path: '/vendor/customers' },
  { icon: History, label: 'Customer History', path: '/vendor/customer-history' },
  { icon: TrendingUp, label: 'Sales History', path: '/vendor/sales-history' },
];

const adminLinks = [
  { icon: LayoutDashboard, label: 'Admin Dashboard', path: '/admin' },
  { icon: Users, label: 'Users', path: '/admin/users' },
  { icon: Store, label: 'Vendors', path: '/admin/vendors' },
  { icon: Tags, label: 'Manage Deals', path: '/admin/deals' },
  { icon: BarChart3, label: 'Reports', path: '/admin/reports' },
];

const superAdminLinks = [
  { icon: Shield, label: 'Super Admin', path: '/super-admin' },
  { icon: Users, label: 'Admins', path: '/super-admin/admins' },
  { icon: BarChart3, label: 'System Analytics', path: '/super-admin/analytics' },
];

const DashboardNav = () => {
  const { user: authUser } = useAuth();

  // Detect if we are in an Inertia environment
  let isInertia = false;
  try {
    usePage();
    isInertia = true;
  } catch (e) {
    isInertia = false;
  }

  // Try to get Inertia data, otherwise use fallback
  let url = '';
  let user = authUser;

  if (isInertia) {
    const page = usePage<any>();
    url = page.url;
    user = page.props.auth?.user || authUser;
  } else {
    // Fallback for non-Inertia environments
    url = typeof window !== 'undefined' ? window.location.pathname : '';
  }

  const isActive = (path: string) => url === path || url.startsWith(`${path}/`);

  const renderLinks = (links: Array<{ icon: any; label: string; path: string; highlight?: boolean; badge?: number }>, label: string) => (
    <SidebarGroup>
      <SidebarGroupLabel>{label}</SidebarGroupLabel>
      <SidebarGroupContent>
        <SidebarMenu>
          {links.map((link) => {
            const active = isActive(link.path);
            return (
              <SidebarMenuItem key={link.path}>
                <SidebarMenuButton asChild className={cn(
                  active && "bg-accent text-accent-foreground",
                  link.highlight && !active && "bg-primary/10 text-primary font-semibold hover:bg-primary/20 border border-primary/20"
                )}>
                  <Link href={link.path} className="flex items-center justify-between w-full">
                    <span className="flex items-center">
                      <link.icon className={cn("h-4 w-4 mr-2", link.highlight && !active && "text-primary")} />
                      <span>{link.label}</span>
                    </span>
                    {link.badge && link.badge > 0 && (
                      <span className="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-destructive-foreground text-[10px] font-bold">{link.badge}</span>
                    )}
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            );
          })}
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  );

  return (
    <>
      {/* Vendor section on top */}
      {['vendor', 'admin', 'super_admin'].includes(user?.role || '') && renderLinks(vendorLinks, 'Vendor')}
      {/* User section below */}
      {renderLinks(userLinks, 'User')}
      {['admin', 'super_admin'].includes(user?.role || '') && renderLinks(adminLinks, 'Admin')}
      {user?.role === 'super_admin' && renderLinks(superAdminLinks, 'Super Admin')}
    </>
  );
};

export default DashboardNav;
