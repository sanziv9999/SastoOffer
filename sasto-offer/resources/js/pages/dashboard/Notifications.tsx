import { useState, useEffect } from 'react';
import { Bell, Gift, Tag, AlertCircle, CheckCircle, Trash2, CheckCircle2 } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Notification } from '@/types';

interface NotificationsProps {
  notifications: Notification[];
}

const getIcon = (type: string) => {
  switch (type) {
    case 'deal': return <Tag className="h-5 w-5 text-primary" />;
    case 'coupon': return <Gift className="h-5 w-5 text-green-500" />;
    case 'system': return <AlertCircle className="h-5 w-5 text-blue-500" />;
    default: return <Bell className="h-5 w-5" />;
  }
};

const Notifications = ({ notifications: initialNotifications }: NotificationsProps) => {
  const [notifications, setNotifications] = useState<Notification[]>(initialNotifications);

  // Update local state if props change (e.g. on page reload/navigation if AppShell re-renders)
  useEffect(() => {
    setNotifications(initialNotifications);
  }, [initialNotifications]);

  const unreadCount = notifications?.filter((n) => !n.read).length || 0;

  const handleMarkAsRead = (id: string) => {
    setNotifications(prev =>
      prev.map(n => n.id === id ? { ...n, read: true } : n)
    );
  };

  const handleMarkAllAsRead = () => {
    setNotifications(prev => prev.map(n => ({ ...n, read: true })));
  };

  const handleDelete = (id: string) => {
    setNotifications(prev => prev.filter(n => n.id !== id));
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Notifications</h1>
          <p className="text-muted-foreground">{unreadCount} unread notifications</p>
        </div>
        {unreadCount > 0 && (
          <Button variant="outline" size="sm" onClick={handleMarkAllAsRead}>
            <CheckCircle2 className="h-4 w-4 mr-2" />
            Mark all as read
          </Button>
        )}
      </div>

      <div className="space-y-3">
        {notifications?.length > 0 ? (
          notifications.map((notification) => (
            <Card key={notification.id} className={`${notification.read ? 'bg-muted/30 border-muted' : 'border-primary/20 shadow-sm'}`}>
              <CardContent className="flex items-start gap-4 p-4">
                <div className={`mt-1 p-2 rounded-full ${notification.read ? 'bg-muted' : 'bg-primary/10'}`}>
                  {getIcon(notification.type)}
                </div>
                <div className="flex-grow">
                  <div className="flex items-center gap-2">
                    <h3 className={`font-semibold ${notification.read ? 'text-muted-foreground' : 'text-foreground'}`}>
                      {notification.title}
                    </h3>
                    {!notification.read && <Badge className="bg-primary text-[10px] h-4">New</Badge>}
                  </div>
                  <p className={`text-sm mt-1 ${notification.read ? 'text-muted-foreground/70' : 'text-muted-foreground'}`}>
                    {notification.message}
                  </p>
                  <p className="text-xs text-muted-foreground/60 mt-2">{notification.time}</p>
                </div>
                <div className="flex gap-1">
                  {!notification.read && (
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-8 w-8 text-primary hover:text-primary hover:bg-primary/10"
                      onClick={() => handleMarkAsRead(notification.id)}
                      title="Mark as read"
                    >
                      <CheckCircle className="h-4 w-4" />
                    </Button>
                  )}
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                    onClick={() => handleDelete(notification.id)}
                    title="Delete"
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))
        ) : (
          <Card className="border-dashed">
            <CardContent className="p-12 text-center">
              <Bell className="h-12 w-12 mx-auto mb-4 text-muted-foreground/30" />
              <h3 className="text-lg font-medium text-muted-foreground">No notifications</h3>
              <p className="text-sm text-muted-foreground/70">We'll notify you when something important happens.</p>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  );
};

Notifications.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Notifications;
