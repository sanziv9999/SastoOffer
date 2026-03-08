
import { useState } from 'react';
import {
    QrCode,
    Maximize2,
    Zap,
    Keyboard,
    CheckCircle2,
    AlertCircle,
    Loader2
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
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import DashboardLayout from '@/layouts/DashboardLayout';
import { toast } from 'sonner';

const Scanner = () => {
    const [manualCode, setManualCode] = useState('');
    const [isValidating, setIsValidating] = useState(false);
    const [scanResult, setScanResult] = useState<{
        status: 'success' | 'error' | null;
        message: string;
        dealName?: string;
        customerName?: string;
    }>({ status: null, message: '' });

    const handleManualSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!manualCode.trim()) return;

        setIsValidating(true);
        setScanResult({ status: null, message: '' });

        // Mock validation logic
        setTimeout(() => {
            setIsValidating(false);
            if (manualCode.toUpperCase() === 'GOUR12345') {
                setScanResult({
                    status: 'success',
                    message: 'Voucher validated successfully!',
                    dealName: '50% Off Luxury 5-Course Dinner for Two',
                    customerName: 'John Doe'
                });
                toast.success('Voucher Validated');
            } else {
                setScanResult({
                    status: 'error',
                    message: 'Invalid or expired voucher code.'
                });
                toast.error('Validation Failed');
            }
        }, 1500);
    };

    const resetScanner = () => {
        setScanResult({ status: null, message: '' });
        setManualCode('');
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-2">
                <h1 className="text-2xl font-bold tracking-tight">Voucher Scanner</h1>
                <p className="text-muted-foreground">Verify customer vouchers instantly via camera or manual entry.</p>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Camera Scanner View */}
                <Card className="overflow-hidden bg-black flex flex-col min-h-[400px]">
                    <CardHeader className="bg-background/10 backdrop-blur-sm z-10 border-b border-white/10">
                        <CardTitle className="text-white flex items-center gap-2">
                            <QrCode className="h-5 w-5" />
                            Camera Feed
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex-grow flex items-center justify-center p-0 relative">
                        {/* Mock Camera View */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10 pointer-events-none" />
                        <div className="relative z-0 w-full h-full flex items-center justify-center bg-[#1a1a1a]">
                            {/* Scan Overlay */}
                            <div className="relative h-64 w-64 border-2 border-white/20 rounded-2xl overflow-hidden">
                                <div className="absolute inset-x-0 h-0.5 bg-primary/80 animate-[scan_2s_ease-in-out_infinite]" />
                                <div className="absolute inset-0 border-[40px] border-black/40" />
                                {/* Corners */}
                                <div className="absolute top-0 left-0 h-6 w-6 border-t-4 border-l-4 border-primary rounded-tl-sm" />
                                <div className="absolute top-0 right-0 h-6 w-6 border-t-4 border-r-4 border-primary rounded-tr-sm" />
                                <div className="absolute bottom-0 left-0 h-6 w-6 border-b-4 border-l-4 border-primary rounded-bl-sm" />
                                <div className="absolute bottom-0 right-0 h-6 w-6 border-b-4 border-r-4 border-primary rounded-br-sm" />
                            </div>
                            <div className="absolute bottom-12 text-white/60 text-sm animate-pulse">
                                Align QR code within the frame
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter className="bg-background/10 backdrop-blur-sm z-10 flex justify-center gap-4 py-4 border-t border-white/10">
                        <Button variant="ghost" size="icon" className="text-white hover:bg-white/20">
                            <Zap className="h-5 w-5" />
                        </Button>
                        <Button variant="ghost" size="icon" className="text-white hover:bg-white/20">
                            <Maximize2 className="h-5 w-5" />
                        </Button>
                    </CardFooter>
                </Card>

                {/* Manual Entry & Results */}
                <div className="space-y-6">
                    <Card h-full>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Keyboard className="h-5 w-5" />
                                Manual Entry
                            </CardTitle>
                            <CardDescription>Enter the alphanumeric coupon code provided by the customer.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleManualSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Input
                                        placeholder="e.g., GOUR12345"
                                        className="text-lg font-mono tracking-widest uppercase h-12"
                                        value={manualCode}
                                        onChange={(e) => setManualCode(e.target.value)}
                                        disabled={isValidating}
                                    />
                                </div>
                                <Button className="w-full h-12" disabled={isValidating || !manualCode}>
                                    {isValidating ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Validating...
                                        </>
                                    ) : (
                                        "Validate Voucher"
                                    )}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Result Card */}
                    {scanResult.status && (
                        <Card className={`animate-in fade-in slide-in-from-bottom-2 border-2 ${scanResult.status === 'success' ? 'border-green-500 bg-green-50/50' : 'border-destructive bg-destructive/5'
                            }`}>
                            <CardContent className="pt-6">
                                <div className="flex items-start gap-4">
                                    {scanResult.status === 'success' ? (
                                        <CheckCircle2 className="h-10 w-10 text-green-600 shrink-0" />
                                    ) : (
                                        <AlertCircle className="h-10 w-10 text-destructive shrink-0" />
                                    )}
                                    <div className="space-y-1">
                                        <h3 className={`text-lg font-bold ${scanResult.status === 'success' ? 'text-green-900' : 'text-destructive'
                                            }`}>
                                            {scanResult.status === 'success' ? 'Voucher Validated' : 'Validation Error'}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">{scanResult.message}</p>

                                        {scanResult.status === 'success' && (
                                            <div className="mt-4 p-3 bg-white rounded-md border shadow-sm space-y-2 text-sm">
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Deal:</span>
                                                    <span className="font-semibold text-right">{scanResult.dealName}</span>
                                                </div>
                                                <div className="flex justify-between">
                                                    <span className="text-muted-foreground">Customer:</span>
                                                    <span className="font-semibold">{scanResult.customerName}</span>
                                                </div>
                                                <div className="flex justify-between pt-2 border-t">
                                                    <span className="text-muted-foreground">Status:</span>
                                                    <Badge variant="outline" className="bg-green-100 text-green-800 border-green-200">
                                                        Available to Redeem
                                                    </Badge>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter className="flex gap-2">
                                {scanResult.status === 'success' && (
                                    <Button className="flex-1 bg-green-600 hover:bg-green-700">Confirm Redemption</Button>
                                )}
                                <Button variant="outline" className="flex-1" onClick={resetScanner}>
                                    {scanResult.status === 'success' ? 'Scan Another' : 'Try Again'}
                                </Button>
                            </CardFooter>
                        </Card>
                    )}

                    {/* Quick Help */}
                    <Card>
                        <CardContent className="pt-6 text-sm text-muted-foreground">
                            <h4 className="font-semibold text-foreground mb-2">Need help?</h4>
                            <ul className="list-disc pl-4 space-y-1">
                                <li>Ensure the QR code is well-lit and centered.</li>
                                <li>Manual codes are case-sensitive but usually uppercase.</li>
                                <li>Contact support if a valid code is rejected.</li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <style>{`
        @keyframes scan {
          0%, 100% { top: 0; }
          50% { top: 100%; }
        }
      `}</style>
        </div>
    );
};

Scanner.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default Scanner;
