import { useState } from 'react';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/react';
import { ArrowUp, ArrowDown, Search } from 'lucide-react';

type Props = {
  featuredDeals: Array<{
    id: number;
    title: string;
    vendorName?: string | null;
    rank: number | null;
    discountedPrice?: number | null;
    originalPrice?: number | null;
    image?: string | null;
  }>;
  filters?: { search?: string; maxRank?: number };
};

const FeaturedRanking = ({ featuredDeals, filters }: Props) => {
  const [searchTerm, setSearchTerm] = useState(filters?.search || '');
  const maxRank = filters?.maxRank || 1;

  const onSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/admin/featured-ranking', { search: searchTerm || undefined }, { preserveState: true, replace: true });
  };

  const move = (dealId: number, direction: 'up' | 'down') => {
    router.post(`/admin/featured-ranking/${dealId}/move`, { direction }, { preserveScroll: true });
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Featured Deal Ranking</h1>
        <p className="text-muted-foreground">Only featured deals appear here. Use Up/Down to reorder.</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle>Featured deals</CardTitle>
              <CardDescription>{featuredDeals?.length || 0} featured deals</CardDescription>
            </div>
            <form onSubmit={onSearch} className="flex w-full md:w-auto">
              <Input placeholder="Search featured..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="md:w-80 rounded-r-none" />
              <Button type="submit" size="icon" className="rounded-l-none"><Search className="h-4 w-4" /></Button>
            </form>
          </div>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border">
            <div className="relative w-full overflow-auto">
              <table className="w-full caption-bottom text-sm">
                <thead className="border-b">
                  <tr>
                    <th className="h-12 px-4 text-left align-middle font-medium">Deal</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Vendor</th>
                    <th className="h-12 px-4 text-left align-middle font-medium">Rank</th>
                    <th className="h-12 px-4 text-right align-middle font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {featuredDeals.length ? featuredDeals.map((d) => (
                    <tr key={d.id} className="border-b transition-colors hover:bg-muted/50">
                      <td className="p-4 align-middle">
                        <div className="flex items-center gap-3">
                          {d.image && <img src={d.image} alt={d.title} className="h-10 w-10 rounded object-cover" />}
                          <div className="min-w-0">
                            <div className="font-medium truncate">{d.title}</div>
                            <div className="text-xs text-muted-foreground">ID: {d.id}</div>
                          </div>
                        </div>
                      </td>
                      <td className="p-4 align-middle">{d.vendorName || 'Unknown'}</td>
                      <td className="p-4 align-middle">
                        <Badge variant="outline">{d.rank ?? '-'}</Badge>
                      </td>
                      <td className="p-4 align-middle text-right">
                        <div className="flex justify-end gap-2">
                          <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => move(d.id, 'up')}
                            disabled={d.rank != null && d.rank <= 1}
                            title="Move up"
                          >
                            <ArrowUp className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="outline"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => move(d.id, 'down')}
                            disabled={d.rank != null && d.rank >= maxRank}
                            title="Move down"
                          >
                            <ArrowDown className="h-4 w-4" />
                          </Button>
                        </div>
                      </td>
                    </tr>
                  )) : (
                    <tr>
                      <td colSpan={4} className="p-8 text-center text-muted-foreground">No featured deals found.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

FeaturedRanking.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default FeaturedRanking;

