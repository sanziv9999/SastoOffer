
import { Link } from 'react-router-dom';
import { Facebook, Twitter, Instagram, Linkedin } from 'lucide-react';
import Logo from './Logo';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useState, useEffect } from 'react';

const Footer = () => {
  const [openSections, setOpenSections] = useState<{ [key: string]: boolean }>({});

  const toggleSection = (section: string) => {
    setOpenSections(prev => ({
      ...prev,
      [section]: !prev[section]
    }));
  };

  return (
    <footer className="bg-slate-100 text-gray-700 pb-16 md:pb-0">
      <div className="container mx-auto px-6 py-12">
        {/* Main Footer Content */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* Logo and Social Media Section */}
          <div>
            <div className="mb-4">
              <Logo />
            </div>
            <p className="text-gray-600 mb-4">
              Find the best deals and offers from your favorite businesses.
            </p>
            <div className="flex space-x-4">
              <a href="#" className="text-gray-500 hover:text-primary transition-colors">
                <Facebook className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-500 hover:text-primary transition-colors">
                <Twitter className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-500 hover:text-primary transition-colors">
                <Instagram className="h-5 w-5" />
              </a>
              <a href="#" className="text-gray-500 hover:text-primary transition-colors">
                <Linkedin className="h-5 w-5" />
              </a>
            </div>
          </div>

          {/* Quick Links Section - Desktop */}
          <div className="hidden md:block">
            <h3 className="text-lg font-semibold mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/" className="text-gray-600 hover:text-primary transition-colors">
                  Home
                </Link>
              </li>
              <li>
                <Link to="/search" className="text-gray-600 hover:text-primary transition-colors">
                  Search Deals
                </Link>
              </li>
              <li>
                <Link to="/search?featured=true" className="text-gray-600 hover:text-primary transition-colors">
                  Featured Deals
                </Link>
              </li>
              <li>
                <Link to="/search?new=true" className="text-gray-600 hover:text-primary transition-colors">
                  New Arrivals
                </Link>
              </li>
            </ul>
          </div>

          {/* Categories Section - Desktop */}
          <div className="hidden md:block">
            <h3 className="text-lg font-semibold mb-4">Categories</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/search?category=food-dining" className="text-gray-600 hover:text-primary transition-colors">
                  Restaurants
                </Link>
              </li>
              <li>
                <Link to="/search?category=beauty-spa" className="text-gray-600 hover:text-primary transition-colors">
                  Beauty & Spa
                </Link>
              </li>
              <li>
                <Link to="/search?category=activities-events" className="text-gray-600 hover:text-primary transition-colors">
                  Activities
                </Link>
              </li>
              <li>
                <Link to="/search?category=travel" className="text-gray-600 hover:text-primary transition-colors">
                  Travel
                </Link>
              </li>
              <li>
                <Link to="/search?category=electronics" className="text-gray-600 hover:text-primary transition-colors">
                  Electronics
                </Link>
              </li>
            </ul>
          </div>

          {/* Support Section - Desktop */}
          <div className="hidden md:block">
            <h3 className="text-lg font-semibold mb-4">Support</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/help" className="text-gray-600 hover:text-primary transition-colors">
                  Help Center
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-gray-600 hover:text-primary transition-colors">
                  Contact Us
                </Link>
              </li>
              <li>
                <Link to="/privacy" className="text-gray-600 hover:text-primary transition-colors">
                  Privacy Policy
                </Link>
              </li>
              <li>
                <Link to="/terms" className="text-gray-600 hover:text-primary transition-colors">
                  Terms of Service
                </Link>
              </li>
            </ul>
          </div>

          {/* Mobile Footer Accordion */}
          <div className="md:hidden space-y-4 col-span-1">
            {/* Quick Links Section - Mobile */}
            <Collapsible
              open={openSections['quickLinks']}
              onOpenChange={() => toggleSection('quickLinks')}
              className="border-b pb-2"
            >
              <CollapsibleTrigger className="flex w-full justify-between items-center py-2 group">
                <h3 className="text-lg font-semibold">Quick Links</h3>
                <div className="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                  <span className={`transform transition-transform ${openSections['quickLinks'] ? 'rotate-180' : ''}`}>↓</span>
                </div>
              </CollapsibleTrigger>
              <CollapsibleContent>
                <ul className="space-y-2 py-2 pl-2">
                  <li>
                    <Link to="/" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Home
                    </Link>
                  </li>
                  <li>
                    <Link to="/search" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Search Deals
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?featured=true" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Featured Deals
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?new=true" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      New Arrivals
                    </Link>
                  </li>
                </ul>
              </CollapsibleContent>
            </Collapsible>

            {/* Categories Section - Mobile */}
            <Collapsible
              open={openSections['categories']}
              onOpenChange={() => toggleSection('categories')}
              className="border-b pb-2"
            >
              <CollapsibleTrigger className="flex w-full justify-between items-center py-2 group">
                <h3 className="text-lg font-semibold">Categories</h3>
                <div className="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                  <span className={`transform transition-transform ${openSections['categories'] ? 'rotate-180' : ''}`}>↓</span>
                </div>
              </CollapsibleTrigger>
              <CollapsibleContent>
                <ul className="space-y-2 py-2 pl-2">
                  <li>
                    <Link to="/search?category=food-dining" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Restaurants
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?category=beauty-spa" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Beauty & Spa
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?category=activities-events" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Activities
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?category=travel" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Travel
                    </Link>
                  </li>
                  <li>
                    <Link to="/search?category=electronics" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Electronics
                    </Link>
                  </li>
                </ul>
              </CollapsibleContent>
            </Collapsible>

            {/* Support Section - Mobile */}
            <Collapsible
              open={openSections['support']}
              onOpenChange={() => toggleSection('support')}
              className="border-b pb-2"
            >
              <CollapsibleTrigger className="flex w-full justify-between items-center py-2 group">
                <h3 className="text-lg font-semibold">Support</h3>
                <div className="p-0 h-6 w-6 flex items-center justify-center text-muted-foreground group-hover:text-primary transition-colors">
                  <span className={`transform transition-transform ${openSections['support'] ? 'rotate-180' : ''}`}>↓</span>
                </div>
              </CollapsibleTrigger>
              <CollapsibleContent>
                <ul className="space-y-2 py-2 pl-2">
                  <li>
                    <Link to="/help" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Help Center
                    </Link>
                  </li>
                  <li>
                    <Link to="/contact" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Contact Us
                    </Link>
                  </li>
                  <li>
                    <Link to="/privacy" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Privacy Policy
                    </Link>
                  </li>
                  <li>
                    <Link to="/terms" className="text-gray-600 hover:text-primary transition-colors block py-1">
                      Terms of Service
                    </Link>
                  </li>
                </ul>
              </CollapsibleContent>
            </Collapsible>
          </div>
        </div>

        {/* Copyright Section */}
        <div className="border-t border-gray-200 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center">
          <p className="text-gray-500 text-sm">
            &copy; {new Date().getFullYear()} Offer Oasis. All rights reserved.
          </p>
          <div className="mt-4 md:mt-0">
            <ul className="flex space-x-6">
              <li>
                <Link to="/privacy" className="text-gray-500 hover:text-primary text-sm transition-colors">
                  Privacy
                </Link>
              </li>
              <li>
                <Link to="/terms" className="text-gray-500 hover:text-primary text-sm transition-colors">
                  Terms
                </Link>
              </li>
              <li>
                <Link to="/sitemap" className="text-gray-500 hover:text-primary text-sm transition-colors">
                  Sitemap
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {/* We've removed the duplicate mobile menu since we're using MobileNavigation */}
    </footer>
  );
};

export default Footer;
