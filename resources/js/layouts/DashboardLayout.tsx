
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

interface DashboardLayoutProps {
  children: React.ReactNode;
}

const DashboardLayout = ({ children }: DashboardLayoutProps) => {
  const { user, logout } = useAuth();

  const handleLogout = () => {
    logout();
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full overflow-x-hidden">
        <Sidebar>
          <SidebarHeader>
            <div className="p-4">
              <a href="/" className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2">
                <Home className="h-4 w-4" />
                <span>Back to Site</span>
              </a>
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
              <p className="text-xs text-muted-foreground">© {new Date().getFullYear()} Offer Oasis</p>
            </div>
          </SidebarFooter>
        </Sidebar>
        <div className="flex-1 min-w-0 p-3 sm:p-4 lg:p-8">
          <div className="flex justify-end mb-3 sm:mb-4">
            <SidebarTrigger className="h-9 w-9" />
          </div>
          {children || <Outlet />}
        </div>
      </div>
    </SidebarProvider>
  );
};

export default DashboardLayout;
