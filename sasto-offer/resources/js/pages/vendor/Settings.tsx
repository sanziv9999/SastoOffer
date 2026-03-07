
import { useState } from 'react';
import {
    Building2,
    MapPin,
    Clock,
    Mail,
    Phone,
    Globe,
    Instagram,
    Facebook,
    Twitter,
    Save,
    Plus
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import DashboardLayout from '@/layouts/DashboardLayout';
import { toast } from 'sonner';

const VendorSettings = () => {
    const [isLoading, setIsLoading] = useState(false);

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setTimeout(() => {
            setIsLoading(false);
            toast.success('Business profile updated successfully!');
        }, 1500);
    };

    return (
        <div className="max-w-5xl mx-auto space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Business Profile</h1>
                    <p className="text-muted-foreground">Manage your public presence and business details.</p>
                </div>
                <Button onClick={handleSave} disabled={isLoading}>
                    {isLoading ? "Saving..." : "Save Changes"}
                    <Save className="ml-2 h-4 w-4" />
                </Button>
            </div>

            <Tabs defaultValue="general" className="space-y-4">
                <TabsList className="grid w-full grid-cols-4 lg:w-[600px]">
                    <TabsTrigger value="general">General Info</TabsTrigger>
                    <TabsTrigger value="location">Location</TabsTrigger>
                    <TabsTrigger value="hours">Business Hours</TabsTrigger>
                    <TabsTrigger value="social">Social Media</TabsTrigger>
                </TabsList>

                <form onSubmit={handleSave}>
                    <TabsContent value="general" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-primary">
                                    <Building2 className="h-5 w-5" />
                                    Basic Information
                                </CardTitle>
                                <CardDescription>Tell customers about your business and brand.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="businessName">Business Name</Label>
                                        <Input id="businessName" defaultValue="Gourmet Delights" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="category">Primary Category</Label>
                                        <Input id="category" defaultValue="Restaurants & Dining" />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="description">About Your Business</Label>
                                    <Textarea
                                        id="description"
                                        className="min-h-[120px]"
                                        defaultValue="Offering fine dining experiences with the best ingredients from around the world. Our chefs specialize in fusion cuisine that tells a story on every plate."
                                    />
                                </div>

                                <Separator />

                                <div className="space-y-4">
                                    <Label>Branding Assets</Label>
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label className="text-xs text-muted-foreground">Business Logo (1:1 aspect ratio)</Label>
                                            <div className="flex items-center gap-4">
                                                <div className="h-20 w-20 rounded-lg bg-muted flex items-center justify-center overflow-hidden border">
                                                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=120&h=120&auto=format&fit=crop" alt="Logo" className="object-cover" />
                                                </div>
                                                <Button variant="outline" size="sm">Change Logo</Button>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs text-muted-foreground">Cover Photo (16:9 recommended)</Label>
                                            <div className="h-20 w-full rounded-lg bg-muted flex items-center justify-center overflow-hidden border relative group">
                                                <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&auto=format" alt="Cover" className="object-cover w-full h-full" />
                                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <Button variant="secondary" size="sm">Change Banner</Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm font-semibold">Contact Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="email">Public Contact Email</Label>
                                        <div className="relative">
                                            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                            <Input id="email" className="pl-9" defaultValue="contact@gourmetdelights.com" />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="phone">Public Phone Number</Label>
                                        <div className="relative">
                                            <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                            <Input id="phone" className="pl-9" defaultValue="(555) 123-4567" />
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="website">Website URL</Label>
                                    <div className="relative">
                                        <Globe className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input id="website" className="pl-9" defaultValue="https://gourmetdelights.com" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="location" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-primary">
                                    <MapPin className="h-5 w-5" />
                                    Store Location
                                </CardTitle>
                                <CardDescription>Where should customers go to redeem their deals?</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="address">Street Address</Label>
                                    <Input id="address" defaultValue="123 Main St" />
                                </div>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="city">City</Label>
                                        <Input id="city" defaultValue="New York" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="state">State</Label>
                                        <Input id="state" defaultValue="NY" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="zip">ZIP Code</Label>
                                        <Input id="zip" defaultValue="10001" />
                                    </div>
                                </div>

                                <Separator />

                                <div className="space-y-2">
                                    <Label>Map Integration</Label>
                                    <div className="h-64 rounded-lg bg-muted border-2 border-dashed flex flex-col items-center justify-center text-muted-foreground">
                                        <MapPin className="h-10 w-10 mb-2 opacity-20" />
                                        <p>Map Preview Placeholder</p>
                                        <Button variant="link" size="sm">Adjust Coordinates</Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="hours" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-primary">
                                    <Clock className="h-5 w-5" />
                                    Operating Hours
                                </CardTitle>
                                <CardDescription>Set your regular business hours for deal redemptions.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map((day) => (
                                    <div key={day} className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3 border rounded-lg">
                                        <span className="font-semibold min-w-[100px]">{day}</span>
                                        <div className="flex items-center gap-4">
                                            <div className="flex items-center gap-2 uppercase font-mono text-sm">
                                                <Input className="w-24 h-8" defaultValue="09:00" />
                                                <span>TO</span>
                                                <Input className="w-24 h-8" defaultValue="21:00" />
                                            </div>
                                            <Badge variant="outline" className="text-[10px] hidden sm:block">OPEN</Badge>
                                        </div>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="social" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-primary">
                                    <Instagram className="h-5 w-5" />
                                    Social Connectivity
                                </CardTitle>
                                <CardDescription>Link your profiles to grow your following.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-4">
                                    <div className="flex items-center gap-4">
                                        <Instagram className="h-5 w-5 text-pink-600" />
                                        <Input placeholder="Instagram Username" defaultValue="@gourmetdelights" />
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <Facebook className="h-5 w-5 text-blue-600" />
                                        <Input placeholder="Facebook Page URL" defaultValue="facebook.com/gourmetdelights" />
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <Twitter className="h-5 w-5 text-sky-500" />
                                        <Input placeholder="Twitter Handle" defaultValue="@gourmet_nyc" />
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter className="bg-muted/30 border-t flex justify-between">
                                <Button variant="outline" size="sm">
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Another Platform
                                </Button>
                            </CardFooter>
                        </Card>
                    </TabsContent>
                </form>
            </Tabs>
        </div>
    );
};

VendorSettings.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorSettings;
