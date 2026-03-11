
import { Outlet } from 'react-router-dom';
import { useEffect, useState, useRef } from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import MobileNavigation from '@/components/MobileNavigation';

const RootLayout = ({ children }: { children?: React.ReactNode }) => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [showFullHeader, setShowFullHeader] = useState(true);
  const lastScrollY = useRef(0);

  useEffect(() => {
    const handleScroll = () => {
      const currentScrollY = window.scrollY;
      
      setIsScrolled(currentScrollY > 40);
      
      if (currentScrollY > 80) {
        if (currentScrollY - lastScrollY.current > 8) {
          setShowFullHeader(false);
        } else if (lastScrollY.current - currentScrollY > 5) {
          setShowFullHeader(true);
        }
      } else {
        setShowFullHeader(true);
      }
      
      lastScrollY.current = currentScrollY;
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <div className="min-h-screen flex flex-col bg-muted/30">
      <div className="fixed top-0 left-0 right-0 z-50">
        <Navbar headerIsScrolled={isScrolled} showFullHeader={showFullHeader} />
      </div>
      {/* Dynamic spacer */}
      <div className={`transition-all duration-300 ${isScrolled && !showFullHeader ? 'h-14 md:h-14' : 'h-28 md:h-32'}`} />
      <main className="flex-grow">
        {children || <Outlet />}
      </main>
      <Footer />
      <MobileNavigation />
    </div>
  );
};

export default RootLayout;
