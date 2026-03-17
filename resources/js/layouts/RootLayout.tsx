
import { Outlet } from 'react-router-dom';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import MobileNavigation from '@/components/MobileNavigation';

const RootLayout = ({ children }: { children?: React.ReactNode }) => {
  return (
    <div className="min-h-screen flex flex-col bg-muted/30">
      <div className="fixed top-0 left-0 right-0 z-50">
        <Navbar />
      </div>
      {/* Fixed spacer for navbar height */}
      <div className="h-28 md:h-32" />
      <main className="flex-grow">
        {children || <Outlet />}
      </main>
      <Footer />
      <MobileNavigation />
    </div>
  );
};

export default RootLayout;
