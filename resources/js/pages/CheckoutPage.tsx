
import { useState, useEffect } from 'react';
import { useParams, useSearchParams, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import { deals, vendors } from '@/data/mockData';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { toast } from '@/lib/toast';
import { ArrowLeft, CreditCard, Wallet, ShieldCheck, MapPin } from 'lucide-react';

const CheckoutPage = () => {
  const { id } = useParams<{ id: string }>();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const quantity = parseInt(searchParams.get('qty') || '1', 10);

  const deal = deals.find(d => d.id === id);
  const vendor = deal ? vendors.find(v => v.id === deal.vendorId) : null;

  const [paymentMethod, setPaymentMethod] = useState('card');
  const [processing, setProcessing] = useState(false);

  if (!deal) {
    return (
      <div className="container py-12 text-center min-h-[60vh] flex flex-col items-center justify-center">
        <h1 className="text-2xl font-bold mb-4">Deal not found</h1>
        <Button asChild><Link to="/">Back to Home</Link></Button>
      </div>
    );
  }

  const subtotal = deal.discountedPrice * quantity;
  const tax = subtotal * 0.08;
  const total = subtotal + tax;

  const handlePlaceOrder = () => {
    setProcessing(true);
    setTimeout(() => {
      setProcessing(false);
      toast.success('Order placed successfully!');
      navigate('/dashboard/purchases');
    }, 1500);
  };

  return (
    <div className="container py-8 max-w-4xl">
      <Button variant="ghost" size="sm" className="mb-6" onClick={() => navigate(-1)}>
        <ArrowLeft className="h-4 w-4 mr-2" /> Back
      </Button>

      <h1 className="text-2xl font-bold mb-6">Review & Pay</h1>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {/* Left - Form */}
        <div className="lg:col-span-3 space-y-6">
          {/* Order Summary Card */}
          <div className="bg-background border border-border rounded-lg p-5">
            <h2 className="font-semibold mb-4">Order Details</h2>
            <div className="flex gap-4">
              <img src={deal.image} alt={deal.title} className="h-20 w-20 rounded-lg object-cover" />
              <div className="flex-1">
                <h3 className="font-medium">{deal.title}</h3>
                {vendor && <p className="text-sm text-muted-foreground">{vendor.businessName}</p>}
                <div className="flex items-center gap-3 mt-1">
                  <span className="text-primary font-semibold">${deal.discountedPrice.toFixed(2)}</span>
                  <span className="text-muted-foreground line-through text-sm">${deal.originalPrice.toFixed(2)}</span>
                  <span className="text-xs text-muted-foreground">× {quantity}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Contact Info */}
          <div className="bg-background border border-border rounded-lg p-5">
            <h2 className="font-semibold mb-4">Contact Information</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <Label>Full Name</Label>
                <Input defaultValue={user?.name || ''} placeholder="Your name" className="mt-1" />
              </div>
              <div>
                <Label>Email</Label>
                <Input defaultValue={user?.email || ''} placeholder="you@email.com" className="mt-1" />
              </div>
              <div className="sm:col-span-2">
                <Label>Phone</Label>
                <Input placeholder="+1 (555) 000-0000" className="mt-1" />
              </div>
            </div>
          </div>

          {/* Payment Method */}
          <div className="bg-background border border-border rounded-lg p-5">
            <h2 className="font-semibold mb-4">Payment Method</h2>
            <RadioGroup value={paymentMethod} onValueChange={setPaymentMethod} className="space-y-3">
              <div className={`flex items-center gap-3 p-3 border rounded-lg cursor-pointer ${paymentMethod === 'card' ? 'border-primary bg-primary/5' : 'border-border'}`}>
                <RadioGroupItem value="card" id="card" />
                <Label htmlFor="card" className="flex items-center gap-2 cursor-pointer flex-1">
                  <CreditCard className="h-4 w-4" /> Credit / Debit Card
                </Label>
              </div>
              <div className={`flex items-center gap-3 p-3 border rounded-lg cursor-pointer ${paymentMethod === 'wallet' ? 'border-primary bg-primary/5' : 'border-border'}`}>
                <RadioGroupItem value="wallet" id="wallet" />
                <Label htmlFor="wallet" className="flex items-center gap-2 cursor-pointer flex-1">
                  <Wallet className="h-4 w-4" /> Digital Wallet
                </Label>
              </div>
            </RadioGroup>

            {paymentMethod === 'card' && (
              <div className="mt-4 space-y-3">
                <div>
                  <Label>Card Number</Label>
                  <Input placeholder="4242 4242 4242 4242" className="mt-1" />
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <Label>Expiry</Label>
                    <Input placeholder="MM/YY" className="mt-1" />
                  </div>
                  <div>
                    <Label>CVV</Label>
                    <Input placeholder="123" className="mt-1" />
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right - Price Summary */}
        <div className="lg:col-span-2">
          <div className="bg-background border border-border rounded-lg p-5 sticky top-24">
            <h2 className="font-semibold mb-4">Price Summary</h2>
            <div className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Subtotal ({quantity} item{quantity > 1 ? 's' : ''})</span>
                <span>${subtotal.toFixed(2)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Tax (8%)</span>
                <span>${tax.toFixed(2)}</span>
              </div>
              <div className="flex justify-between text-sm text-green-600">
                <span>Savings</span>
                <span>-${((deal.originalPrice - deal.discountedPrice) * quantity).toFixed(2)}</span>
              </div>
              <Separator />
              <div className="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span className="text-primary">${total.toFixed(2)}</span>
              </div>
            </div>

            <Button 
              className="w-full mt-6" 
              size="lg"
              onClick={handlePlaceOrder}
              disabled={processing}
            >
              {processing ? 'Processing...' : `Pay $${total.toFixed(2)}`}
            </Button>

            <div className="flex items-center justify-center gap-2 mt-4 text-xs text-muted-foreground">
              <ShieldCheck className="h-4 w-4" />
              <span>Secure & encrypted payment</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckoutPage;
