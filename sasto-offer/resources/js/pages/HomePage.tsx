
import { useMemo, useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import SlidingBanner from '@/components/SlidingBanner';
import FeaturedProducts from '@/components/FeaturedProducts';
import RecentOffers from '@/components/RecentOffers';
import BrandLogos from '@/components/BrandLogos';
import MultiAdsBanner from '@/components/MultiAdsBanner';
import { deals, locations } from '@/data/mockData';

const HomePage = () => {
  const [searchParams] = useSearchParams();
  const cityParam = searchParams.get('city');
  const [selectedCity, setSelectedCity] = useState(cityParam || 'All Cities');

  // Update when URL params change
  useEffect(() => {
    if (cityParam) {
      setSelectedCity(cityParam);
    } else {
      setSelectedCity('All Cities');
    }
  }, [cityParam]);

  // Filter deals by city if a city is selected
  const filteredDeals = useMemo(() => {
    if (selectedCity && selectedCity !== 'All Cities') {
      return deals.filter(deal => {
        const dealLocation = locations.find(loc => loc.id === deal.locationId);
        return dealLocation && dealLocation.city.toLowerCase() === selectedCity.toLowerCase();
      });
    }
    return deals;
  }, [selectedCity, deals]);

  return (
    <div className="space-y-0">
      <SlidingBanner />
      
      <FeaturedProducts />
      <MultiAdsBanner />
      <RecentOffers />
      <BrandLogos />
    </div>
  );
};

export default HomePage;
