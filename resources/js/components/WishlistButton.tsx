
import { useState } from 'react';
import Link from '@/components/Link';
import { Heart } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const WishlistButton = () => {
  const [wishlistCount, setWishlistCount] = useState(0);

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="icon" className="relative">
          <Heart className="h-5 w-5" />
          {wishlistCount > 0 && (
            <span className="absolute -top-1 -right-1 bg-primary text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
              {wishlistCount}
            </span>
          )}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56 bg-white z-50">
        <DropdownMenuLabel>Your Wishlist</DropdownMenuLabel>
        <DropdownMenuSeparator />
        {wishlistCount === 0 ? (
          <div className="px-2 py-4 text-center text-sm text-muted-foreground">
            Your wishlist is empty
          </div>
        ) : (
          <DropdownMenuItem asChild>
            <Link href="/wishlist">View Wishlist</Link>
          </DropdownMenuItem>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default WishlistButton;
