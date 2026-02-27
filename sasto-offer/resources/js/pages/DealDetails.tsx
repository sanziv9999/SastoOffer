
import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import { 
  Clock, 
  MapPin, 
  Heart, 
  Share2, 
  Check, 
  Star, 
  AlertTriangle,
  ShoppingCart
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/lib/toast';
import { deals, vendors, reviews } from '@/data/mockData';
import { formatDistanceToNow } from 'date-fns';
import { Deal, Vendor } from '@/types';

const DealDetails = () => {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [deal, setDeal] = useState<Deal | null>(null);
  const [vendor, setVendor] = useState<Vendor | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);
  
  // Fetch deal data
  useEffect(() => {
    // Simulate API call
    const timer = setTimeout(() => {
      const foundDeal = deals.find(d => d.id === id);
      setDeal(foundDeal || null);
      
      if (foundDeal) {
        const foundVendor = vendors.find(v => v.id === foundDeal.vendorId);
        setVendor(foundVendor || null);
      }
      
      setLoading(false);
    }, 500);
    
    return () => clearTimeout(timer);
  }, [id]);
  
  if (loading) {
    return (
      <div className="container py-12 flex items-center justify-center min-h-[60vh]">
        <div className="animate-pulse flex flex-col items-center space-y-4">
          <div className="h-12 w-48 bg-gray-200 rounded"></div>
          <div className="h-6 w-32 bg-gray-200 rounded"></div>
          <div className="h-24 w-full max-w-lg bg-gray-200 rounded"></div>
        </div>
      </div>
    );
  }
  
  if (!deal) {
    return (
      <div className="container py-12 text-center min-h-[60vh] flex flex-col items-center justify-center">
        <AlertTriangle className="h-16 w-16 text-yellow-500 mb-4" />
        <h1 className="text-3xl font-bold mb-2">Deal Not Found</h1>
        <p className="text-muted-foreground mb-6">
          The deal you're looking for doesn't exist or may have expired.
        </p>
        <Button asChild>
          <Link to="/">Back to Homepage</Link>
        </Button>
      </div>
    );
  }
  
  const handleAddToCart = () => {
    if (!user) {
      toast.error("Please log in to purchase this deal");
      return;
    }
    
    // In a real app, this would add to cart in database/context
    toast.success(`Added to cart: ${quantity} x ${deal.title}`);
  };
  
  const handleBuyNow = () => {
    if (!user) {
      toast.error("Please log in to purchase this deal");
      return;
    }
    
    navigate(`/checkout/${deal.id}?qty=${quantity}`);
  };
  
  const incrementQuantity = () => {
    if (deal.maxQuantity && quantity >= deal.maxQuantity) {
      toast.error(`Maximum ${deal.maxQuantity} per order`);
      return;
    }
    setQuantity(prev => prev + 1);
  };
  
  const decrementQuantity = () => {
    if (quantity > 1) {
      setQuantity(prev => prev - 1);
    }
  };
  
  const timeLeft = formatDistanceToNow(new Date(deal.endDate), { addSuffix: true });
  const discountPercentage = deal.discountPercentage || 
    Math.round(((deal.originalPrice - deal.discountedPrice) / deal.originalPrice) * 100);
  
  const dealReviews = reviews.filter(review => review.dealId === deal.id);
  
  return (
    <div className="container py-8">
      {/* Breadcrumb */}
      <div className="text-sm mb-6 flex items-center">
        <Link to="/" className="text-muted-foreground hover:text-foreground">
          Home
        </Link>
        <span className="mx-2">/</span>
        <Link to="/search" className="text-muted-foreground hover:text-foreground">
          Deals
        </Link>
        <span className="mx-2">/</span>
        <span className="font-medium truncate">{deal.title}</span>
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {/* Left Column - Images */}
        <div>
          <div className="bg-white rounded-lg overflow-hidden shadow-md mb-4">
            <img 
              src={deal.image} 
              alt={deal.title}
              className="w-full aspect-video object-cover"
            />
          </div>
          
          {/* Additional images */}
          {deal.images && deal.images.length > 0 && (
            <div className="grid grid-cols-4 gap-2">
              {deal.images.map((image, index) => (
                <div key={index} className="bg-white rounded-lg overflow-hidden shadow-sm">
                  <img 
                    src={image} 
                    alt={`${deal.title} - Image ${index + 1}`}
                    className="w-full aspect-square object-cover"
                  />
                </div>
              ))}
            </div>
          )}
        </div>
        
        {/* Right Column - Details */}
        <div>
          {/* Title and badges */}
          <div className="mb-4">
            <div className="flex flex-wrap gap-2 mb-3">
              <Badge variant="secondary" className="text-xs">
                {deal.type === 'bogo' ? 'Buy One Get One' : `${discountPercentage}% Off`}
              </Badge>
              {deal.featured && (
                <Badge variant="default" className="text-xs">
                  Featured
                </Badge>
              )}
              <Badge variant="outline" className="text-xs">
                {deal.categoryId === '1' ? 'Restaurant' : 
                 deal.categoryId === '2' ? 'Beauty & Spa' :
                 deal.categoryId === '3' ? 'Activities' : 'Other'}
              </Badge>
            </div>
            
            <h1 className="text-2xl md:text-3xl font-bold mb-2">{deal.title}</h1>
            
            {/* Rating */}
            <div className="flex items-center mb-4">
              <div className="flex items-center">
                <Star className="h-4 w-4 text-yellow-500 mr-1" />
                <span className="font-medium">{deal.averageRating?.toFixed(1)}</span>
              </div>
              <span className="mx-2 text-muted-foreground">•</span>
              <span className="text-muted-foreground">{dealReviews.length} reviews</span>
              {vendor && (
                <>
                  <span className="mx-2 text-muted-foreground">•</span>
                  <Link to={`/vendor/${vendor.id}`} className="text-primary hover:underline">
                    {vendor.businessName}
                  </Link>
                </>
              )}
            </div>
          </div>
          
          {/* Price */}
          <div className="bg-muted/50 p-4 rounded-lg mb-6">
            <div className="flex items-end gap-3 mb-2">
              <span className="text-3xl font-bold text-primary">
                ${deal.discountedPrice.toFixed(2)}
              </span>
              <span className="text-lg text-muted-foreground line-through">
                ${deal.originalPrice.toFixed(2)}
              </span>
              <span className="text-sm font-medium bg-secondary/10 text-secondary px-2 py-0.5 rounded-full">
                Save ${(deal.originalPrice - deal.discountedPrice).toFixed(2)}
              </span>
            </div>
            
            <div className="flex items-center text-sm text-muted-foreground">
              <Clock className="h-4 w-4 mr-1" />
              <span>
                {new Date() > new Date(deal.endDate) 
                  ? 'Offer expired' 
                  : `Offer ends ${timeLeft}`}
              </span>
              
              {deal.maxQuantity && (
                <span className="ml-4">
                  {deal.quantitySold} / {deal.maxQuantity} sold
                </span>
              )}
            </div>
          </div>
          
          {/* Add to Cart */}
          <div className="mb-6">
            <div className="flex items-center mb-3">
              <span className="mr-4">Quantity:</span>
              <div className="flex items-center">
                <Button 
                  variant="outline" 
                  size="icon" 
                  className="h-8 w-8 rounded-r-none" 
                  onClick={decrementQuantity}
                  disabled={quantity <= 1}
                >
                  -
                </Button>
                <div className="h-8 px-4 flex items-center justify-center border-y border-input">
                  {quantity}
                </div>
                <Button 
                  variant="outline" 
                  size="icon" 
                  className="h-8 w-8 rounded-l-none" 
                  onClick={incrementQuantity}
                  disabled={deal.maxQuantity ? quantity >= deal.maxQuantity : false}
                >
                  +
                </Button>
              </div>
            </div>
            
            <div className="flex flex-wrap gap-3">
              <Button 
                onClick={handleAddToCart}
                variant="outline"
                className="flex-1 min-w-[140px]"
              >
                <ShoppingCart className="mr-2 h-4 w-4" />
                Add to Cart
              </Button>
              <Button 
                onClick={handleBuyNow}
                className="flex-1 min-w-[140px]"
              >
                Buy Now
              </Button>
            </div>
          </div>
          
          {/* Actions */}
          <div className="flex space-x-4 mb-6">
            <Button variant="ghost" size="sm">
              <Heart className="mr-1 h-4 w-4" />
              Save
            </Button>
            <Button variant="ghost" size="sm">
              <Share2 className="mr-1 h-4 w-4" />
              Share
            </Button>
          </div>
          
          {/* Highlights */}
          <div className="mb-6">
            <h3 className="font-semibold mb-3">Highlights</h3>
            <ul className="space-y-2">
              {[
                'Valid for dine-in only',
                'Reservation required',
                'Cannot be combined with other offers',
                'Tax and gratuity not included'
              ].map((item, index) => (
                <li key={index} className="flex items-start">
                  <Check className="h-5 w-5 text-primary mr-2 mt-0.5 flex-shrink-0" />
                  <span>{item}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
      
      {/* Description & Details */}
      <div className="mt-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2">
            <h2 className="text-xl font-bold mb-4">Description</h2>
            <div className="prose max-w-none">
              <p>{deal.description}</p>
            </div>
            
            <Separator className="my-8" />
            
            <h2 className="text-xl font-bold mb-4">Redemption Instructions</h2>
            <div className="prose max-w-none">
              <p>{deal.redemptionInstructions}</p>
            </div>
            
            <Separator className="my-8" />
            
            <h2 className="text-xl font-bold mb-4">Reviews</h2>
            {dealReviews.length > 0 ? (
              <div className="space-y-6">
                {dealReviews.map(review => (
                  <div key={review.id} className="border-b pb-4">
                    <div className="flex items-center mb-2">
                      <div className="flex items-center mr-3">
                        {Array.from({ length: 5 }).map((_, i) => (
                          <Star 
                            key={i} 
                            className={`h-4 w-4 ${i < review.rating ? 'text-yellow-500' : 'text-gray-300'}`}
                            fill={i < review.rating ? 'currentColor' : 'none'}
                          />
                        ))}
                      </div>
                      <span className="font-medium">User</span>
                      <span className="mx-2 text-muted-foreground">•</span>
                      <span className="text-muted-foreground">{formatDistanceToNow(new Date(review.createdAt), { addSuffix: true })}</span>
                    </div>
                    <p>{review.comment}</p>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-muted-foreground">No reviews yet.</p>
            )}
          </div>
          
          <div>
            {vendor && (
              <div className="bg-white shadow-md rounded-lg p-6">
                <h3 className="text-lg font-semibold mb-3">About the Vendor</h3>
                <div className="flex items-center mb-4">
                  {vendor.logo ? (
                    <img 
                      src={vendor.logo} 
                      alt={vendor.businessName}
                      className="h-12 w-12 rounded-full mr-3"
                    />
                  ) : (
                    <div className="h-12 w-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3">
                      {vendor.businessName.charAt(0)}
                    </div>
                  )}
                  <div>
                    <h4 className="font-medium">{vendor.businessName}</h4>
                    <div className="flex items-center text-sm">
                      <Star className="h-4 w-4 text-yellow-500 mr-1" />
                      <span>{vendor.averageRating.toFixed(1)}</span>
                    </div>
                  </div>
                </div>
                
                <p className="text-sm text-muted-foreground mb-4">
                  {vendor.description}
                </p>
                
                {vendor.location && (
                  <div className="flex items-start text-sm mb-4">
                    <MapPin className="h-4 w-4 text-muted-foreground mt-0.5 mr-2" />
                    <div>
                      <p>{vendor.location.address}</p>
                      <p>{vendor.location.city}, {vendor.location.state} {vendor.location.zipCode}</p>
                    </div>
                  </div>
                )}
                
                <Button asChild variant="outline" className="w-full">
                  <Link to={`/vendor/${vendor.id}`}>
                    View Profile
                  </Link>
                </Button>
              </div>
            )}
            
            <div className="bg-muted/30 rounded-lg p-6 mt-6">
              <h3 className="text-lg font-semibold mb-3">Similar Deals</h3>
              <div className="space-y-4">
                {deals
                  .filter(d => d.id !== deal.id && d.categoryId === deal.categoryId)
                  .slice(0, 3)
                  .map(similarDeal => (
                    <Link 
                      key={similarDeal.id} 
                      to={`/deals/${similarDeal.id}`}
                      className="flex gap-3 hover:bg-muted/50 p-2 rounded -m-2"
                    >
                      <img 
                        src={similarDeal.image} 
                        alt={similarDeal.title}
                        className="h-16 w-16 object-cover rounded"
                      />
                      <div>
                        <h4 className="font-medium line-clamp-2">{similarDeal.title}</h4>
                        <div className="flex items-center gap-2 text-sm">
                          <span className="text-primary font-semibold">${similarDeal.discountedPrice.toFixed(2)}</span>
                          <span className="text-muted-foreground line-through">${similarDeal.originalPrice.toFixed(2)}</span>
                        </div>
                      </div>
                    </Link>
                  ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DealDetails;
