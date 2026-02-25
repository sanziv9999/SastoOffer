
import { Link } from 'react-router-dom';
import { Category } from '@/types';

interface CategoryGridProps {
  categories: Category[];
}

const CategoryGrid = ({ categories }: CategoryGridProps) => {
  return (
    <section className="py-10 bg-gray-50">
      <div className="container px-4 mx-auto">
        <h2 className="text-2xl md:text-3xl font-bold text-center mb-8">Popular Categories</h2>
        
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          {categories.map(category => (
            <Link 
              key={category.id}
              to={`/search?category=${category.slug}`}
              className="bg-white rounded-lg shadow-sm p-5 flex flex-col items-center justify-center text-center transition-all hover:shadow-md hover:-translate-y-1"
            >
              <div className="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                {/* Would use actual icons based on category.icon */}
                <span className="text-xl text-primary">
                  {category.name.charAt(0)}
                </span>
              </div>
              <h3 className="font-medium">{category.name}</h3>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default CategoryGrid;
