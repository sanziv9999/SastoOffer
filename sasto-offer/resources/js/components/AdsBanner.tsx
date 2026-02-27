
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';

const AdsBanner = () => {
  return (
    <section className="py-6 bg-white">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* First Ad */}
          <Link to="/search?category=electronics" className="group relative overflow-hidden rounded-lg">
            <div className="absolute inset-0 bg-gradient-to-r from-primary/80 to-primary/40 z-10"></div>
            <img 
              src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format" 
              alt="Electronics Sale" 
              className="w-full h-48 object-cover transition-transform group-hover:scale-105 duration-300"
            />
            <div className="absolute inset-0 z-20 flex flex-col justify-center p-6 text-white">
              <h3 className="text-xl font-bold mb-2">Electronics Super Sale</h3>
              <p className="mb-4">Up to 50% off on all electronics</p>
              <span className="text-sm inline-flex items-center">
                Shop Now
                <ArrowRight className="ml-2 h-4 w-4" />
              </span>
            </div>
          </Link>
          
          {/* Second Ad */}
          <Link to="/search?category=fashion" className="group relative overflow-hidden rounded-lg">
            <div className="absolute inset-0 bg-gradient-to-r from-blue-800/80 to-blue-600/40 z-10"></div>
            <img 
              src="https://images.unsplash.com/photo-1445205170230-053b83016050?w=600&auto=format" 
              alt="Fashion Sale" 
              className="w-full h-48 object-cover transition-transform group-hover:scale-105 duration-300"
            />
            <div className="absolute inset-0 z-20 flex flex-col justify-center p-6 text-white">
              <h3 className="text-xl font-bold mb-2">Summer Fashion</h3>
              <p className="mb-4">New arrivals at special prices</p>
              <span className="text-sm inline-flex items-center">
                Explore Collection
                <ArrowRight className="ml-2 h-4 w-4" />
              </span>
            </div>
          </Link>
        </div>
      </div>
    </section>
  );
};

export default AdsBanner;
