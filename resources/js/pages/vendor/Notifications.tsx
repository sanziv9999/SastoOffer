import { useState } from 'react';
import {
    ShoppingBag,
    CheckCircle2,
    AlertTriangle,
    Info,
    Settings,
    Search,
    Check,
    Trash2,
    Clock,
    ArrowRight
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardHeader,
    CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import DashboardLayout from '@/layouts/DashboardLayout';
import { formatDistanceToNow } from 'date-fns';

const Notifications = () => {
    const [alerts, setAlerts] = useState([
        {
            id: '1',
            type: 'sale',
            title: 'New Voucher Purchased!',
            message: 'John Doe just purchased your "50% Off Luxury 5-Course Dinner" deal.',
            time: new Date(Date.now() - 1000 * 60 * 45), // 45 mins ago
            isRead: false,
            actionText: 'View Order',
            icon: <ShoppingBag className="h-4 w-4 text-primary" />,
            color: 'bg-primary/10'
        },
        {
            id: '2',
            type: 'approval',
            title: 'Deal Approved',
            message: 'Your new deal "Weekend Spa Retreat" has been approved and is now live.',
            time: new Date(Date.now() - 1000 * 60 * 60 * 3), // 3 hours ago
            isRead: true,
            actionText: 'View Deal',
            icon: <CheckCircle2 className="h-4 w-4 text-green-600" />,
            color: 'bg-green-100'
        },
        {
            id: '3',
            type: 'alert',
            title: 'Low Stock Warning',
            message: 'Your deal "Mid-week Massage" has only 5 vouchers left.',
            time: new Date(Date.now() - 1000 * 60 * 60 * 12), // 12 hours ago
            isRead: false,
            actionText: 'Update Stock',
            icon: <AlertTriangle className="h-4 w-4 text-amber-600" />,
            color: 'bg-amber-100'
        },
        {
            id: '4',
            type: 'system',
            title: 'Payout Processed',
            message: 'Your weekly payout of $1,240.50 has been sent to your bank account.',
            time: new Date(Date.now() - 1000 * 60 * 60 * 24), // 1 day ago
            isRead: true,
            actionText: 'Billing History',
            icon: <Info className="h-4 w-4 text-blue-600" />,
            color: 'bg-blue-100'
        },
    ]);

    const [searchTerm, setSearchTerm] = useState('');

    const markAsRead = (id: string) => {
        setAlerts(alerts.map((a: any) => a.id === id ? { ...a, isRead: true } : a));
    };

    const markAllAsRead = () => {
        setAlerts(alerts.map((a: any) => ({ ...a, isRead: true })));
    };

    const deleteNotification = (id: string) => {
        setAlerts(alerts.filter((a: any) => a.id !== id));
    };

    const unreadCount = alerts.filter((a: any) => !a.isRead).length;

    const filteredAlerts = alerts.filter((a: any) =>
        a.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        a.message.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Merchant Alerts</h1>
                    <p className="text-muted-foreground">Stay updated on your sales and deal performance.</p>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" size="sm" onClick={markAllAsRead} disabled={unreadCount === 0}>
                        <Check className="mr-2 h-4 w-4" />
                        Mark all as read
                    </Button>
                    <Button variant="outline" size="sm">
                        <Settings className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                        <Tabs defaultValue="all" className="w-full">
                            <div className="flex items-center justify-between">
                                <TabsList>
                                    <TabsTrigger value="all" className="relative">
                                        All
                                        <Badge variant="secondary" className="ml-2 h-5 w-5 p-0 flex items-center justify-center rounded-full text-[10px]">{alerts.length}</Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="unread" className="relative">
                                        Unread
                                        {unreadCount > 0 && (
                                            <Badge className="ml-2 h-5 w-5 p-0 flex items-center justify-center rounded-full text-[10px] bg-primary text-primary-foreground">{unreadCount}</Badge>
                                        )}
                                    </TabsTrigger>
                                    <TabsTrigger value="sales">Sales</TabsTrigger>
                                </TabsList>

                                <div className="relative hidden sm:block">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Filter alerts..."
                                        className="pl-9 h-9 w-[200px]"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                            </div>

                            <Separator className="mt-4" />

                            <TabsContent value="all" className="mt-4 space-y-0">
                                <div className="divide-y">
                                    {filteredAlerts.length > 0 ? (
                                        filteredAlerts.map((notif) => (
                                            <div
                                                key={notif.id}
                                                className={`group relative flex items-start gap-4 p-4 transition-colors hover:bg-muted/50 ${!notif.isRead ? 'bg-primary/5' : ''}`}
                                            >
                                                <div className={`mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${notif.color}`}>
                                                    {notif.icon}
                                                </div>
                                                <div className="flex-grow space-y-1 pr-8">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className={`text-sm font-semibold ${!notif.isRead ? 'text-primary' : ''}`}>
                                                            {notif.title}
                                                        </h4>
                                                        <span className="flex items-center gap-1 text-[10px] text-muted-foreground uppercase font-bold tracking-tighter">
                                                            <Clock className="h-3 w-3" />
                                                            {formatDistanceToNow(notif.time, { addSuffix: true })}
                                                        </span>
                                                    </div>
                                                    <p className="text-sm text-muted-foreground line-clamp-2 leading-snug">
                                                        {notif.message}
                                                    </p>
                                                    <div className="flex items-center gap-4 pt-1">
                                                        <Button variant="link" className="h-auto p-0 text-xs font-bold text-primary flex items-center group/btn">
                                                            {notif.actionText}
                                                            <ArrowRight className="ml-1 h-3 w-3 transition-transform group-hover/btn:translate-x-1" />
                                                        </Button>
                                                        {!notif.isRead && (
                                                            <Button
                                                                variant="ghost"
                                                                className="h-auto p-0 text-xs text-muted-foreground hover:text-foreground"
                                                                onClick={() => markAsRead(notif.id)}
                                                            >
                                                                Mark read
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="absolute right-2 top-4 opacity-0 group-hover:opacity-100 transition-opacity h-8 w-8 text-muted-foreground hover:text-destructive"
                                                    onClick={() => deleteNotification(notif.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                                {!notif.isRead && (
                                                    <div className="absolute left-1 top-1/2 -translate-y-1/2 h-2 w-2 rounded-full bg-primary" />
                                                )}
                                            </div>
                                        ))
                                    ) : (
                                        <div className="py-12 text-center text-muted-foreground">
                                            No notifications found matching your search.
                                        </div>
                                    )}
                                </div>
                            </TabsContent>

                            <TabsContent value="unread" className="mt-4">
                                <div className="divide-y">
                                    {alerts.filter((n: any) => !n.isRead).length > 0 ? (
                                        alerts.filter((n: any) => !n.isRead).map((notif: any) => (
                                            <div
                                                key={notif.id}
                                                className="group relative flex items-start gap-4 p-4 bg-primary/5 transition-colors hover:bg-muted/50"
                                            >
                                                <div className={`mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${notif.color}`}>
                                                    {notif.icon}
                                                </div>
                                                <div className="flex-grow space-y-1">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="text-sm font-semibold text-primary">{notif.title}</h4>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="h-auto p-0 text-xs text-muted-foreground"
                                                            onClick={() => markAsRead(notif.id)}
                                                        >
                                                            Mark read
                                                        </Button>
                                                    </div>
                                                    <p className="text-xs text-muted-foreground">{notif.message}</p>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="py-12 text-center text-muted-foreground">
                                            All caught up! No unread notifications.
                                        </div>
                                    )}
                                </div>
                            </TabsContent>

                            <TabsContent value="sales" className="mt-4">
                                <div className="divide-y">
                                    {alerts.filter((n: any) => n.type === 'sale').length > 0 ? (
                                        alerts.filter((n: any) => n.type === 'sale').map((notif: any) => (
                                            <div
                                                key={notif.id}
                                                className={`group relative flex items-start gap-4 p-4 transition-colors hover:bg-muted/50 ${!notif.isRead ? 'bg-primary/5' : ''}`}
                                            >
                                                <div className={`mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${notif.color}`}>
                                                    {notif.icon}
                                                </div>
                                                <div className="flex-grow space-y-1">
                                                    <h4 className="text-sm font-semibold">{notif.title}</h4>
                                                    <p className="text-xs text-muted-foreground">{notif.message}</p>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="flex flex-col items-center justify-center py-12 text-muted-foreground">
                                            <ShoppingBag className="h-10 w-10 mb-4 opacity-20" />
                                            <p className="font-medium text-sm">No sales alerts yet.</p>
                                        </div>
                                    )}
                                </div>
                            </TabsContent>
                        </Tabs>
                    </div>
                </CardHeader>
                <CardFooter className="bg-muted/30 flex justify-center py-3 border-t">
                    <Button variant="ghost" size="sm" className="text-xs">Load older notifications</Button>
                </CardFooter>
            </Card>

            {/* Notification Preferences Quick Link */}
            <Card className="bg-primary/5 border-primary/20">
                <CardContent className="p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Settings className="h-5 w-5 text-primary" />
                        <div className="text-sm">
                            <span className="font-bold">Notification settings</span>
                            <p className="text-muted-foreground text-xs">Manage email and push notification preferences.</p>
                        </div>
                    </div>
                    <Button variant="outline" size="sm">Configure</Button>
                </CardContent>
            </Card>
        </div>
    );
};

Notifications.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Notifications;
