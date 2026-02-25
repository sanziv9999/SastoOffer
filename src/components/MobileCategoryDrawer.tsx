
import { useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { X, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { categories } from '@/data/mockData';

interface MobileCategoryDrawerProps {
  isOpen: boolean;
  onClose: () => void;
}

const MobileCategoryDrawer = ({ isOpen, onClose }: MobileCategoryDrawerProps) => {
  const drawerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Prevent scrolling when drawer is open
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    
    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  useEffect(() => {
    // Close on escape key
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    
    window.addEventListener('keydown', handleEscape);
    return () => window.removeEventListener('keydown', handleEscape);
  }, [onClose]);

  // Handle clicks outside
  const handleBackdropClick = (e: React.MouseEvent) => {
    if (drawerRef.current && !drawerRef.current.contains(e.target as Node)) {
      onClose();
    }
  };

  // Group categories by parent
  const parentCategories = categories.filter(c => !c.parentId);
  
  return (
    <div 
      className={`fixed inset-0 z-[60] bg-black/30 transition-opacity ${isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
      onClick={handleBackdropClick}
    >
      <div 
        ref={drawerRef}
        className={`fixed inset-y-0 left-0 max-w-[280px] w-[80vw] bg-white shadow-xl z-[60] transition-transform duration-300 ease-in-out ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex items-center justify-between p-4 border-b">
          <h2 className="font-semibold text-lg">Categories</h2>
          <Button variant="ghost" size="icon" onClick={onClose}>
            <X className="h-5 w-5" />
          </Button>
        </div>
        
        <div className="overflow-y-auto h-[calc(100%-60px)]">
          {parentCategories.map(category => (
            <div key={category.id} className="border-b">
              <Link 
                to={`/search?category=${category.slug}`}
                className="flex items-center justify-between px-4 py-3 hover:bg-gray-50"
                onClick={onClose}
              >
                <span className="font-medium">{category.name}</span>
                <ChevronRight className="h-4 w-4 text-gray-400" />
              </Link>

              {/* Subcategories */}
              <div className="pl-4 pb-2">
                {categories
                  .filter(c => c.parentId === category.id)
                  .map(subcat => (
                    <Link 
                      key={subcat.id}
                      to={`/search?category=${subcat.slug}`}
                      className="flex items-center py-2 px-3 text-sm text-gray-600 hover:text-primary"
                      onClick={onClose}
                    >
                      {subcat.name}
                    </Link>
                  ))
                }
              </div>
            </div>
          ))}
          
          <div className="p-4">
            <Link 
              to="/search"
              className="block w-full py-2 px-4 bg-primary text-white text-center rounded-md"
              onClick={onClose}
            >
              View All Deals
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MobileCategoryDrawer;
