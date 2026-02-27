
import { useEffect, useState } from 'react';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

type Banner = {
  id: number;
  title: string;
  description: string;
  imageUrl: string;
  link: string;
};

const banners: Banner[] = [
  {
    id: 1,
    title: "Summer Deals",
    description: "Up to 70% off on selected summer items",
    imageUrl: "https://images.unsplash.com/photo-1534349762230-e0cadf78f5da?w=1200&auto=format",
    link: "/search?category=summer"
  },
  {
    id: 2,
    title: "Tech Sale",
    description: "Latest gadgets at unbeatable prices",
    imageUrl: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&auto=format",
    link: "/search?category=tech"
  },
  {
    id: 3,
    title: "Travel Offers",
    description: "Exclusive holiday packages",
    imageUrl: "https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=1200&auto=format",
    link: "/search?category=travel"
  }
];

const SlidingBanner = () => {
  const [currentBanner, setCurrentBanner] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentBanner((prev) => (prev + 1) % banners.length);
    }, 5000);
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="relative w-full py-4 bg-white">
      <div className="container mx-auto px-4">
        <Carousel className="w-full">
          <CarouselContent>
            {banners.map((banner) => (
              <CarouselItem key={banner.id}>
                <Link to={banner.link} className="block relative overflow-hidden rounded-lg">
                  <div className="aspect-[21/9] md:aspect-[21/6] w-full relative">
                    <img 
                      src={banner.imageUrl} 
                      alt={banner.title} 
                      className="w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent flex flex-col justify-center pl-14 md:pl-20 pr-14 md:pr-20">
                      <h2 className="text-xl md:text-3xl font-bold text-white mb-2">{banner.title}</h2>
                      <p className="text-sm md:text-base text-white/90 mb-4 max-w-md">{banner.description}</p>
                      <div className="flex items-center text-white text-sm md:text-base font-medium">
                        <span>View Offers</span>
                        <ArrowRight className="ml-2 h-4 w-4" />
                      </div>
                    </div>
                  </div>
                </Link>
              </CarouselItem>
            ))}
          </CarouselContent>
          <CarouselPrevious className="left-4 md:left-8" />
          <CarouselNext className="right-4 md:right-8" />
        </Carousel>
      </div>
    </div>
  );
};

export default SlidingBanner;
