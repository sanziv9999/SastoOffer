import { useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useAuth } from '@/context/AuthContext';
import {
  Clock,
  MapPin,
  Heart,
  Share2,
  Check,
  Star,
  ShoppingCart,
  Tag,
  ChevronLeft,
  AlertTriangle,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { formatDistanceToNow } from 'date-fns';

const DealDetails = () => {
  const { deal } = usePage().props as any;
  const { user } = useAuth();
  const [quantity, setQuantity] = useState(1);

  if (!deal) {
    return (
      <div className="container py-12 text-center min-h-[60vh] flex flex-col items-center justify-center">
        <AlertTriangle className="h-16 w-16 text-yellow-500 mb-4" />
        <h1 className="text-3xl font-bold mb-2">Deal Not Found</h1>
        <p className="text-muted-foreground mb-6">
          The deal you're looking for doesn't exist or may have expired.
        </p>
        <Button asChild>
          <a href="/">Back to Homepage</a>
        </Button>
      </div>
    );
  }

  const discountedPrice = deal.discountedPrice ?? 0;
  const originalPrice = deal.originalPrice ?? 0;
  const savingsAmount = originalPrice > 0 ? originalPrice - discountedPrice : 0;
  const discountPct =
    typeof deal.discountPercent === 'number' && deal.discountPercent > 0
      ? deal.discountPercent
      : originalPrice > 0
        ? Math.round((savingsAmount / originalPrice) * 100)
        : 0;

  const featureImage = deal.images?.find((img: any) => img.attribute_name === 'feature_photo');
  const galleryImages = deal.images?.filter((img: any) => img.attribute_name === 'gallery') ?? [];

  const offers = Array.isArray(deal.offers) ? deal.offers : [];
  const [selectedOfferId, setSelectedOfferId] = useState<number | null>(offers?.[0]?.id ?? null);

  const selectedOffer = useMemo(() => {
    if (!offers.length) return null;
    if (selectedOfferId === null) return offers[0] ?? null;
    return offers.find((o: any) => Number(o.id) === Number(selectedOfferId)) ?? offers[0] ?? null;
  }, [offers, selectedOfferId]);

  const selectedEndsAt = selectedOffer?.pivot?.ends_at ?? deal.ends_at ?? null;

  const timeLeft = selectedEndsAt
    ? formatDistanceToNow(new Date(selectedEndsAt), { addSuffix: true })
    : null;

  const handleAddToCart = () => {
    if (!user) {
      toast.error('Please log in to purchase this deal');
      return;
    }
    toast.success(`Added to cart: ${quantity} × ${deal.title}`);
  };

  const handleBuyNow = () => {
    if (!user) {
      toast.error('Please log in to purchase this deal');
      return;
    }
    window.location.href = `/checkout/${deal.id}?qty=${quantity}`;
  };

  return (
    <div className="container py-8 max-w-7xl mx-auto px-4">
      {/* Breadcrumb */}
      <div className="text-sm mb-6 flex items-center gap-1 text-muted-foreground">
        <a href="/" className="hover:text-foreground transition-colors">Home</a>
        <span>/</span>
        <a href="/" className="hover:text-foreground transition-colors">Deals</a>
        <span>/</span>
        <span className="font-medium text-foreground truncate">{deal.title}</span>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {/* Left Column – Images */}
        <div>
          {featureImage ? (
            <div className="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted">
              <img
                src={featureImage.image_url}
                alt={deal.title}
                className="w-full aspect-video object-cover"
              />
            </div>
          ) : (
            <div className="rounded-2xl overflow-hidden shadow-md mb-3 bg-muted/40 w-full aspect-video flex items-center justify-center">
              <Tag className="h-16 w-16 text-muted-foreground/30" />
            </div>
          )}

          {galleryImages.length > 0 && (
            <div className="grid grid-cols-4 gap-2">
              {galleryImages.map((img: any) => (
                <div key={img.id} className="rounded-lg overflow-hidden shadow-sm bg-muted">
                  <img
                    src={img.image_url}
                    alt={deal.title}
                    className="w-full aspect-square object-cover"
                  />
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Right Column – Details */}
        <div>
          {/* Badges */}
          <div className="flex flex-wrap gap-2 mb-3">
            {discountPct > 0 && (
              <Badge variant="secondary" className="text-xs bg-primary/10 text-primary border-none">
                {discountPct}% Off
              </Badge>
            )}
            {deal.is_featured && (
              <Badge variant="default" className="text-xs">Featured</Badge>
            )}
            {deal.subCategory && (
              <Badge variant="outline" className="text-xs">{deal.subCategory.name}</Badge>
            )}
            <Badge
              className={`text-xs border-none ${deal.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}
            >
              {deal.status?.charAt(0).toUpperCase() + deal.status?.slice(1)}
            </Badge>
          </div>

          <h1 className="text-2xl md:text-3xl font-bold mb-4">{deal.title}</h1>

          {/* Vendor */}
          {deal.vendor && (
            <div className="flex items-center gap-2 mb-4 text-sm text-muted-foreground">
              <span>By</span>
              <a
                href={`/vendor-profile/${deal.vendor.id}`}
                className="text-primary font-medium hover:underline"
              >
                {deal.vendor.business_name}
              </a>
            </div>
          )}

          {/* Offer selector (when multiple offers exist) */}
          {offers.length > 1 && (
            <div className="mb-4">
              <p className="text-sm font-medium mb-2">Choose an offer</p>
              <div className="flex flex-wrap gap-2">
                {offers.map((o: any) => {
                  const active = Number(o.id) === Number(selectedOffer?.id);
                  return (
                    <button
                      key={o.id}
                      type="button"
                      onClick={() => setSelectedOfferId(Number(o.id))}
                      className={[
                        'px-3 py-1.5 rounded-full text-xs border transition-colors',
                        active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted border-input',
                      ].join(' ')}
                    >
                      {o.display_name}
                    </button>
                  );
                })}
              </div>
            </div>
          )}

          {/* Price */}
          <div className="bg-muted/50 p-5 rounded-xl mb-6">
            <div className="flex items-end gap-3 mb-2">
              <span className="text-3xl font-bold text-primary">
                Rs. {discountedPrice.toFixed(2)}
              </span>
              {originalPrice > 0 && (
                <span className="text-lg text-muted-foreground line-through">
                  Rs. {originalPrice.toFixed(2)}
                </span>
              )}
              {savingsAmount > 0 && (
                <span className="text-sm font-medium bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                  Save Rs. {savingsAmount.toFixed(2)}
                </span>
              )}
            </div>

            {timeLeft && (
              <div className="flex items-center text-sm text-muted-foreground mt-2">
                <Clock className="h-4 w-4 mr-1.5" />
                <span>
                  {selectedEndsAt && new Date() > new Date(selectedEndsAt)
                    ? 'Offer expired'
                    : `Offer ends ${timeLeft}`}
                </span>
              </div>
            )}
          </div>

          {/* Quantity + Actions */}
          <div className="mb-6 space-y-4">
            <div className="flex items-center gap-3">
              <span className="text-sm font-medium">Quantity:</span>
              <div className="flex items-center">
                <Button
                  variant="outline" size="icon"
                  className="h-8 w-8 rounded-r-none"
                  onClick={() => setQuantity(q => Math.max(1, q - 1))}
                  disabled={quantity <= 1}
                >–</Button>
                <div className="h-8 px-4 flex items-center justify-center border-y border-input text-sm font-medium">
                  {quantity}
                </div>
                <Button
                  variant="outline" size="icon"
                  className="h-8 w-8 rounded-l-none"
                  onClick={() => setQuantity(q => q + 1)}
                >+</Button>
              </div>
            </div>

            <div className="flex flex-wrap gap-3">
              <Button onClick={handleAddToCart} variant="outline" className="flex-1 min-w-[140px]">
                <ShoppingCart className="mr-2 h-4 w-4" />
                Add to Cart
              </Button>
              <Button onClick={handleBuyNow} className="flex-1 min-w-[140px]">
                Buy Now
              </Button>
            </div>

            <div className="flex gap-3">
              <Button variant="ghost" size="sm">
                <Heart className="mr-1 h-4 w-4" /> Save
              </Button>
              <Button variant="ghost" size="sm">
                <Share2 className="mr-1 h-4 w-4" /> Share
              </Button>
            </div>
          </div>

          {/* Highlights */}
          {deal.highlights && deal.highlights.length > 0 && (
            <div>
              <h3 className="font-semibold mb-3">Highlights</h3>
              <ul className="space-y-2">
                {deal.highlights.map((item: string, i: number) => (
                  <li key={i} className="flex items-start text-sm">
                    <Check className="h-4 w-4 text-primary mr-2 mt-0.5 shrink-0" />
                    <span>{item}</span>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>

      {/* Description Section */}
      <div className="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-8">
          {deal.short_description && (
            <div>
              <h2 className="text-xl font-bold mb-4">Summary</h2>
              <div
                className="prose max-w-none text-sm text-muted-foreground"
                dangerouslySetInnerHTML={{ __html: deal.short_description }}
              />
            </div>
          )}

          {deal.long_description && (
            <>
              <Separator />
              <div>
                <h2 className="text-xl font-bold mb-4">Full Description</h2>
                <div
                  className="prose max-w-none text-sm"
                  dangerouslySetInnerHTML={{ __html: deal.long_description }}
                />
              </div>
            </>
          )}
        </div>

        {/* Vendor Sidebar */}
        <div>
          {deal.vendor && (
            <div className="bg-card border rounded-xl p-6 shadow-sm">
              <h3 className="text-lg font-semibold mb-3">About the Vendor</h3>
              <div className="flex items-center gap-3 mb-4">
                <div className="h-12 w-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                  {deal.vendor.business_name?.charAt(0)}
                </div>
                <div>
                  <h4 className="font-medium">{deal.vendor.business_name}</h4>
                  <div className="flex items-center text-sm text-muted-foreground">
                    <Star className="h-3.5 w-3.5 text-yellow-500 mr-1" />
                    <span>Verified Vendor</span>
                  </div>
                </div>
              </div>

              <Button asChild variant="outline" className="w-full">
                <a href={`/vendor-profile/${deal.vendor.id}`}>
                  <MapPin className="mr-2 h-4 w-4" />
                  View Profile
                </a>
              </Button>
            </div>
          )}

          <div className="mt-4">
            <Button asChild variant="ghost" className="w-full">
              <a href="/">
                <ChevronLeft className="mr-2 h-4 w-4" />
                Back to Deals
              </a>
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DealDetails;
