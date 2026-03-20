import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link, router } from '@inertiajs/react';
import { CalendarDays, Clock, ExternalLink, Globe, Mail, MapPin, Phone, ShieldCheck, User } from 'lucide-react';

const AdminVendorView = ({ vendor }: { vendor: any }) => {
  const formatDate = (value?: string | null) => {
    if (!value) return 'N/A';
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? 'N/A' : d.toLocaleString();
  };

  const getCategoryLabel = () => {
    const parent = vendor?.category?.parent?.name;
    const name = vendor?.category?.name;
    if (parent && name) return `${parent} / ${name}`;
    return name || parent || 'Uncategorized';
  };

  const updateVerifiedStatus = (verified_status: string) => {
    router.patch(
      `/admin/vendors/${vendor.id}/verified-status`,
      { verified_status },
      { preserveScroll: true, preserveState: true, replace: true },
    );
  };

  const address = vendor?.default_address;
  const addressLabel = address
    ? [address.tole, address.ward_no ? `Ward ${address.ward_no}` : null, address.municipality, address.district, address.province]
        .filter(Boolean)
        .join(', ')
    : 'N/A';
  const socials = Array.isArray(vendor?.social_media) ? vendor.social_media : [];
  const businessHours = Array.isArray(vendor?.business_hours) ? vendor.business_hours : [];
  const status = String(vendor?.verified_status || 'pending').toLowerCase();
  const statusClass =
    status === 'verified'
      ? 'bg-green-600 text-white hover:bg-green-600'
      : status === 'rejected'
        ? 'bg-red-600 text-white hover:bg-red-600'
        : status === 'suspended'
          ? 'bg-gray-700 text-white hover:bg-gray-700'
          : 'bg-amber-500 text-white hover:bg-amber-500';
  const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
  const latitude = address?.latitude !== null && address?.latitude !== undefined ? Number(address.latitude) : null;
  const longitude = address?.longitude !== null && address?.longitude !== undefined ? Number(address.longitude) : null;
  const hasCoordinates =
    latitude !== null &&
    longitude !== null &&
    Number.isFinite(latitude) &&
    Number.isFinite(longitude);
  const mapEmbedSrc = hasCoordinates
    ? `https://www.openstreetmap.org/export/embed.html?bbox=${longitude - 0.01}%2C${latitude - 0.01}%2C${longitude + 0.01}%2C${latitude + 0.01}&layer=mapnik&marker=${latitude}%2C${longitude}`
    : null;
  const mapOpenUrl = hasCoordinates
    ? `https://www.openstreetmap.org/?mlat=${latitude}&mlon=${longitude}#map=16/${latitude}/${longitude}`
    : null;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight">{vendor.business_name}</h1>
          <p className="text-muted-foreground">Full vendor profile review</p>
        </div>
        <Button asChild variant="outline">
          <Link href="/admin/vendors">Back</Link>
        </Button>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="lg:col-span-2 overflow-hidden">
          <div className="h-40 w-full bg-muted">
            {vendor?.cover ? (
              <img src={vendor.cover} alt="Vendor cover" className="h-full w-full object-cover" />
            ) : (
              <div className="h-full w-full flex items-center justify-center text-sm text-muted-foreground">No cover image</div>
            )}
          </div>
          <CardContent className="pt-4">
            <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
              <div className="flex items-start gap-4">
                <div className="h-16 w-16 rounded-xl border bg-muted overflow-hidden shrink-0">
                  {vendor?.logo ? (
                    <img src={vendor.logo} alt="Vendor logo" className="h-full w-full object-cover" />
                  ) : (
                    <div className="h-full w-full flex items-center justify-center text-xl font-semibold text-muted-foreground">
                      {vendor?.business_name?.charAt(0) || 'V'}
                    </div>
                  )}
                </div>
                <div className="space-y-1">
                  <div className="text-xl font-semibold">{vendor?.business_name || 'N/A'}</div>
                  <div className="text-sm text-muted-foreground">Slug: {vendor?.slug || 'N/A'}</div>
                  <div className="flex flex-wrap items-center gap-2 pt-1">
                    <Badge variant="outline">{getCategoryLabel()}</Badge>
                    <Badge className={statusClass}>{statusLabel}</Badge>
                    <Badge className={vendor?.is_profile_complete ? 'bg-green-600 text-white hover:bg-green-600' : 'bg-red-600 text-white hover:bg-red-600'}>
                      {vendor?.is_profile_complete ? 'Profile Complete' : 'Profile Incomplete'}
                    </Badge>
                  </div>
                </div>
              </div>
              <div className="text-sm text-muted-foreground">
                <div>Verified at: <span className="font-medium text-foreground">{formatDate(vendor?.verified_at)}</span></div>
                <div>Verified by: <span className="font-medium text-foreground">{vendor?.verified_by?.name || 'N/A'}</span></div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Verification Controls</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1">
              <div className="text-sm text-muted-foreground">Current status</div>
              <Badge className={statusClass}>{statusLabel}</Badge>
            </div>
            <div className="space-y-2">
              <div className="text-sm text-muted-foreground">Change status</div>
              <Select value={vendor.verified_status || 'pending'} onValueChange={updateVerifiedStatus}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="verified">Verified</SelectItem>
                  <SelectItem value="rejected">Rejected</SelectItem>
                  <SelectItem value="suspended">Suspended</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Business Details</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-1">
                <div className="text-sm text-muted-foreground">Business name</div>
                <div className="font-medium">{vendor?.business_name || 'N/A'}</div>
              </div>
              <div className="space-y-1">
                <div className="text-sm text-muted-foreground">Slug</div>
                <div className="font-medium">{vendor?.slug || 'N/A'}</div>
              </div>
              <div className="space-y-1">
                <div className="text-sm text-muted-foreground">Business type</div>
                <div className="font-medium capitalize">{vendor?.business_type || 'N/A'}</div>
              </div>
              <div className="space-y-1">
                <div className="text-sm text-muted-foreground">Category</div>
                <div className="font-medium">{getCategoryLabel()}</div>
              </div>
              <div className="space-y-1 md:col-span-2">
                <div className="text-sm text-muted-foreground">Description</div>
                <div className="text-sm whitespace-pre-wrap">{vendor?.description || 'N/A'}</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Address & Map</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-start gap-3">
              <MapPin className="h-4 w-4 mt-1 text-muted-foreground" />
              <div className="space-y-1">
                <div className="text-sm text-muted-foreground">Full address</div>
                <div className="font-medium">{addressLabel}</div>
                <div className="text-xs text-muted-foreground">
                  Lat: {latitude ?? 'N/A'} · Lng: {longitude ?? 'N/A'}
                </div>
              </div>
            </div>

            {hasCoordinates && mapEmbedSrc ? (
              <div className="space-y-3">
                <div className="h-[280px] w-full overflow-hidden rounded-md border">
                  <iframe
                    title="Vendor location map"
                    width="100%"
                    height="100%"
                    className="border-0"
                    loading="lazy"
                    src={mapEmbedSrc}
                  />
                </div>
                {mapOpenUrl && (
                  <a
                    href={mapOpenUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1 text-sm text-primary underline"
                  >
                    Open in OpenStreetMap <ExternalLink className="h-3.5 w-3.5" />
                  </a>
                )}
              </div>
            ) : (
              <div className="rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                Location coordinates are missing for this vendor.
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Owner & Contact</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-start gap-3">
              <User className="h-4 w-4 mt-1 text-muted-foreground" />
              <div>
                <div className="text-sm text-muted-foreground">Owner</div>
                <div className="font-medium">{vendor?.user?.name || 'N/A'}</div>
                <div className="text-sm text-muted-foreground">{vendor?.user?.email || 'N/A'}</div>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Mail className="h-4 w-4 mt-1 text-muted-foreground" />
              <div>
                <div className="text-sm text-muted-foreground">Public email</div>
                <div className="font-medium">{vendor?.public_email || 'N/A'}</div>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Phone className="h-4 w-4 mt-1 text-muted-foreground" />
              <div>
                <div className="text-sm text-muted-foreground">Public phone</div>
                <div className="font-medium">{vendor?.public_phone || 'N/A'}</div>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Globe className="h-4 w-4 mt-1 text-muted-foreground" />
              <div>
                <div className="text-sm text-muted-foreground">Website</div>
                {vendor?.website_url ? (
                  <a className="font-medium text-primary underline" href={vendor.website_url} target="_blank" rel="noreferrer">
                    {vendor.website_url}
                  </a>
                ) : (
                  <div className="font-medium">N/A</div>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Media</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <div className="text-xs text-muted-foreground mb-2">Logo</div>
              <div className="h-24 w-24 rounded border bg-muted overflow-hidden">
                {vendor?.logo ? (
                  <img src={vendor.logo} alt="Vendor logo" className="h-full w-full object-cover" />
                ) : (
                  <div className="h-full w-full flex items-center justify-center text-xs text-muted-foreground">No logo</div>
                )}
              </div>
            </div>
            <div>
              <div className="text-xs text-muted-foreground mb-2">Cover</div>
              <div className="h-28 w-full rounded border bg-muted overflow-hidden">
                {vendor?.cover ? (
                  <img src={vendor.cover} alt="Vendor cover" className="h-full w-full object-cover" />
                ) : (
                  <div className="h-full w-full flex items-center justify-center text-xs text-muted-foreground">No cover</div>
                )}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Social Media</CardTitle>
          </CardHeader>
          <CardContent>
            {socials.length > 0 ? (
              <div className="space-y-3">
                {socials.map((item: any, idx: number) => (
                  <div key={`${item?.platform || 'social'}-${idx}`} className="flex items-start justify-between gap-3 border rounded-md p-3">
                    <div>
                      <div className="text-sm text-muted-foreground capitalize">{item?.platform || 'Platform'}</div>
                      <div className="font-medium break-all">{item?.url || 'N/A'}</div>
                    </div>
                    {item?.url ? (
                      <a href={item.url} target="_blank" rel="noreferrer" className="text-xs text-primary underline">
                        Open
                      </a>
                    ) : null}
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-sm text-muted-foreground">No social media links provided.</div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Business Hours</CardTitle>
          </CardHeader>
          <CardContent>
            {businessHours.length > 0 ? (
              <div className="space-y-2">
                {businessHours.map((hour: any, idx: number) => (
                  <div key={`${hour?.day || 'day'}-${idx}`} className="flex items-center justify-between border rounded-md px-3 py-2">
                    <div className="flex items-center gap-2">
                      <Clock className="h-4 w-4 text-muted-foreground" />
                      <span className="font-medium">{hour?.day || 'Day'}</span>
                    </div>
                    <span className="text-sm text-muted-foreground">
                      {hour?.is_closed ? 'Closed' : `${hour?.open || '--:--'} - ${hour?.close || '--:--'}`}
                    </span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-sm text-muted-foreground">Business hours not provided.</div>
            )}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Audit Metadata</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="space-y-1">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4" />
                Created at
              </div>
              <div className="font-medium">{formatDate(vendor?.created_at)}</div>
            </div>

            <div className="space-y-1">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4" />
                Updated at
              </div>
              <div className="font-medium">{formatDate(vendor?.updated_at)}</div>
            </div>

            <div className="space-y-1">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <ShieldCheck className="h-4 w-4" />
                Verified by (email)
              </div>
              <div className="font-medium">{vendor?.verified_by?.email || 'N/A'}</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

AdminVendorView.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default AdminVendorView;

