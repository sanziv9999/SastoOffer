
import { Outlet, Link } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
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

const DashboardLayout = () => {
  const { user, logout } = useAuth();

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full">
        <Sidebar>
          <SidebarHeader>
            <div className="p-4">
              <Link to="/" className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-2">
                <Home className="h-4 w-4" />
                <span>Back to Site</span>
              </Link>
              <h2 className="font-semibold text-xl">Dashboard</h2>
              <p className="text-sm text-muted-foreground">{user?.name}</p>
            </div>
          </SidebarHeader>
          <SidebarContent>
            <DashboardNav />
          </SidebarContent>
          <SidebarFooter>
            <div className="p-4 space-y-2">
              <Button variant="ghost" size="sm" className="w-full justify-start" onClick={logout}>
                <LogOut className="h-4 w-4 mr-2" />
                Logout
              </Button>
              <p className="text-xs text-muted-foreground">© 2024 Offer Oasis</p>
            </div>
          </SidebarFooter>
        </Sidebar>
        <div className="flex-1 p-6">
          <div className="flex justify-end mb-4">
            <SidebarTrigger />
          </div>
          <Outlet />
        </div>
      </div>
    </SidebarProvider>
  );
};

export default DashboardLayout;
