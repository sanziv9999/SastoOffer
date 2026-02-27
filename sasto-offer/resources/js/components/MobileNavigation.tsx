
import { Link } from 'react-router-dom';
import { Heart, ShoppingBag, User, Grid2X2, ChevronRight } from 'lucide-react';
import { useAuth } from '@/context/AuthContext';
import { useState } from 'react';
import MobileCategoryDrawer from './MobileCategoryDrawer';

const MobileNavigation = () => {
  const { user } = useAuth();
  const [showCategories, setShowCategories] = useState(false);
  
  return (
    <>
      <MobileCategoryDrawer isOpen={showCategories} onClose={() => setShowCategories(false)} />
      
      <div className="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t z-50 md:hidden">
        <div className="flex justify-around">
          <button 
            onClick={() => setShowCategories(true)}
            className="flex flex-col items-center py-2 px-4 relative focus:outline-none"
          >
            <Grid2X2 className="h-5 w-5 text-gray-600" />
            <span className="text-xs mt-1 text-gray-600">Categories</span>
          </button>
          
          <Link to="/dashboard/favorites" className="flex flex-col items-center py-2 px-4 active:bg-gray-100 active:text-primary">
            <Heart className="h-5 w-5 text-gray-600" />
            <span className="text-xs mt-1 text-gray-600">Wishlist</span>
          </Link>
          
          <Link to="/cart" className="flex flex-col items-center py-2 px-4 active:bg-gray-100 active:text-primary">
            <ShoppingBag className="h-5 w-5 text-gray-600" />
            <span className="text-xs mt-1 text-gray-600">Cart</span>
          </Link>
          
          <Link 
            to={user ? "/dashboard" : "/login"} 
            className="flex flex-col items-center py-2 px-4 active:bg-gray-100 active:text-primary"
          >
            <User className="h-5 w-5 text-gray-600" />
            <span className="text-xs mt-1 text-gray-600">{user ? 'Account' : 'Sign In'}</span>
          </Link>
        </div>
      </div>
    </>
  );
};

export default MobileNavigation;
