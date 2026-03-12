import { useState, useRef } from 'react';
import { router } from '@inertiajs/react';
import Link from '@/components/Link';
import { useAuth } from '@/context/AuthContext';
import { 
  Search, 
  Menu, 
  X, 
  User, 
  LogOut, 
  LayoutDashboard,
  MapPin,
  ChevronRight,
  ChevronLeft
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import MainNavigation from './MainNavigation';
import { cities } from '@/data/mockData';
import Logo from './Logo';
import CartButton from './CartButton';
import WishlistButton from './WishlistButton';
import { Input } from '@/components/ui/input';

interface NavbarProps {
  headerIsScrolled: boolean;
  showFullHeader?: boolean;
}

function getSearchParamsFromUrl(): URLSearchParams {
  if (typeof window === 'undefined') return new URLSearchParams();
  return new URLSearchParams(window.location.search);
}

const Navbar = ({ headerIsScrolled, showFullHeader = true }: NavbarProps) => {
  const { user, logout } = useAuth();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState(() => getSearchParamsFromUrl().get('city') || 'All Cities');
  
  const menuScrollRef = useRef<HTMLDivElement>(null);

  const handleSearch = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    const cityParam = selectedCity && selectedCity !== 'All Cities' ? `&city=${encodeURIComponent(selectedCity)}` : '';
    if (searchQuery.trim()) {
      router.visit(`/search?q=${encodeURIComponent(searchQuery)}${cityParam}`);
    } else if (selectedCity && selectedCity !== 'All Cities') {
      router.visit(`/search?city=${encodeURIComponent(selectedCity)}`);
    }
    setMobileMenuOpen(false);
  };

  const toggleMobileMenu = () => setMobileMenuOpen(!mobileMenuOpen);

  const getDashboardLink = () => {
    if (user?.role === 'admin') return '/admin';
    if (user?.role === 'vendor') return '/vendor';
    return '/dashboard';
  };

  const scrollMenu = (direction: 'left' | 'right') => {
    if (menuScrollRef.current) {
      const scrollAmount = 200;
      menuScrollRef.current.scrollLeft += direction === 'left' ? -scrollAmount : scrollAmount;
    }
  };

  const compactMode = headerIsScrolled && !showFullHeader;

  return (
    <header className="bg-background shadow-sm transition-all duration-300">
      <div className="container mx-auto px-4">
        {/* Compact search-only bar when scrolled down */}
        {compactMode ? (
          <div className="flex items-center gap-3 py-2">
            <Link href="/" className="flex-shrink-0">
              <Logo />
            </Link>
            <form onSubmit={handleSearch} className="flex flex-1 max-w-2xl bg-muted rounded-lg border border-border focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
              <div className="relative flex-grow">
                <Input
                  type="search"
                  placeholder="Search deals..."
                  className="w-full pl-10 border-0 shadow-none bg-transparent rounded-lg h-9 md:h-10"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                />
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
              </div>
              <Button type="submit" size="icon" className="rounded-lg h-9 w-9 md:h-10 md:w-10 bg-primary hover:bg-primary/90">
                <Search className="h-4 w-4" />
              </Button>
            </form>
            <div className="flex items-center gap-1">
              {user ? (
<Button variant="ghost" size="icon" className="rounded-full" asChild>
                  <Link href={getDashboardLink()}>
                    <User className="h-5 w-5" />
                  </Link>
                </Button>
              ) : (
                <Button asChild size="sm" className="rounded-full px-4">
                  <Link href="/login">Sign In</Link>
                </Button>
              )}
            </div>
          </div>
        ) : (
          <>
            {/* Full header */}
            <div className="flex items-center justify-between py-3">
              <Link href="/" className="flex-shrink-0">
                <Logo />
              </Link>

              {/* Desktop search bar */}
              <div className="hidden md:flex flex-1 max-w-xl mx-6">
                <form onSubmit={handleSearch} className="flex w-full bg-muted rounded-lg border border-border focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                  <div className="relative flex-grow">
                    <Input
                      type="search"
                      placeholder="Search deals..."
                      className="w-full pl-10 border-0 shadow-none bg-transparent rounded-lg h-10"
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                    />
                    <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  </div>
                  <Button type="submit" size="icon" className="rounded-lg h-10 w-10 bg-primary hover:bg-primary/90">
                    <Search className="h-4 w-4" />
                  </Button>
                </form>
              </div>

              {/* Right side actions */}
              <div className="flex items-center gap-2">
                <div className="hidden md:flex items-center">
                  <Select value={selectedCity} onValueChange={setSelectedCity}>
                    <SelectTrigger className="h-9 border-0 bg-muted/50 rounded-full px-3 text-sm gap-1 w-auto min-w-[120px]">
                      <MapPin className="h-3.5 w-3.5 text-primary flex-shrink-0" />
                      <SelectValue placeholder="City" />
                    </SelectTrigger>
                    <SelectContent className="max-h-[300px] bg-background z-50">
                      <SelectItem value="All Cities">All Cities</SelectItem>
                      {cities.map(city => (
                        <SelectItem key={city} value={city}>{city}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="hidden md:flex items-center gap-1">
                  <WishlistButton />
                  <CartButton />
                </div>
                
                {user ? (
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="ghost" size="icon" className="rounded-full">
                        <User className="h-5 w-5" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56 bg-background z-50">
                      <DropdownMenuLabel>My Account</DropdownMenuLabel>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem asChild>
                        <Link href={getDashboardLink()}>
                          <LayoutDashboard className="mr-2 h-4 w-4" />
                          <span>Dashboard</span>
                        </Link>
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem onClick={logout}>
                        <LogOut className="mr-2 h-4 w-4" />
                        <span>Log out</span>
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                ) : (
                  <Button asChild size="sm" className="rounded-full px-5">
                    <Link href="/login">Sign In</Link>
                  </Button>
                )}

                <div className="md:hidden flex items-center gap-1">
                  <WishlistButton />
                  <CartButton />
                </div>

                <Button variant="ghost" size="icon" className="md:hidden" onClick={toggleMobileMenu}>
                  {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                </Button>
              </div>
            </div>

            {/* Mobile search */}
            <div className="md:hidden pb-3">
              <form onSubmit={handleSearch} className="flex bg-muted rounded-lg border border-border overflow-hidden focus-within:border-primary">
                <div className="relative flex-grow flex items-center">
                  <Search className="absolute left-3 h-4 w-4 text-muted-foreground" />
                  <Input
                    type="search"
                    placeholder="Search deals..."
                    className="w-full pl-10 border-0 shadow-none bg-transparent h-9 rounded-lg"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                  />
                </div>
                <Button type="submit" size="icon" className="rounded-lg h-9 w-9 bg-primary hover:bg-primary/90">
                  <Search className="h-4 w-4" />
                </Button>
              </form>
            </div>
            
            {/* Category navigation - all devices */}
            <div className="relative group overflow-hidden transition-all duration-300 max-h-20 opacity-100">
              <div ref={menuScrollRef} className="overflow-x-auto scrollbar-none py-2 scroll-smooth" style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
                <MainNavigation />
              </div>
              <div className="absolute left-0 top-1/2 transform -translate-y-1/2 z-10">
                <Button variant="outline" size="icon" className="rounded-full shadow-md bg-background/90 h-7 w-7 p-0 opacity-0 group-hover:opacity-100 transition-opacity" onClick={() => scrollMenu('left')}>
                  <ChevronLeft className="h-4 w-4" />
                </Button>
              </div>
              <div className="absolute right-0 top-1/2 transform -translate-y-1/2 z-10">
                <Button variant="outline" size="icon" className="rounded-full shadow-md bg-background/90 h-7 w-7 p-0 opacity-0 group-hover:opacity-100 transition-opacity" onClick={() => scrollMenu('right')}>
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            </div>

            {/* Mobile menu */}
            {mobileMenuOpen && (
              <div className="md:hidden py-4 border-t border-border animate-in slide-in-from-top-2 duration-200">
                <div className="flex items-center gap-2 mb-4">
                  <MapPin className="h-4 w-4 text-primary" />
                  <Select value={selectedCity} onValueChange={setSelectedCity}>
                    <SelectTrigger className="flex-1 h-9 rounded-full bg-muted border-0">
                      <SelectValue placeholder="Select city" />
                    </SelectTrigger>
                    <SelectContent className="max-h-[300px] bg-background z-50">
                      <SelectItem value="All Cities">All Cities</SelectItem>
                      {cities.map(city => (
                        <SelectItem key={city} value={city}>{city}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                
                {!user ? (
                  <div className="flex flex-col gap-2 pt-3 border-t border-border">
                    <Button asChild className="w-full rounded-full" onClick={toggleMobileMenu}>
                      <Link href="/login">Sign In</Link>
                    </Button>
                    <Button asChild variant="outline" className="w-full rounded-full" onClick={toggleMobileMenu}>
                      <Link href="/register">Sign Up</Link>
                    </Button>
                  </div>
                ) : (
                  <div className="flex flex-col gap-3 pt-3 border-t border-border">
                    <Link href={getDashboardLink()} className="flex items-center gap-2 text-foreground hover:bg-primary hover:text-primary-foreground px-3 py-2 rounded-lg transition-colors" onClick={toggleMobileMenu}>
                      <LayoutDashboard className="h-4 w-4" />
                      <span>Dashboard</span>
                    </Link>
                    <button className="flex items-center gap-2 text-destructive hover:text-destructive/80 text-left py-2" onClick={() => { logout(); toggleMobileMenu(); }}>
                      <LogOut className="h-4 w-4" />
                      <span>Log out</span>
                    </button>
                  </div>
                )}
              </div>
            )}
          </>
        )}
      </div>
    </header>
  );
};

export default Navbar;
