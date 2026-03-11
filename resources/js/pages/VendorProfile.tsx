
import { Link } from '@inertiajs/react';
import { Store, Star, MapPin, Phone, Mail, Globe, Clock, Briefcase } from 'lucide-react';
import { Button } from '@/components/ui/button';
import DealGrid from '@/components/DealGrid';
import { deals } from '@/data/mockData';
import RootLayout from '@/layouts/RootLayout';

const VendorProfile = ({ vendorProfile }: { vendorProfile: any }) => {
  // If no dynamic data is passed, use a default skeleton or handle it
  const vendor = vendorProfile || {
    business_name: 'Loading...',
    description: '',
    public_phone: '',
    public_email: '',
    website_url: '',
    business_hours: '',
    business_type: '',
    primary_category: null,
    images: [],
    rating: 0,
    reviewCount: 0
  };

  // Helper to format address from relation if available
  const displayAddress = vendor.default_address 
    ? `${vendor.default_address.tole}, ${vendor.default_address.municipality}, ${vendor.default_address.district}`
    : 'No address provided';

  // Helper to get image URL (logo/cover)
  const getLogo = () => {
    const logoImg = vendor.images?.find((img: any) => img.label === 'logo');
    return logoImg ? `/storage/${logoImg.path}` : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&auto=format&fit=crop&q=60';
  };
  
  const getCover = () => {
    const coverImg = vendor.images?.find((img: any) => img.label === 'cover');
    return coverImg ? `/storage/${coverImg.path}` : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop&q=60';
  };

  // Filter deals for this vendor
  const vendorDeals = deals.filter(deal => deal.vendorId === (vendor.id?.toString() || '1')).slice(0, 8);

  return (
    <div className="min-h-screen">
      {/* Cover Image */}
      <div className="h-48 md:h-64 w-full relative">
        <img
          src={getCover()}
          alt={`${vendor.business_name} cover`}
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
              src={getLogo()}
              alt={vendor.business_name}
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
                    {vendor.business_name}
                  </h1>
                  <div className="flex items-center mt-2 text-sm text-gray-600">
                    <Star className="h-4 w-4 text-yellow-500 fill-yellow-500" />
                    <span className="ml-1 font-medium">{vendor.rating || 4.5}</span>
                    <span className="mx-1 text-gray-400">•</span>
                    <span>{vendor.reviewCount || 0} reviews</span>
                    {vendor.primary_category && (
                      <>
                        <span className="mx-1 text-gray-400">•</span>
                        <span className="text-primary font-medium">{vendor.primary_category.name}</span>
                      </>
                    )}
                  </div>
                </div>
                <Button asChild>
                  <Link href={`/search?vendorId=${vendor.id}`}>
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
                    <p className="text-sm text-gray-600">{displayAddress}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Phone className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Phone</p>
                    <p className="text-sm text-gray-600">{vendor.public_phone || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Mail className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Email</p>
                    <p className="text-sm text-gray-600">{vendor.public_email || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Globe className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Website</p>
                    <p className="text-sm text-gray-600">{vendor.website_url || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Clock className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Business Hours</p>
                    {Array.isArray(vendor.business_hours) ? (
                        <div className="mt-1 space-y-1">
                          {vendor.business_hours.map((hour: any, idx: number) => (
                              <div key={idx} className="text-sm flex justify-between gap-4 text-gray-600">
                                <span>{hour.day}</span>
                                <span>{hour.is_closed ? 'Closed' : `${hour.open} - ${hour.close}`}</span>
                              </div>
                          ))}
                        </div>
                    ) : (
                        <p className="text-sm text-gray-600">{vendor.business_hours || 'N/A'}</p>
                    )}
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Briefcase className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Business Category</p>
                    <p className="text-sm text-gray-600">{vendor.primary_category?.name || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <Briefcase className="h-5 w-5 text-gray-500" />
                  <div>
                    <p className="text-sm font-medium">Business Type</p>
                    <p className="text-sm text-gray-600 capitalize">{vendor.business_type || 'N/A'}</p>
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

VendorProfile.layout = (page: React.ReactNode) => <RootLayout>{page}</RootLayout>;

export default VendorProfile;
