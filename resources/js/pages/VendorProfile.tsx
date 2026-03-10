
import { Link, useParams } from 'react-router-dom';
import { Store, Star, MapPin, Phone, Mail, Globe, Clock, Briefcase } from 'lucide-react';
import { Button } from '@/components/ui/button';
import DealGrid from '@/components/DealGrid';
import { deals } from '@/data/mockData';

const VendorProfile = () => {
  const { id } = useParams();
  // This would typically come from an API call with the vendor ID
  const vendor = {
    id: id || '1',
    name: 'Gourmet Delights',
    rating: 4.8,
    reviewCount: 124,
    description: 'Premium food and dining experiences at the best prices. From fine dining to casual eateries, we curate the best food deals in town.',
    address: '123 Foodie Lane, Culinary District',
    city: 'New York',
    phone: '+1 (555) 123-4567',
    email: 'contact@gourmetdelights.com',
    website: 'www.gourmetdelights.com',
    hours: 'Mon-Fri: 9am-6pm, Sat-Sun: 10am-4pm',
    logo: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&auto=format&fit=crop&q=60&ixlib=rb-4.0.3',
    coverImage: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop&q=60&ixlib=rb-4.0.3',
    businessType: 'Hybrid',
  };

  // Filter deals for this vendor
  const vendorDeals = deals.filter(deal => deal.vendorId === vendor.id).slice(0, 8);

  return (
    <div className="min-h-screen">
      {/* Cover Image */}
      <div className="h-48 md:h-64 w-full relative">
        <img
          src={vendor.coverImage}
          alt={`${vendor.name} cover`}
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      </div>

      {/* Vendor Info */}
      <div className="container mx-auto px-4 relative">
        <div className="flex flex-col md:flex-row gap-6 -mt-16 mb-8">
          {/* Logo */}
          <div className="w-32 h-32 rounded-xl overflow-hidden border-4 border-white shadow-lg bg-white z-10">
            <img
              src={vendor.logo}
              alt={vendor.name}
              className="w-full h-full object-cover"
            />
          </div>

          {/* Details */}
          <div className="flex-1 mt-6 md:mt-0">
            <div className="bg-white rounded-lg shadow-sm p-6">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                  <h1 className="text-2xl font-bold flex items-center gap-2">
                    <Store className="h-5 w-5 text-primary" />
                    {vendor.name}
                  </h1>
                  <div className="flex items-center mt-2 text-sm text-gray-600">
                    <Star className="h-4 w-4 text-yellow-500 fill-yellow-500" />
                    <span className="ml-1 font-medium">{vendor.rating}</span>
                    <span className="mx-1 text-gray-400">•</span>
                    <span>{vendor.reviewCount} reviews</span>
                  </div>
                </div>
                <Button asChild>
                  <Link to={`/search?vendorId=${vendor.id}`}>
                    View All Deals
                  </Link>
                </Button>
              </div>

              <p className="my-4 text-gray-600">{vendor.description}</p>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div className="flex items-start gap-2">
                  <MapPin className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Address</p>
                    <p className="text-sm text-gray-600">{vendor.address}, {vendor.city}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Phone className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Phone</p>
                    <p className="text-sm text-gray-600">{vendor.phone}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Mail className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Email</p>
                    <p className="text-sm text-gray-600">{vendor.email}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Globe className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Website</p>
                    <p className="text-sm text-gray-600">{vendor.website}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Clock className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Business Hours</p>
                    <p className="text-sm text-gray-600">{vendor.hours}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Briefcase className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Business Type</p>
                    <p className="text-sm text-gray-600 capitalize">{vendor.businessType}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Vendor's Deals */}
        {vendorDeals.length > 0 && (
          <div className="mb-12">
            <h2 className="text-2xl font-bold mb-6">Current Deals</h2>
            <DealGrid
              deals={vendorDeals}
              title="Vendor Deals"
              emptyMessage="No deals available at the moment"
            />
          </div>
        )}
      </div>
    </div>
  );
};

export default VendorProfile;
