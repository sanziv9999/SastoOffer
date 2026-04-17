import { useEffect, useMemo, useState } from 'react';
import { router } from '@inertiajs/react';
import { Minus, Plus, ShoppingCart, Trash2 } from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';
import Link from '@/components/Link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

type CartItem = {
  id: number;
  offerPivotId: number;
  title: string;
  dealId: number | null;
  dealSlug: string | null;
  quantity: number;
  discountedPrice: number;
  originalPrice: number;
  image: string;
  typeLabel: string;
  url: string;
  isFirstXOffer: boolean;
};

type Props = {
  items?: CartItem[];
  total?: number;
  count?: number;
};

const Cart = ({ items = [], total = 0, count = 0 }: Props) => {
  const [cartItems, setCartItems] = useState<CartItem[]>(items);
  const [busyItemId, setBusyItemId] = useState<number | null>(null);
  const [isSubmittingCheckout, setIsSubmittingCheckout] = useState(false);

  useEffect(() => {
    setCartItems(items);
  }, [items]);

  const totalAmount = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.discountedPrice * item.quantity, 0),
    [cartItems],
  );
  const totalCount = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.quantity, 0),
    [cartItems],
  );

  const reloadCart = async () => {
    try {
      const res = await fetch('/dashboard/cart/data', {
        headers: { Accept: 'application/json' },
      });
      if (!res.ok) return;
      const data = await res.json();
      setCartItems(Array.isArray(data.items) ? data.items : []);
    } catch {
      // Keep current state if refresh fails.
    }
  };

  const updateQty = async (item: CartItem, nextQty: number) => {
    if (nextQty < 1) return;
    if (item.isFirstXOffer && nextQty > 1) return;

    setBusyItemId(item.id);
    try {
      await (window as any).axios.put(`/cart/${item.id}`, { quantity: nextQty });
      await reloadCart();
    } finally {
      setBusyItemId(null);
    }
  };

  const removeItem = async (item: CartItem) => {
    setBusyItemId(item.id);
    try {
      await (window as any).axios.delete(`/cart/${item.id}`);
      await reloadCart();
    } finally {
      setBusyItemId(null);
    }
  };

  const proceedCheckout = () => {
    setIsSubmittingCheckout(true);
    router.post('/checkout', {}, {
      preserveScroll: true,
      onFinish: () => setIsSubmittingCheckout(false),
    });
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">My Cart</h1>
        <p className="text-muted-foreground">Separate cart page inside customer dashboard</p>
      </div>

      {cartItems.length === 0 ? (
        <Card>
          <CardContent className="py-14 text-center">
            <ShoppingCart className="h-10 w-10 mx-auto mb-3 text-muted-foreground/40" />
            <p className="text-muted-foreground mb-4">Your cart is empty.</p>
            <Button asChild>
              <a href="/search">Browse Deals</a>
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-4">
            {cartItems.map((item) => (
              <Card key={item.id} className={busyItemId === item.id ? 'opacity-60' : ''}>
                <CardContent className="p-4">
                  <div className="flex gap-4">
                    <a href={item.url} className="h-20 w-20 rounded-lg overflow-hidden border">
                      <img src={item.image} alt={item.title} className="h-full w-full object-cover" />
                    </a>
                    <div className="flex-1 min-w-0">
                      <a href={item.url} className="font-semibold hover:text-primary line-clamp-1">
                        {item.title}
                      </a>
                      <p className="text-xs text-muted-foreground mt-1">{item.typeLabel}</p>
                      <div className="mt-3 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <Button
                            size="icon"
                            variant="outline"
                            className="h-8 w-8"
                            onClick={() => updateQty(item, item.quantity - 1)}
                            disabled={busyItemId === item.id || item.quantity <= 1}
                          >
                            <Minus className="h-3 w-3" />
                          </Button>
                          <span className="w-6 text-center text-sm font-semibold">{item.quantity}</span>
                          <Button
                            size="icon"
                            variant="outline"
                            className="h-8 w-8"
                            onClick={() => updateQty(item, item.quantity + 1)}
                            disabled={busyItemId === item.id || (item.isFirstXOffer && item.quantity >= 1)}
                          >
                            <Plus className="h-3 w-3" />
                          </Button>
                          {item.isFirstXOffer && (
                            <span className="text-[11px] text-amber-700">First-X offer: max 1</span>
                          )}
                        </div>
                        <div className="text-right">
                          <div className="text-sm text-muted-foreground line-through">
                            Rs. {(item.originalPrice * item.quantity).toFixed(2)}
                          </div>
                          <div className="font-bold">Rs. {(item.discountedPrice * item.quantity).toFixed(2)}</div>
                        </div>
                      </div>
                    </div>
                    <Button
                      size="icon"
                      variant="ghost"
                      className="h-8 w-8 text-red-600"
                      onClick={() => removeItem(item)}
                      disabled={busyItemId === item.id}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          <Card className="h-fit">
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
              <CardDescription>{totalCount || count} item(s) in your cart</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div className="flex justify-between text-sm">
                <span>Subtotal</span>
                <span>Rs. {(totalAmount || total).toFixed(2)}</span>
              </div>
              <div className="border-t pt-3 flex justify-between font-bold">
                <span>Total</span>
                <span>Rs. {(totalAmount || total).toFixed(2)}</span>
              </div>
              <Button
                className="w-full mt-2"
                onClick={proceedCheckout}
                disabled={isSubmittingCheckout || cartItems.length === 0}
              >
                {isSubmittingCheckout ? 'Processing...' : 'Lock In Offer'}
              </Button>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
};

Cart.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Cart;
