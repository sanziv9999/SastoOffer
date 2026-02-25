
import { Link } from 'react-router-dom';

type BrandLogoProps = {
  id: string;
  name: string;
  logo: string;
};

const BrandLogo = ({ id, name, logo }: BrandLogoProps) => {
  return (
    <Link 
      to={`/vendor/${id}`} 
      className="flex flex-col items-center"
    >
      <div className="w-20 h-20 sm:w-24 sm:h-24 rounded-md overflow-hidden border border-gray-200 bg-white p-3 flex items-center justify-center">
        <img 
          src={logo} 
          alt={`${name} logo`} 
          className="w-full h-full object-contain" 
        />
      </div>
      <span className="mt-2 text-sm font-medium text-gray-700">
        {name}
      </span>
    </Link>
  );
};

export default BrandLogo;
