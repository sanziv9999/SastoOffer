
import { useState, useEffect } from 'react';
import { MapPin } from 'lucide-react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { cities } from '@/data/mockData';

const CitySelector = () => {
  const [searchParams] = useSearchParams();
  const cityParam = searchParams.get('city');
  const navigate = useNavigate();
  const [selectedCity, setSelectedCity] = useState(cityParam || 'All Cities');

  useEffect(() => {
    if (cityParam) {
      setSelectedCity(cityParam);
    }
  }, [cityParam]);

  const handleCityChange = (city: string) => {
    setSelectedCity(city);
    
    // Update URL with the selected city
    const currentPath = window.location.pathname;
    if (city === 'All Cities') {
      navigate(currentPath);
    } else {
      navigate(`${currentPath}?city=${encodeURIComponent(city)}`);
    }
  };

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="flex items-center gap-1 text-sm">
          <MapPin className="h-4 w-4 text-primary" />
          <span>{selectedCity}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-48 max-h-[300px] overflow-y-auto">
        <DropdownMenuItem onClick={() => handleCityChange('All Cities')}>
          All Cities
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        {cities.map(city => (
          <DropdownMenuItem key={city} onClick={() => handleCityChange(city)}>
            {city}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default CitySelector;
