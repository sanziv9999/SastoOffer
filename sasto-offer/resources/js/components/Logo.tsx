
import { Tag, Percent } from 'lucide-react';

const Logo = () => {
  return (
    <div className="flex items-center gap-1">
      <div className="relative">
        <Tag className="h-7 w-7 text-primary" />
        <Percent className="h-4 w-4 absolute bottom-0 right-0 text-secondary" />
      </div>
      <span className="text-2xl font-bold text-primary">Offer Oasis</span>
    </div>
  );
};

export default Logo;
