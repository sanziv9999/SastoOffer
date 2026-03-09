
import { Link } from 'react-router-dom';

type BannerAdProps = {
  image: string;
  title: string;
  description: string;
  linkUrl: string;
  linkText: string;
};

const BannerAd = ({ image, title, description, linkUrl, linkText }: BannerAdProps) => {
  return (
    <div className="container mx-auto px-4 my-8">
      <div className="bg-gradient-to-r from-slate-100 to-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div className="flex flex-col md:flex-row">
          <div className="md:w-1/2">
            <img 
              src={image} 
              alt={title} 
              className="w-full h-full object-cover" 
            />
          </div>
          <div className="md:w-1/2 p-6 md:p-10 flex flex-col justify-center">
            <h3 className="text-2xl md:text-3xl font-bold mb-4 text-slate-800">{title}</h3>
            <p className="text-slate-600 mb-6">{description}</p>
            <div>
              <Link 
                to={linkUrl} 
                className="bg-primary hover:bg-primary/90 text-white font-medium py-2 px-6 rounded-md inline-block transition-colors"
              >
                {linkText}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default BannerAd;
