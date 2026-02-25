import { Bell, Gift, Tag, AlertCircle, CheckCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const mockNotifications = [
  { id: '1', type: 'deal', title: 'Flash Sale Alert!', message: '50% off all restaurants near you for the next 2 hours.', time: '2 hours ago', read: false },
  { id: '2', type: 'coupon', title: 'Coupon Expiring Soon', message: 'Your coupon for "Gourmet Delights" expires tomorrow.', time: '5 hours ago', read: false },
  { id: '3', type: 'system', title: 'Welcome to Offer Oasis!', message: 'Thanks for joining. Start exploring amazing deals in your city.', time: '1 day ago', read: true },
  { id: '4', type: 'deal', title: 'New Deals in Your Area', message: '15 new deals added near your location this week.', time: '2 days ago', read: true },
  { id: '5', type: 'coupon', title: 'Coupon Redeemed', message: 'Your coupon SAVE20 was successfully redeemed.', time: '3 days ago', read: true },
];

const getIcon = (type: string) => {
  switch (type) {
    case 'deal': return <Tag className="h-5 w-5 text-primary" />;
    case 'coupon': return <Gift className="h-5 w-5 text-green-500" />;
    case 'system': return <AlertCircle className="h-5 w-5 text-blue-500" />;
    default: return <Bell className="h-5 w-5" />;
  }
};

const Notifications = () => {
  const unreadCount = mockNotifications.filter(n => !n.read).length;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Notifications</h1>
          <p className="text-muted-foreground">{unreadCount} unread notifications</p>
        </div>
        <Button variant="outline" size="sm">Mark all as read</Button>
      </div>

      <div className="space-y-3">
        {mockNotifications.map(notification => (
          <Card key={notification.id} className={notification.read ? 'opacity-70' : 'border-primary/30'}>
            <CardContent className="flex items-start gap-4 p-4">
              <div className="mt-1">{getIcon(notification.type)}</div>
              <div className="flex-grow">
                <div className="flex items-center gap-2">
                  <h3 className="font-semibold">{notification.title}</h3>
                  {!notification.read && <Badge className="bg-primary text-xs">New</Badge>}
                </div>
                <p className="text-sm text-muted-foreground mt-1">{notification.message}</p>
                <p className="text-xs text-muted-foreground mt-2">{notification.time}</p>
              </div>
              {!notification.read && (
                <Button variant="ghost" size="sm">
                  <CheckCircle className="h-4 w-4" />
                </Button>
              )}
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
};

export default Notifications;
