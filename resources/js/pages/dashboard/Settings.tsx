
import { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { User, Bell, Shield, MapPin } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import DashboardLayout from '@/layouts/DashboardLayout';
import { useAuth } from '@/context/AuthContext';
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

const Settings = () => {
  const { user } = useAuth();
  // Try to read default address from Inertia props when available
  let defaultAddress: any = null;
  try {
    const page = usePage<any>();
    defaultAddress = page.props.defaultAddress || null;
  } catch (e) {
    defaultAddress = null;
  }

  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [phone, setPhone] = useState(user?.phone || '');
  const [city, setCity] = useState(user?.city || '');

  const [province, setProvince] = useState(defaultAddress?.province || 'bagmati');
  const [district, setDistrict] = useState(defaultAddress?.district || '');
  const [municipality, setMunicipality] = useState(defaultAddress?.municipality || '');
  const [wardNo, setWardNo] = useState(defaultAddress?.ward_no || '');
  const [tole, setTole] = useState(defaultAddress?.tole || '');
  const [savingAddress, setSavingAddress] = useState(false);

  const [latitude, setLatitude] = useState<number>(defaultAddress?.latitude ?? 27.7172);
  const [longitude, setLongitude] = useState<number>(defaultAddress?.longitude ?? 85.3240);
  const [mapPosition, setMapPosition] = useState<[number, number]>([latitude, longitude]);
  const mapRef = useRef<L.Map | null>(null);

  const handleSaveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    // router.post('/dashboard/settings/profile', { name, email, phone, city });
    toast.success('Profile updated successfully!');
  };

  const handleSaveAddress = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingAddress(true);
    try {
      router.post(
        '/dashboard/settings/address',
        {
          province,
          district,
          municipality,
          ward_no: wardNo,
          tole,
          latitude,
          longitude,
          is_default: true,
          label: 'Home',
        },
        {
          preserveScroll: true,
          onSuccess: () => toast.success('Address saved successfully!'),
          onError: (errors: any) => {
            const first = errors ? Object.values(errors)[0] : null;
            toast.error((first as string) || 'Failed to save address.');
          },
          onFinish: () => setSavingAddress(false),
        }
      );
    } catch (error) {
      console.error(error);
      toast.error('Something went wrong while saving address.');
    }
  };

  const fetchAddressDetails = async (lat: number, lng: number) => {
    try {
      const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
      const addrData = await response.json();

      if (addrData && addrData.address) {
        const addr = addrData.address;

        let nextDistrict = addr.city_district || addr.county || addr.state_district || district || 'Kathmandu';
        let nextMunicipality = addr.city || addr.town || addr.municipality || municipality || 'Kathmandu Metropolitan City';
        const nextTole = addr.suburb || addr.neighbourhood || addr.road || tole || 'Thamel';

        // Province mapping
        let nextProvince = province || 'bagmati';
        const stateStr = (addr.state || '').toLowerCase();
        if (stateStr.includes('koshi') || stateStr.includes('कोशी')) nextProvince = 'koshi';
        else if (stateStr.includes('madhesh') || stateStr.includes('मधेश')) nextProvince = 'madhesh';
        else if (stateStr.includes('bagmati') || stateStr.includes('बागमती')) nextProvince = 'bagmati';
        else if (stateStr.includes('gandaki') || stateStr.includes('गण्डकी')) nextProvince = 'gandaki';
        else if (stateStr.includes('lumbini') || stateStr.includes('लुम्बिनी')) nextProvince = 'lumbini';
        else if (stateStr.includes('karnali') || stateStr.includes('कर्णाली')) nextProvince = 'karnali';
        else if (stateStr.includes('sudurpashchim') || stateStr.includes('सुदूरपश्चिम')) nextProvince = 'sudurpashchim';

        let nextWard = wardNo;
        if (addr.city_district) {
          const match = addr.city_district.match(/-(\d+)$/);
          if (match) {
            nextWard = parseInt(match[1], 10).toString();
            nextDistrict = addr.county || nextDistrict || 'Kathmandu';
          }
        }

        setProvince(nextProvince);
        setDistrict(nextDistrict);
        setMunicipality(nextMunicipality);
        setTole(nextTole);
        setWardNo(nextWard);
      }
    } catch (error) {
      console.error('Error fetching address details:', error);
    }
  };

  function LocationPicker() {
    const map = useMap();

    useEffect(() => {
      const provider = new OpenStreetMapProvider();
      const searchControl = new (GeoSearchControl as any)({
        provider,
        style: 'bar',
        showMarker: false,
        autoClose: true,
        retainZoomLevel: false,
        animateZoom: true,
        keepResult: true,
        searchLabel: 'Enter address to search',
      });

      map.addControl(searchControl);
      return () => {
        map.removeControl(searchControl);
      };
    }, [map]);

    useMapEvents({
      click(e: any) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        setMapPosition([lat, lng]);
        setLatitude(lat);
        setLongitude(lng);
        fetchAddressDetails(lat, lng);
      },
      geosearch_showlocation(e: any) {
        if (e.location && e.location.y && e.location.x) {
          const lat = e.location.y;
          const lng = e.location.x;
          setMapPosition([lat, lng]);
          setLatitude(lat);
          setLongitude(lng);
          fetchAddressDetails(lat, lng);
        }
      },
    } as any);

    return mapPosition ? <Marker position={mapPosition} /> : null;
  }

  const handleLocateMe = (e: React.MouseEvent) => {
    e.preventDefault();
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          setMapPosition([lat, lng]);
          setLatitude(lat);
          setLongitude(lng);
          fetchAddressDetails(lat, lng);
          if (mapRef.current) {
            mapRef.current.flyTo([lat, lng], 16);
          }
          toast.success('Location found!');
        },
        (error) => {
          console.error('Error asking for location', error);
          toast.error('Please allow location access to use this feature.');
        }
      );
    } else {
      toast.error('Geolocation is not supported by your browser');
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Settings</h1>
        <p className="text-muted-foreground">Manage your account preferences</p>
      </div>

      <Tabs defaultValue="profile" className="space-y-4">
        <TabsList>
          <TabsTrigger value="profile"><User className="h-4 w-4 mr-2" />Profile</TabsTrigger>
          <TabsTrigger value="notifications"><Bell className="h-4 w-4 mr-2" />Notifications</TabsTrigger>
          <TabsTrigger value="security"><Shield className="h-4 w-4 mr-2" />Security</TabsTrigger>
          <TabsTrigger value="address"><MapPin className="h-4 w-4 mr-2" />Address</TabsTrigger>
        </TabsList>

        <TabsContent value="profile">
          <Card>
            <CardHeader>
              <CardTitle>Profile Information</CardTitle>
              <CardDescription>Update your personal details</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSaveProfile} className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor="name">Full Name</Label>
                    <Input id="name" value={name} onChange={e => setName(e.target.value)} />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" value={email} onChange={e => setEmail(e.target.value)} />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone">Phone</Label>
                    <Input id="phone" value={phone} onChange={e => setPhone(e.target.value)} placeholder="+1 (555) 000-0000" />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="city">City</Label>
                    <Input id="city" value={city} onChange={e => setCity(e.target.value)} placeholder="Your city" />
                  </div>
                </div>
                <Button type="submit">Save Changes</Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="notifications">
          <Card>
            <CardHeader>
              <CardTitle>Notification Preferences</CardTitle>
              <CardDescription>Choose what notifications you receive</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {[
                { label: 'Deal Alerts', desc: 'Get notified about new deals near you' },
                { label: 'Coupon Reminders', desc: 'Reminders before your coupons expire' },
                { label: 'Price Drops', desc: 'Alerts when prices drop on saved deals' },
                { label: 'Weekly Digest', desc: 'Weekly summary of best deals' },
              ].map(item => (
                <div key={item.label} className="flex items-center justify-between p-3 border rounded-lg">
                  <div>
                    <p className="font-medium">{item.label}</p>
                    <p className="text-sm text-muted-foreground">{item.desc}</p>
                  </div>
                  <Switch defaultChecked />
                </div>
              ))}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="security">
          <Card>
            <CardHeader>
              <CardTitle>Security Settings</CardTitle>
              <CardDescription>Manage your password and security options</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="current-password">Current Password</Label>
                <Input id="current-password" type="password" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="new-password">New Password</Label>
                <Input id="new-password" type="password" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="confirm-password">Confirm New Password</Label>
                <Input id="confirm-password" type="password" />
              </div>
              <Button onClick={() => toast.success('Password updated!')}>Update Password</Button>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="address">
          <Card>
            <CardHeader>
              <CardTitle>Address</CardTitle>
              <CardDescription>Set your default address and pin it on the map</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSaveAddress} className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor="province">Province</Label>
                    <Select value={province} onValueChange={setProvince}>
                      <SelectTrigger id="province">
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
                      value={district}
                      onChange={e => setDistrict(e.target.value)}
                      placeholder="e.g. Kathmandu"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="municipality">Municipality / Rural Municipality</Label>
                    <Input
                      id="municipality"
                      value={municipality}
                      onChange={e => setMunicipality(e.target.value)}
                      placeholder="e.g. Kathmandu Metropolitan City"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="ward">Ward No.</Label>
                    <Input
                      id="ward"
                      value={wardNo}
                      onChange={e => setWardNo(e.target.value)}
                      placeholder="e.g. 1"
                    />
                  </div>
                  <div className="space-y-2 md:col-span-2">
                    <Label htmlFor="tole">Tole / Street</Label>
                    <Input
                      id="tole"
                      value={tole}
                      onChange={e => setTole(e.target.value)}
                      placeholder="e.g. Thamel"
                    />
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <Label>Pin Location on Map</Label>
                    <Button type="button" variant="outline" size="sm" onClick={handleLocateMe}>
                      Use Current Location
                    </Button>
                  </div>
                  <div className="h-[420px] w-full rounded-lg overflow-hidden border z-10 relative">
                    <MapContainer
                      center={mapPosition}
                      zoom={13}
                      scrollWheelZoom={true}
                      style={{ height: '100%', width: '100%' }}
                      ref={mapRef as any}
                    >
                      <TileLayer
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                      />
                      <LocationPicker />
                    </MapContainer>
                  </div>
                  <p className="text-xs text-muted-foreground mt-1">
                    Click on the map to place the marker at your address.
                    Current Coordinates: {Number(mapPosition[0]).toFixed(5)}, {Number(mapPosition[1]).toFixed(5)}
                  </p>
                </div>

                <Button type="submit" disabled={savingAddress}>
                  {savingAddress ? 'Saving...' : 'Save Address'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

Settings.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Settings;
