import { usePage } from '@inertiajs/react';
import { Outlet } from 'react-router-dom';
import { LogOut, Home } from 'lucide-react';
import {
  SidebarProvider,
  Sidebar,
  SidebarHeader,
  SidebarContent,
  SidebarFooter,
  SidebarTrigger
} from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import DashboardNav from '@/components/DashboardNav';
import { useAuth } from '@/context/AuthContext';
import Link from '@/components/Link';

interface DashboardLayoutProps {
  children: React.ReactNode;
}

const DashboardLayout = ({ children }: DashboardLayoutProps) => {
  const { user, logout } = useAuth();

  // Detect environment
  let isInertia = false;
  try {
    usePage();
    isInertia = true;
  } catch (e) {
    isInertia = false;
  }

  const handleLogout = () => {
    if (isInertia) {
      // router.post('/logout');
      console.log("Inertia logout");
    } else {
      logout();
    }
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full">
        <Sidebar>
          <SidebarHeader>
            <div className="p-4">
              <Link href="/" className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2">
                <Home className="h-4 w-4" />
                <span>Back to Site</span>
              </Link>
              <h2 className="font-semibold text-xl">Dashboard</h2>
              <p className="text-sm text-muted-foreground">{user?.name || 'Guest'}</p>
            </div>
          </SidebarHeader>
          <SidebarContent>
            <DashboardNav />
          </SidebarContent>
          <SidebarFooter>
            <div className="p-4 space-y-2">
              <Button variant="ghost" size="sm" className="w-full justify-start" onClick={handleLogout}>
                <LogOut className="h-4 w-4 mr-2" />
                Logout
              </Button>
              <p className="text-xs text-muted-foreground">© 2024 Offer Oasis</p>
            </div>
          </SidebarFooter>
        </Sidebar>
        <div className="flex-1 p-6 lg:p-10">
          <div className="flex justify-end mb-4">
            <SidebarTrigger />
          </div>
          {children || <Outlet />}
        </div>
      </div>
    </SidebarProvider>
  );
};

export default DashboardLayout;
