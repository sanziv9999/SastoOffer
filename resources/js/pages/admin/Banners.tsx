import DashboardLayout from '@/layouts/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
  banners: any[];
};

const Banners = ({ banners }: Props) => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight">Banners</h1>
        <p className="text-muted-foreground">Manage homepage banners and activation.</p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Banners</CardTitle>
        </CardHeader>
        <CardContent>
          {banners?.length ? (
            <pre className="text-xs bg-muted p-3 rounded-md overflow-auto">{JSON.stringify(banners, null, 2)}</pre>
          ) : (
            <p className="text-sm text-muted-foreground">No data yet (view-only route).</p>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

Banners.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Banners;

