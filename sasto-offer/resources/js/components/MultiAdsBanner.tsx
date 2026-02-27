import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';

interface AdItem {
  id: number;
  title: string;
  description: string;
  imageUrl: string;
  link: string;
  gradient: string;
}

const adItems: AdItem[] = [
  {
    id: 1,
    title: "Electronics Super Sale",
    description: "Up to 70% off on latest gadgets",
    imageUrl: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format",
    link: "/search?category=electronics",
    gradient: "from-primary/90 to-primary/60"
  },
  {
    id: 2,
    title: "Fashion Week Special",
    description: "Designer clothes at unbeatable prices",
    imageUrl: "https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&auto=format",
    link: "/search?category=fashion",
    gradient: "from-blue-800/90 to-blue-600/60"
  },
  {
    id: 3,
    title: "Food & Dining",
    description: "Delicious deals at top restaurants",
    imageUrl: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&auto=format",
    link: "/search?category=food-dining",
    gradient: "from-orange-800/90 to-orange-600/60"
  },
  {
    id: 4,
    title: "Travel Adventures",
    description: "Exclusive holiday packages & tours",
    imageUrl: "https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=600&auto=format",
    link: "/search?category=travel",
    gradient: "from-green-800/90 to-green-600/60"
  },
  {
    id: 5,
    title: "Beauty & Wellness",
    description: "Pamper yourself with spa deals",
    imageUrl: "https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=600&auto=format",
    link: "/search?category=beauty-spa",
    gradient: "from-pink-800/90 to-pink-600/60"
  },
  {
    id: 6,
    title: "Entertainment",
    description: "Movies, events & fun activities",
    imageUrl: "https://images.unsplash.com/photo-1489599953280-375e6b8d6ac8?w=600&auto=format",
    link: "/search?category=activities-events",
    gradient: "from-purple-800/90 to-purple-600/60"
  }
];

const MultiAdsBanner = () => {
  return (
    <section className="py-8 bg-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold mb-2">Discover Amazing Deals</h2>
          <p className="text-muted-foreground">Explore our curated collection of offers across all categories</p>
        </div>
        
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
          {adItems.map((ad) => (
            <Link 
              key={ad.id} 
              to={ad.link} 
              className="group relative overflow-hidden rounded-lg aspect-[4/5] hover:scale-105 transition-all duration-300 shadow-md hover:shadow-xl"
            >
              <div className={`absolute inset-0 bg-gradient-to-br ${ad.gradient} z-10`}></div>
              <img 
                src={ad.imageUrl} 
                alt={ad.title} 
                className="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500"
              />
              <div className="absolute inset-0 z-20 flex flex-col justify-end p-4 text-white">
                <h3 className="font-bold text-sm lg:text-base mb-1 leading-tight">{ad.title}</h3>
                <p className="text-xs lg:text-sm mb-3 leading-tight opacity-90">{ad.description}</p>
                <span className="text-xs inline-flex items-center font-medium">
                  Shop Now
                  <ArrowRight className="ml-1 h-3 w-3" />
                </span>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default MultiAdsBanner;