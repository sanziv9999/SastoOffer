
import { Bell, Gift, Tag, AlertCircle, CheckCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DashboardLayout from '@/layouts/DashboardLayout';

interface NotificationsProps {
  notifications: any[];
}

const getIcon = (type: string) => {
  switch (type) {
    case 'deal': return <Tag className="h-5 w-5 text-primary" />;
    case 'coupon': return <Gift className="h-5 w-5 text-green-500" />;
    case 'system': return <AlertCircle className="h-5 w-5 text-blue-500" />;
    default: return <Bell className="h-5 w-5" />;
  }
};

const Notifications = ({ notifications }: NotificationsProps) => {
  const unreadCount = notifications?.filter((n: any) => !n.read).length || 0;

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
        {notifications?.map((notification: any) => (
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

Notifications.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Notifications;
