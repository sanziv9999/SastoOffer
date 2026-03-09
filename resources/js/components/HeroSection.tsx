
import { Link } from 'react-router-dom';

const HeroSection = () => {
  return (
    <section className="relative bg-gradient-to-r from-primary/90 to-secondary/90 text-white py-6">
      <div className="container px-4 mx-auto">
        {/* Background decoration */}
        <div className="absolute inset-0 overflow-hidden pointer-events-none opacity-10">
          <div className="absolute -right-24 -top-24 w-96 h-96 rounded-full bg-white"></div>
          <div className="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-white"></div>
        </div>
      </div>
    </section>
  );
};

export default HeroSection;
