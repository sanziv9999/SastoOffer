import { useState, useEffect, useRef } from 'react';
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
    Plus,
    Navigation
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { MapContainer, TileLayer, Marker, useMapEvents, useMap } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-geosearch/dist/geosearch.css';
import L from 'leaflet';
import { GeoSearchControl, OpenStreetMapProvider } from 'leaflet-geosearch';

// Fix for default Leaflet markers in React
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});
import DashboardLayout from '@/layouts/DashboardLayout';
import { toast } from 'sonner';

const VendorSettings = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [mapPosition, setMapPosition] = useState<[number, number]>([27.7172, 85.3240]); // Default to Kathmandu
    const mapRef = useRef<L.Map | null>(null);
    const [addressDetails, setAddressDetails] = useState({
        province: 'bagmati',
        district: 'Kathmandu',
        municipality: 'Kathmandu Metropolitan City',
        tole: 'Thamel',
        ward: '1'
    });

    const fetchAddressDetails = async (lat: number, lng: number) => {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
            const data = await response.json();

            if (data && data.address) {
                const addr = data.address;

                let district = addr.city_district || addr.county || addr.state_district || 'Kathmandu';
                let municipality = addr.city || addr.town || addr.municipality || 'Kathmandu Metropolitan City';
                const tole = addr.suburb || addr.neighbourhood || addr.road || 'Thamel';

                // Province mapping (OSM often returns Nepali translations like "बागमती प्रदेश")
                let province = 'bagmati';
                const stateStr = (addr.state || '').toLowerCase();
                if (stateStr.includes('koshi') || stateStr.includes('कोशी')) province = 'koshi';
                else if (stateStr.includes('madhesh') || stateStr.includes('मधेश')) province = 'madhesh';
                else if (stateStr.includes('bagmati') || stateStr.includes('बागमती')) province = 'bagmati';
                else if (stateStr.includes('gandaki') || stateStr.includes('गण्डकी')) province = 'gandaki';
                else if (stateStr.includes('lumbini') || stateStr.includes('लुम्बिनी')) province = 'lumbini';
                else if (stateStr.includes('karnali') || stateStr.includes('कर्णाली')) province = 'karnali';
                else if (stateStr.includes('sudurpashchim') || stateStr.includes('सुदूरपश्चिम')) province = 'sudurpashchim';

                // Try to extract ward number from district/municipality strings like "Kathmandu-01" or "वडा नं १"
                let ward = addressDetails.ward; // keep existing if not found

                // Common pattern in OSM for Nepal: city_district contains "CityName-WardNumber" (e.g., Kathmandu-01)
                if (addr.city_district) {
                    const match = addr.city_district.match(/-(\d+)$/);
                    if (match) {
                        ward = parseInt(match[1], 10).toString(); // removes leading zeros
                        district = addr.county || 'Kathmandu'; // if city_district was actually the ward, fallback to county for district
                    }
                }

                // Clean up devanagari numbers or strings if you prefer, but standard parseInt helps with English digits

                setAddressDetails(prev => ({
                    ...prev,
                    district: district,
                    municipality: municipality,
                    tole: tole,
                    province: province,
                    ward: ward
                }));
            }
        } catch (error) {
            console.error("Error fetching address details:", error);
        }
    };

    function LocationPicker() {
        const map = useMap();

        useEffect(() => {
            const provider = new OpenStreetMapProvider();
            const searchControl = new (GeoSearchControl as any)({
                provider: provider,
                style: 'bar',
                showMarker: false,
                autoClose: true,
                retainZoomLevel: false,
                animateZoom: true,
                keepResult: true,
                searchLabel: 'Enter address to search'
            });

            map.addControl(searchControl);
            return () => {
                map.removeControl(searchControl);
            };
        }, [map]);

        useMapEvents({
            click(e: any) {
                setMapPosition([e.latlng.lat, e.latlng.lng]);
                fetchAddressDetails(e.latlng.lat, e.latlng.lng);
            },
            geosearch_showlocation(e: any) {
                if (e.location && e.location.y && e.location.x) {
                    setMapPosition([e.location.y, e.location.x]);
                    fetchAddressDetails(e.location.y, e.location.x);
                }
            }
        } as any);

        return mapPosition ? <Marker position={mapPosition} /> : null;
    }

    const handleLocateMe = (e: React.MouseEvent) => {
        e.preventDefault();
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    setMapPosition([lat, lng]);
                    fetchAddressDetails(lat, lng);
                    if (mapRef.current) {
                        mapRef.current.flyTo([lat, lng], 16);
                    }
                    toast.success("Location found!");
                },
                (error) => {
                    console.error("Error asking for location", error);
                    toast.error("Please allow location access to use this feature.");
                }
            );
        } else {
            toast.error("Geolocation is not supported by your browser");
        }
    };

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setTimeout(() => {
            setIsLoading(false);
            toast.success('Business profile updated successfully!');
        }, 1500);
    };

    return (
        <div className="space-y-6">
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
                                    <div className="space-y-2">
                                        <Label>Business Type</Label>
                                        <Select defaultValue="hybrid">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select business type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="services">Services</SelectItem>
                                                <SelectItem value="product">Product</SelectItem>
                                                <SelectItem value="hybrid">Hybrid</SelectItem>
                                            </SelectContent>
                                        </Select>
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
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="province">Province</Label>
                                        <Select
                                            value={addressDetails.province}
                                            onValueChange={(val) => setAddressDetails(prev => ({ ...prev, province: val }))}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select province" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="koshi">Koshi</SelectItem>
                                                <SelectItem value="madhesh">Madhesh</SelectItem>
                                                <SelectItem value="bagmati">Bagmati</SelectItem>
                                                <SelectItem value="gandaki">Gandaki</SelectItem>
                                                <SelectItem value="lumbini">Lumbini</SelectItem>
                                                <SelectItem value="karnali">Karnali</SelectItem>
                                                <SelectItem value="sudurpashchim">Sudurpashchim</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="district">District</Label>
                                        <Input
                                            id="district"
                                            placeholder="e.g. Kathmandu"
                                            value={addressDetails.district}
                                            onChange={(e) => setAddressDetails(prev => ({ ...prev, district: e.target.value }))}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="local_level">Municipality / Rural Municipality</Label>
                                        <Input
                                            id="local_level"
                                            placeholder="e.g. Kathmandu Metropolitan City"
                                            value={addressDetails.municipality}
                                            onChange={(e) => setAddressDetails(prev => ({ ...prev, municipality: e.target.value }))}
                                        />
                                    </div>
                                    <div className="space-y-2 flex gap-4">
                                        <div className="flex-1 space-y-2">
                                            <Label htmlFor="ward">Ward No.</Label>
                                            <Input
                                                id="ward"
                                                type="number"
                                                placeholder="e.g. 1"
                                                value={addressDetails.ward}
                                                onChange={(e) => setAddressDetails(prev => ({ ...prev, ward: e.target.value }))}
                                            />
                                        </div>
                                        <div className="flex-[2] space-y-2">
                                            <Label htmlFor="tole">Tole / Street</Label>
                                            <Input
                                                id="tole"
                                                placeholder="e.g. Thamel"
                                                value={addressDetails.tole}
                                                onChange={(e) => setAddressDetails(prev => ({ ...prev, tole: e.target.value }))}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <Label>Pin Location on Map</Label>
                                        <Button type="button" variant="outline" size="sm" onClick={handleLocateMe} className="gap-2">
                                            <Navigation className="h-4 w-4" />
                                            Use Current Location
                                        </Button>
                                    </div>
                                    <div className="h-[350px] w-full rounded-lg overflow-hidden border z-10 relative">
                                        <MapContainer
                                            center={mapPosition}
                                            zoom={13}
                                            scrollWheelZoom={true}
                                            style={{ height: '100%', width: '100%' }}
                                            ref={mapRef}
                                        >
                                            <TileLayer
                                                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                            />
                                            <LocationPicker />
                                        </MapContainer>
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">
                                        Click on the map to place the marker exactly at your business location.
                                        Current Coordinates: {mapPosition[0].toFixed(5)}, {mapPosition[1].toFixed(5)}
                                    </p>
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
