import { usePage } from '@inertiajs/react';
import { useAuth } from '@/context/AuthContext';
import Link from '@/components/Link';
import {
  LayoutDashboard,
  Heart,
  ShoppingCart,
  ShoppingBag,
  Star,
  Settings,
  Store,
  BarChart3,
  Users,
  Tags,
  Shield,
  UserCheck,
  ClipboardList,
  Lock
} from 'lucide-react';
import { SidebarGroup, SidebarGroupContent, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from './ui/sidebar';
import { cn } from '@/lib/utils';

const userLinks = [
  { icon: LayoutDashboard, label: 'Overview', path: '/dashboard' },
  { icon: ShoppingCart, label: 'Cart', path: '/dashboard/cart' },
  { icon: Heart, label: 'Saved Deals', path: '/dashboard/favorites' },
  { icon: ShoppingBag, label: 'My Claimed Offers', path: '/dashboard/purchases' },
  { icon: Star, label: 'My Reviews', path: '/dashboard/reviews' },
  { icon: Settings, label: 'Settings', path: '/dashboard/settings' },
];

const adminLinks = [
  { icon: LayoutDashboard, label: 'Admin Dashboard', path: '/admin' },
  { icon: LayoutDashboard, label: 'Featured Deal Ranking', path: '/admin/featured-ranking' },
  // Unified categories entry (handles both primary and sub-categories)
  { icon: Tags, label: 'Categories', path: '/admin/primary-categories' },
  { icon: Tags, label: 'Offer Types', path: '/admin/offer-types' },
  { icon: Tags, label: 'Display Types', path: '/admin/display-types' },
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
  let vendorAccess: any = null;
  let vendorMetrics: any = null;

  if (isInertia) {
    const page = usePage<any>();
    user = page.props.auth?.user || authUser;
    vendorAccess = page.props.auth?.vendor_access ?? null;
    vendorMetrics = page.props.auth?.vendor_metrics ?? null;
  }
  const vendorMenusLocked = user?.role === 'vendor' && !(vendorAccess?.is_unlocked ?? false);
  const vendorLinks = [
    { icon: Store, label: 'Overview', path: '/vendor' },
    { icon: Tags, label: 'Manage Deals', path: '/vendor/deals' },
    { icon: ClipboardList, label: 'Orders', path: '/vendor/orders', badge: Number(vendorMetrics?.open_orders ?? 0) },
    { icon: BarChart3, label: 'Analytics', path: '/vendor/analytics' },
    { icon: UserCheck, label: 'Customers', path: '/vendor/customers' },
    { icon: Star, label: 'Reviews', path: '/vendor/reviews' },
    { icon: Settings, label: 'Business Settings', path: '/vendor/settings' },
  ];

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
            const isDisabled = label === 'Vendor' && vendorMenusLocked && link.path !== '/vendor/settings';

            if (isDisabled) {
              return (
                <SidebarMenuItem key={link.path}>
                  <SidebarMenuButton
                    className="cursor-not-allowed opacity-55"
                    title="Complete business details and wait for admin verification."
                  >
                    <span className="flex items-center justify-between w-full">
                      <span className="flex items-center">
                        <link.icon className="h-4 w-4 mr-2" />
                        <span>{link.label}</span>
                      </span>
                      <Lock className="h-3.5 w-3.5" />
                    </span>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              );
            }

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
      {user?.role === 'vendor' && vendorMenusLocked && (
        <SidebarGroup>
          <SidebarGroupContent>
            <div className="mx-2 mb-2 rounded-md border border-amber-300/40 bg-amber-50 px-3 py-2 text-xs text-amber-800">
              Complete business details in Business Settings and wait for admin verification to unlock vendor menus.
            </div>
          </SidebarGroupContent>
        </SidebarGroup>
      )}
      {user?.role === 'vendor' && renderLinks(vendorLinks, 'Vendor')}
      {user?.role === 'customer' && renderLinks(userLinks, 'Customer')}
      {user?.role === 'admin' && renderLinks(adminLinks, 'Admin')}
    </>
  );
};

export default DashboardNav;
