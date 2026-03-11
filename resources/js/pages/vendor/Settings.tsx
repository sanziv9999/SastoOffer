import { useState, useEffect, useRef } from 'react';
import { useForm } from '@inertiajs/react';
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

// Helper for route if Ziggy is not defined globally
declare var route: any;

const VendorSettings = ({ vendorProfile, primaryCategories }: { 
    vendorProfile: any, 
    primaryCategories: any[]
}) => {
    const { data, setData, post, processing, errors } = useForm({
        business_name: vendorProfile?.business_name || '',
        primary_category_id: vendorProfile?.primary_category_id || '',
        business_type: vendorProfile?.business_type || 'service',
        description: vendorProfile?.description || '',
        public_email: vendorProfile?.public_email || '',
        public_phone: vendorProfile?.public_phone || '',
        website_url: vendorProfile?.website_url || '',
        business_hours: Array.isArray(vendorProfile?.business_hours)
            ? vendorProfile.business_hours.map((h: any) => ({
                ...h,
                // Ensure React checkbox gets a real boolean, not "0"/"1"
                is_closed: h.is_closed === true || h.is_closed === 1 || h.is_closed === '1',
            }))
            : [
            { day: 'Sunday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Monday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Tuesday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Wednesday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Thursday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Friday', open: '09:00', close: '18:00', is_closed: false },
            { day: 'Saturday', open: '09:00', close: '18:00', is_closed: true }
        ],
        social_media: vendorProfile?.social_media || [
            { platform: 'Instagram', url: '' },
            { platform: 'Facebook', url: '' },
            { platform: 'Twitter', url: '' }
        ],
        province: vendorProfile?.default_address?.province || 'bagmati',
        district: vendorProfile?.default_address?.district || 'Kathmandu',
        municipality: vendorProfile?.default_address?.municipality || 'Kathmandu Metropolitan City',
        tole: vendorProfile?.default_address?.tole || 'Thamel',
        ward_no: vendorProfile?.default_address?.ward_no || '1',
        latitude: vendorProfile?.default_address?.latitude || 27.7172,
        longitude: vendorProfile?.default_address?.longitude || 85.3240,
        logo: null as File | null,
        cover: null as File | null,
        _method: 'put',
    });

    const [logoPreview, setLogoPreview] = useState<string | null>(
        vendorProfile?.images?.find((img: any) => img.attribute_name === 'logo')?.image_url || null
    );
    const [coverPreview, setCoverPreview] = useState<string | null>(
        vendorProfile?.images?.find((img: any) => img.attribute_name === 'cover')?.image_url || null
    );

    const logoInputRef = useRef<HTMLInputElement>(null);
    const coverInputRef = useRef<HTMLInputElement>(null);

    const [mapPosition, setMapPosition] = useState<[number, number]>([data.latitude, data.longitude]); 
    const mapRef = useRef<L.Map | null>(null);

    const fetchAddressDetails = async (lat: number, lng: number) => {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
            const addrData = await response.json();

            if (addrData && addrData.address) {
                const addr = addrData.address;

                let district = addr.city_district || addr.county || addr.state_district || 'Kathmandu';
                let municipality = addr.city || addr.town || addr.municipality || 'Kathmandu Metropolitan City';
                const tole = addr.suburb || addr.neighbourhood || addr.road || 'Thamel';

                // Province mapping
                let province = 'bagmati';
                const stateStr = (addr.state || '').toLowerCase();
                if (stateStr.includes('koshi') || stateStr.includes('कोशी')) province = 'koshi';
                else if (stateStr.includes('madhesh') || stateStr.includes('मधेश')) province = 'madhesh';
                else if (stateStr.includes('bagmati') || stateStr.includes('बागमती')) province = 'bagmati';
                else if (stateStr.includes('gandaki') || stateStr.includes('गण्डकी')) province = 'gandaki';
                else if (stateStr.includes('lumbini') || stateStr.includes('लुम्बिनी')) province = 'lumbini';
                else if (stateStr.includes('karnali') || stateStr.includes('कर्णाली')) province = 'karnali';
                else if (stateStr.includes('sudurpashchim') || stateStr.includes('सुदूरपश्चिम')) province = 'sudurpashchim';

                let ward = data.ward_no; 
                if (addr.city_district) {
                    const match = addr.city_district.match(/-(\d+)$/);
                    if (match) {
                        ward = parseInt(match[1], 10).toString();
                        district = addr.county || 'Kathmandu';
                    }
                }

                setData(prev => ({
                    ...prev,
                    district: district,
                    municipality: municipality,
                    tole: tole,
                    province: province,
                    ward_no: ward
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
                setData(prev => ({ ...prev, latitude: e.latlng.lat, longitude: e.latlng.lng }));
                fetchAddressDetails(e.latlng.lat, e.latlng.lng);
            },
            geosearch_showlocation(e: any) {
                if (e.location && e.location.y && e.location.x) {
                    setMapPosition([e.location.y, e.location.x]);
                    setData(prev => ({ ...prev, latitude: e.location.y, longitude: e.location.x }));
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
                    setData(prev => ({ ...prev, latitude: lat, longitude: lng }));
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

    const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('logo', file);
            setLogoPreview(URL.createObjectURL(file));
        }
    };

    const handleCoverChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('cover', file);
            setCoverPreview(URL.createObjectURL(file));
        }
    };

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        // Since we are uploading files, we must use POST and spoof PUT 
        post('/vendor/settings', {
            forceFormData: true,
            onSuccess: () => toast.success('Business profile updated successfully!'),
            onError: (errs: any) => {
                console.error("Validation Errors:", errs);
                toast.error('Check for errors in the form. ' + (Object.values(errs)[0] || ''));
            }
        });
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Business Profile</h1>
                    <p className="text-muted-foreground">Manage your public presence and business details.</p>
                </div>
                <Button onClick={handleSave} disabled={processing}>
                    {processing ? "Saving..." : "Save Changes"}
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
                                        <Input 
                                            id="businessName" 
                                            value={data.business_name} 
                                            onChange={e => setData('business_name', e.target.value)} 
                                        />
                                        {errors.business_name && <p className="text-xs text-red-500">{errors.business_name}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="category">Primary Category</Label>
                                        <Select 
                                            value={data.primary_category_id?.toString()} 
                                            onValueChange={val => setData('primary_category_id', val)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select Category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {primaryCategories?.map(cat => (
                                                    <SelectItem key={cat.id} value={cat.id.toString()}>{cat.name}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>Business Type</Label>
                                        <Select 
                                            value={data.business_type} 
                                            onValueChange={val => setData('business_type', val)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select business type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="service">Service</SelectItem>
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
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
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
                                                    {logoPreview ? (
                                                        <img src={logoPreview} alt="Logo" className="object-cover w-full h-full" />
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">No Logo</span>
                                                    )}
                                                </div>
                                                <input type="file" accept="image/*" className="hidden" ref={logoInputRef} onChange={handleLogoChange} />
                                                <Button variant="outline" size="sm" type="button" onClick={() => logoInputRef.current?.click()}>Change Logo</Button>
                                            </div>
                                            {errors.logo && <p className="text-xs text-red-500">{errors.logo}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs text-muted-foreground">Cover Photo (16:9 recommended)</Label>
                                            <div className="h-20 w-full rounded-lg bg-muted flex items-center justify-center overflow-hidden border relative group">
                                                {coverPreview ? (
                                                    <img src={coverPreview} alt="Cover" className="object-cover w-full h-full" />
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">No Cover Photo</span>
                                                )}
                                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <input type="file" accept="image/*" className="hidden" ref={coverInputRef} onChange={handleCoverChange} />
                                                    <Button variant="secondary" size="sm" type="button" onClick={() => coverInputRef.current?.click()}>Change Banner</Button>
                                                </div>
                                            </div>
                                            {errors.cover && <p className="text-xs text-red-500">{errors.cover}</p>}
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
                                            <Input id="email" className="pl-9" value={data.public_email} onChange={e => setData('public_email', e.target.value)} />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="phone">Public Phone Number</Label>
                                        <div className="relative">
                                            <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                            <Input id="phone" className="pl-9" value={data.public_phone} onChange={e => setData('public_phone', e.target.value)} />
                                        </div>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="website">Website URL</Label>
                                    <div className="relative">
                                        <Globe className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input id="website" className="pl-9" value={data.website_url} onChange={e => setData('website_url', e.target.value)} />
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
                                            value={data.province}
                                            onValueChange={(val) => setData('province', val)}
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
                                            value={data.district}
                                            onChange={(e) => setData('district', e.target.value)}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="local_level">Municipality / Rural Municipality</Label>
                                        <Input
                                            id="local_level"
                                            placeholder="e.g. Kathmandu Metropolitan City"
                                            value={data.municipality}
                                            onChange={(e) => setData('municipality', e.target.value)}
                                        />
                                    </div>
                                    <div className="space-y-2 flex gap-4">
                                        <div className="flex-1 space-y-2">
                                            <Label htmlFor="ward">Ward No.</Label>
                                            <Input
                                                id="ward"
                                                type="number"
                                                placeholder="e.g. 1"
                                                value={data.ward_no}
                                                onChange={(e) => setData('ward_no', e.target.value)}
                                            />
                                        </div>
                                        <div className="flex-[2] space-y-2">
                                            <Label htmlFor="tole">Tole / Street</Label>
                                            <Input
                                                id="tole"
                                                placeholder="e.g. Thamel"
                                                value={data.tole}
                                                onChange={(e) => setData('tole', e.target.value)}
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
                                    <div className="h-[437px] w-full rounded-lg overflow-hidden border z-10 relative">
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
                                        Current Coordinates: {Number(mapPosition[0]).toFixed(5)}, {Number(mapPosition[1]).toFixed(5)}
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
                                <div className="space-y-4">
                                    <div className="grid grid-cols-[100px_1fr_1fr_80px] gap-4 font-medium text-sm text-muted-foreground pb-2 border-b">
                                        <div>Day</div>
                                        <div>Open Time</div>
                                        <div>Close Time</div>
                                        <div>Closed?</div>
                                    </div>
                                    {data.business_hours.map((hour: any, index: number) => (
                                        <div key={index} className="grid grid-cols-[100px_1fr_1fr_80px] gap-4 items-center">
                                            <div className="text-sm font-medium">{hour.day}</div>
                                            <div>
                                                <Input 
                                                    type="time" 
                                                    value={hour.open || ''} 
                                                    disabled={hour.is_closed}
                                                    onChange={e => {
                                                        const newHours = [...data.business_hours];
                                                        newHours[index].open = e.target.value;
                                                        setData('business_hours', newHours);
                                                    }}
                                                />
                                            </div>
                                            <div>
                                                <Input 
                                                    type="time" 
                                                    value={hour.close || ''} 
                                                    disabled={hour.is_closed}
                                                    onChange={e => {
                                                        const newHours = [...data.business_hours];
                                                        newHours[index].close = e.target.value;
                                                        setData('business_hours', newHours);
                                                    }}
                                                />
                                            </div>
                                            <div className="flex justify-center">
                                                <input 
                                                    type="checkbox" 
                                                    className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                    checked={hour.is_closed}
                                                    onChange={e => {
                                                        const newHours = [...data.business_hours];
                                                        newHours[index].is_closed = e.target.checked;
                                                        if (e.target.checked) {
                                                            newHours[index].open = '';
                                                            newHours[index].close = '';
                                                        } else {
                                                            newHours[index].open = '09:00';
                                                            newHours[index].close = '18:00';
                                                        }
                                                        setData('business_hours', newHours);
                                                    }}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                    {errors.business_hours && <p className="text-xs text-red-500 mt-2">{errors.business_hours}</p>}
                                </div>
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
                                    {data.social_media?.map((social: any, index: number) => (
                                        <div key={index} className="flex items-center gap-4">
                                            {social.platform === 'Instagram' && <Instagram className="h-5 w-5 text-pink-600" />}
                                            {social.platform === 'Facebook' && <Facebook className="h-5 w-5 text-blue-600" />}
                                            {social.platform === 'Twitter' && <Twitter className="h-5 w-5 text-sky-500" />}
                                            <Input
                                                placeholder={`${social.platform} URL/Username`}
                                                value={social.url}
                                                onChange={e => {
                                                    const newSocial = [...data.social_media];
                                                    newSocial[index].url = e.target.value;
                                                    setData('social_media', newSocial);
                                                }}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                            <CardFooter className="bg-muted/30 border-t flex justify-between">
                                <Button variant="outline" size="sm" type="button">
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
