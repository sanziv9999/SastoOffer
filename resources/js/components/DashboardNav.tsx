import { usePage } from '@inertiajs/react';
import { useAuth } from '@/context/AuthContext';
import Link from '@/components/Link';
import {
  LayoutDashboard,
  Heart,
  ShoppingBag,
  Star,
  Settings,
  Store,
  BarChart3,
  Users,
  Tags,
  Shield,
  UserCheck,
  ClipboardList
} from 'lucide-react';
import { SidebarGroup, SidebarGroupContent, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from './ui/sidebar';
import { cn } from '@/lib/utils';

const userLinks = [
  { icon: LayoutDashboard, label: 'Overview', path: '/dashboard' },
  { icon: Heart, label: 'Saved Deals', path: '/dashboard/favorites' },
  { icon: ShoppingBag, label: 'My Purchases', path: '/dashboard/purchases' },
  { icon: Star, label: 'My Reviews', path: '/dashboard/reviews' },
  { icon: Settings, label: 'Settings', path: '/dashboard/settings' },
];

const vendorLinks = [
  { icon: Store, label: 'Overview', path: '/vendor' },
  { icon: Tags, label: 'Manage Deals', path: '/vendor/deals' },
  { icon: ClipboardList, label: 'Orders', path: '/vendor/orders', badge: 3 },
  { icon: BarChart3, label: 'Analytics', path: '/vendor/analytics' },
  { icon: UserCheck, label: 'Customers', path: '/vendor/customers' },
  { icon: Star, label: 'Reviews', path: '/vendor/reviews' },
  { icon: Settings, label: 'Business Settings', path: '/vendor/settings' },
];

const adminLinks = [
  { icon: LayoutDashboard, label: 'Admin Dashboard', path: '/admin' },
  { icon: LayoutDashboard, label: 'Featured Deal Ranking', path: '/admin/featured-ranking' },
  // Unified categories entry (handles both primary and sub-categories)
  { icon: Tags, label: 'Categories', path: '/admin/primary-categories' },
  { icon: Tags, label: 'Offer Types', path: '/admin/offer-types' },
  { icon: ClipboardList, label: 'Banners', path: '/admin/banners' },
  { icon: Users, label: 'Users', path: '/admin/users' },
  { icon: Store, label: 'Vendors', path: '/admin/vendors' },
  { icon: Tags, label: 'Manage Deals', path: '/admin/deals' },
  { icon: BarChart3, label: 'Reports', path: '/admin/reports' },
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

  // Use browser URL; Inertia visits update window.location but not react-router history
  const url = typeof window !== 'undefined' ? window.location.pathname : '/';
  let user = authUser;

  if (isInertia) {
    const page = usePage<any>();
    user = page.props.auth?.user || authUser;
  }

  const isActive = (path: string) => {
    // Standardize URL and path to ignore query strings, hashes, and trailing slashes
    const normalizedUrl = url.split(/[?#]/)[0].replace(/\/$/, "") || "/";
    const normalizedPath = path.replace(/\/$/, "") || "/";

    if (normalizedUrl === normalizedPath) return true;

    // For base routes like /dashboard, /vendor etc, they should only be active on exact match
    // to avoid overlapping with sub-pages like /dashboard/purchases
    const baseRoutes = ['/dashboard', '/vendor', '/admin', '/super-admin'];
    if (baseRoutes.includes(normalizedPath)) {
      return normalizedUrl === normalizedPath;
    }

    return normalizedUrl.startsWith(`${normalizedPath}/`);
  };

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
      {user?.role === 'vendor' && renderLinks(vendorLinks, 'Vendor')}
      {user?.role === 'customer' && renderLinks(userLinks, 'Customer')}
      {user?.role === 'admin' && renderLinks(adminLinks, 'Admin')}
    </>
  );
};

export default DashboardNav;
