
import { Link } from 'react-router-dom';
import { useIsMobile } from '@/hooks/use-mobile';
import { 
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuList,
  NavigationMenuLink,
  NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { 
  Utensils, Car, Heart, Gift, Plane, Scissors, 
  Smartphone, BookOpen, Coffee, ShoppingBag
} from 'lucide-react';
import { categories } from '@/data/mockData';

const categoryIcons: Record<string, React.ReactNode> = {
  'food-dining': <Utensils className="h-4 w-4" />,
  'beauty-spa': <Scissors className="h-4 w-4" />,
  'travel': <Plane className="h-4 w-4" />,
  'activities-events': <Coffee className="h-4 w-4" />,
  'products': <ShoppingBag className="h-4 w-4" />,
  'services': <Gift className="h-4 w-4" />,
  'auto': <Car className="h-4 w-4" />,
  'electronics': <Smartphone className="h-4 w-4" />,
  'education': <BookOpen className="h-4 w-4" />,
  'health-fitness': <Heart className="h-4 w-4" />
};

const getIconForCategory = (slug: string) => categoryIcons[slug] || <Gift className="h-4 w-4" />;

const groupedCategories = categories.reduce((acc, category) => {
  if (!category.parentId) {
    if (!acc[category.id]) {
      acc[category.id] = { ...category, subcategories: [] };
    } else {
      acc[category.id] = { ...category, subcategories: acc[category.id].subcategories };
    }
  } else {
    if (!acc[category.parentId]) {
      acc[category.parentId] = { subcategories: [category] };
    } else {
      acc[category.parentId].subcategories.push(category);
    }
  }
  return acc;
}, {} as Record<string, any>);

export const MainNavigation = () => {
  const isMobile = useIsMobile();
  const parentCategories = Object.values(groupedCategories).filter((c: any) => c.name);

  return (
    <NavigationMenu className="max-w-none">
      <NavigationMenuList className="flex flex-nowrap gap-0.5 whitespace-nowrap">
        {parentCategories.map((category: any) => (
          <NavigationMenuItem key={category.id}>
            <NavigationMenuTrigger className="flex items-center gap-1.5 text-foreground h-8 px-3 py-1 text-sm bg-transparent hover:bg-primary hover:text-primary-foreground data-[state=open]:bg-primary data-[state=open]:text-primary-foreground rounded-full">
              {getIconForCategory(category.slug)}
              <span>{category.name}</span>
            </NavigationMenuTrigger>
            
            <NavigationMenuContent>
              <div className="grid grid-cols-3 gap-3 p-5 w-[600px] lg:w-[750px] bg-background border border-border rounded-lg shadow-lg">
                <div className="col-span-full pb-3 border-b border-border">
                  <NavigationMenuLink asChild>
                    <Link
                      to={`/search?category=${category.slug}`}
                      className="font-medium text-primary hover:underline flex items-center gap-1.5 text-sm"
                    >
                      {getIconForCategory(category.slug)}
                      All {category.name} Deals
                    </Link>
                  </NavigationMenuLink>
                </div>
                
                {category.subcategories?.map((subcat: any) => (
                  <div key={subcat.id} className="p-1.5">
                    <NavigationMenuLink asChild>
                      <Link
                        to={`/search?category=${subcat.slug}`}
                        className="block font-medium text-sm hover:text-primary mb-1.5 text-foreground"
                      >
                        {subcat.name}
                      </Link>
                    </NavigationMenuLink>
                    
                    {categories.filter(c => c.parentId === subcat.id).map(sub => (
                      <NavigationMenuLink key={sub.id} asChild>
                        <Link
                          to={`/search?category=${sub.slug}`}
                          className="block text-xs text-muted-foreground hover:text-primary py-0.5"
                        >
                          {sub.name}
                        </Link>
                      </NavigationMenuLink>
                    ))}
                  </div>
                ))}
              </div>
            </NavigationMenuContent>
          </NavigationMenuItem>
        ))}
        <NavigationMenuItem>
          <Link
            to="/search"
            className="inline-flex items-center gap-1.5 rounded-full px-3 py-1 h-8 text-sm font-medium text-primary hover:bg-primary hover:text-primary-foreground transition-colors"
          >
            <ShoppingBag className="h-4 w-4" />
            All Deals
          </Link>
        </NavigationMenuItem>
      </NavigationMenuList>
    </NavigationMenu>
  );
};

export default MainNavigation;
