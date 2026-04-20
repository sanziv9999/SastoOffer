
import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Star,
    Reply,
    MoreVertical,
    Filter,
    CheckCircle2,
    Search,
    Store,
    ShoppingBag,
} from 'lucide-react';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardFooter
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import DashboardLayout from '@/layouts/DashboardLayout';
import { format } from 'date-fns';

interface VendorReviewsProps {
    reviews: any[];
}

const VendorReviews = ({ reviews: initialReviews }: VendorReviewsProps) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [ratingFilter, setRatingFilter] = useState('all');
    const [visibilityFilter, setVisibilityFilter] = useState('all');
    const [sortBy, setSortBy] = useState('newest');
    const [replyingTo, setReplyingTo] = useState<string | null>(null);
    const [replyText, setReplyText] = useState('');
    const [reviews, setReviews] = useState(initialReviews || []);
    const [togglingHidden, setTogglingHidden] = useState<string | null>(null);

    const [submittingReply, setSubmittingReply] = useState(false);

    const handleReply = (reviewId: string) => {
        setReplyingTo(reviewId);
        setReplyText('');
    };

    const submitReply = (reviewId: string) => {
        if (!replyText.trim() || submittingReply) return;
        setSubmittingReply(true);
        router.post(`/vendor/reviews/${reviewId}/reply`, { vendor_reply: replyText }, {
            preserveScroll: true,
            onSuccess: () => {
                setReviews(reviews.map((r: any) =>
                    r.id === reviewId
                        ? { ...r, merchantReply: { comment: replyText, createdAt: new Date().toISOString() } }
                        : r
                ));
                setReplyingTo(null);
                setReplyText('');
            },
            onFinish: () => setSubmittingReply(false),
        });
    };

    const toggleHidden = (reviewId: string) => {
        setTogglingHidden(reviewId);
        router.patch(`/vendor/reviews/${reviewId}/toggle-hidden`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                setReviews(reviews.map((r: any) =>
                    r.id === reviewId ? { ...r, isHidden: !r.isHidden } : r
                ));
            },
            onFinish: () => setTogglingHidden(null),
        });
    };

    const hiddenCount = reviews.filter((r: any) => r.isHidden).length;
    const visibleCount = reviews.length - hiddenCount;

    const filteredReviews = reviews
        .filter((r: any) => {
            const matchesSearch =
                r.customerName?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                r.comment?.toLowerCase().includes(searchTerm.toLowerCase());
            const matchesRating = ratingFilter === 'all' || r.rating.toString() === ratingFilter;
            const matchesVisibility = visibilityFilter === 'all'
                || (visibilityFilter === 'visible' && !r.isHidden)
                || (visibilityFilter === 'hidden' && r.isHidden);
            return matchesSearch && matchesRating && matchesVisibility;
        })
        .sort((a: any, b: any) => {
            if (sortBy === 'newest') return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
            if (sortBy === 'oldest') return new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
            if (sortBy === 'highest') return b.rating - a.rating;
            if (sortBy === 'lowest') return a.rating - b.rating;
            return 0;
        });

    const filteredCount = filteredReviews.length;

    const averageRating = reviews.length > 0
        ? (reviews.reduce((s: number, r: any) => s + r.rating, 0) / reviews.length).toFixed(1)
        : '0.0';

    return (
        <div className="space-y-4 sm:space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 sm:gap-4">
                <div>
                    <h1 className="text-xl sm:text-2xl font-bold tracking-tight">Customer Reviews</h1>
                    <p className="text-sm sm:text-base text-muted-foreground">Manage your reputation and engage with your customers.</p>
                </div>

                <div className="w-full md:w-auto">
                    <Card className="w-full md:w-auto px-3 sm:px-5 py-3 bg-muted/20 rounded-xl">
                        <div className="flex flex-wrap items-center gap-3 sm:gap-4">
                        <div className="flex flex-col min-w-[88px]">
                            <span className="text-sm font-medium text-muted-foreground uppercase text-[10px] tracking-wider">Average Rating</span>
                            <div className="flex items-center gap-1.5">
                                <span className="text-lg sm:text-xl font-bold">{averageRating}</span>
                                <div className="flex">
                                    {[1, 2, 3, 4, 5].map((i) => (
                                        <Star key={i} className={`h-3 w-3 ${i <= Math.round(parseFloat(averageRating)) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`} />
                                    ))}
                                </div>
                            </div>
                        </div>
                        <Separator orientation="vertical" className="h-8 hidden sm:block" />
                        <div className="flex flex-col min-w-[60px]">
                            <span className="text-sm font-medium text-muted-foreground uppercase text-[10px] tracking-wider">Total</span>
                            <span className="text-lg sm:text-xl font-bold">{reviews.length}</span>
                        </div>
                        {hiddenCount > 0 && (
                            <>
                                <Separator orientation="vertical" className="h-8 hidden sm:block" />
                                <div className="flex flex-col min-w-[70px]">
                                    <span className="text-sm font-medium text-muted-foreground uppercase text-[10px] tracking-wider">Hidden</span>
                                    <span className="text-lg sm:text-xl font-bold text-orange-500">{hiddenCount}</span>
                                </div>
                            </>
                        )}
                        </div>
                    </Card>
                </div>
            </div>

            {/* Filters */}
            <Card>
                <CardContent className="p-3 sm:p-4 flex flex-col gap-3">
                    <div className="relative flex-grow">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <input
                            placeholder="Search reviews or customers..."
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-9"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <Select value={ratingFilter} onValueChange={setRatingFilter}>
                            <SelectTrigger className="w-full">
                                <Filter className="h-4 w-4 mr-2" />
                                <SelectValue placeholder="Rating" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Ratings</SelectItem>
                                <SelectItem value="5">5 Stars</SelectItem>
                                <SelectItem value="4">4 Stars</SelectItem>
                                <SelectItem value="3">3 Stars</SelectItem>
                                <SelectItem value="2">2 Stars</SelectItem>
                                <SelectItem value="1">1 Star</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={visibilityFilter} onValueChange={setVisibilityFilter}>
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Visibility" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Reviews</SelectItem>
                                <SelectItem value="visible">Visible</SelectItem>
                                <SelectItem value="hidden">Hidden</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={sortBy} onValueChange={setSortBy}>
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="newest">Newest First</SelectItem>
                                <SelectItem value="oldest">Oldest First</SelectItem>
                                <SelectItem value="highest">Highest Rating</SelectItem>
                                <SelectItem value="lowest">Lowest Rating</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            {/* Reviews List */}
            <div className="space-y-4">
                {filteredReviews.length > 0 ? (
                    filteredReviews.map((review: any) => (
                        <Card
                            key={review.id}
                            className={`overflow-hidden transition-colors rounded-xl ${review.isHidden ? 'opacity-70 border-dashed' : ''}`}
                        >
                            <CardHeader className="pb-3 px-3 sm:px-6">
                                <div className="flex justify-between items-start gap-2">
                                    <div className="flex gap-3 sm:gap-4 min-w-0">
                                        <div className="h-9 w-9 sm:h-10 sm:w-10 border rounded-full bg-muted flex items-center justify-center font-bold text-muted-foreground overflow-hidden shrink-0">
                                            {review.customerName?.charAt(0) || 'C'}
                                        </div>
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                <CardTitle className="text-sm sm:text-base truncate">{review.customerName || 'Anonymous'}</CardTitle>
                                                <Badge variant="outline" className="text-[10px] h-4">Verified</Badge>
                                                {review.isHidden && (
                                                    <Badge variant="secondary" className="text-[10px] h-4 bg-orange-100 text-orange-700">Hidden</Badge>
                                                )}
                                            </div>
                                            <div className="flex flex-wrap items-center gap-1 mt-1">
                                                {[1, 2, 3, 4, 5].map((i) => (
                                                    <Star key={i} className={`h-3 w-3 ${i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`} />
                                                ))}
                                                <span className="text-xs text-muted-foreground ml-2">
                                                    {format(new Date(review.createdAt), 'MMM dd, yyyy')}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon" className="h-8 w-8 shrink-0">
                                                <MoreVertical className="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                onClick={() => toggleHidden(review.id)}
                                                disabled={togglingHidden === review.id}
                                            >
                                                {review.isHidden ? 'Show on Public Pages' : 'Hide from Public Pages'}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </CardHeader>
                            <CardContent className="pb-3 px-3 sm:px-6 text-sm">
                                <div className="mb-2 flex items-center gap-1.5">
                                    {review.type === 'vendor'
                                        ? <Store className="h-3 w-3 text-muted-foreground" />
                                        : <ShoppingBag className="h-3 w-3 text-muted-foreground" />
                                    }
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        {review.type === 'vendor' ? 'Vendor Review' : 'Deal:'}
                                    </span>
                                    {review.type !== 'vendor' && (
                                        <span className="font-medium text-primary text-xs">
                                            {review.dealTitle || 'Unknown Deal'}
                                        </span>
                                    )}
                                </div>
                                <div className="bg-muted/30 border rounded-lg px-4 py-3">
                                    <p className="leading-relaxed whitespace-pre-line">
                                        {review.comment}
                                    </p>
                                </div>
                            </CardContent>
                            <CardFooter className="pt-0 px-3 sm:px-6 flex flex-col items-stretch gap-3 sm:gap-4">
                                <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="h-8 px-3 gap-2"
                                        onClick={() => handleReply(review.id)}
                                    >
                                        <Reply className="h-3.5 w-3.5" />
                                        Reply to Customer
                                    </Button>
                                </div>

                                {replyingTo === review.id && (
                                    <div className="bg-muted/50 p-4 rounded-lg space-y-3 animate-in fade-in slide-in-from-top-2">
                                        <div className="flex items-center gap-2 text-sm font-semibold mb-1">
                                            <Reply className="h-4 w-4" />
                                            Drafting Reply to {review.customerName}
                                        </div>
                                        <textarea
                                            className="w-full min-h-[100px] p-3 text-sm bg-background border rounded-md focus:ring-1 focus:ring-primary outline-none"
                                            placeholder="Write your response here..."
                                            value={replyText}
                                            onChange={(e) => setReplyText(e.target.value)}
                                        />
                                        <div className="flex justify-end gap-2">
                                            <Button variant="ghost" size="sm" onClick={() => setReplyingTo(null)} disabled={submittingReply}>Cancel</Button>
                                            <Button size="sm" onClick={() => submitReply(review.id)} disabled={submittingReply || !replyText.trim()}>
                                                {submittingReply ? 'Posting...' : 'Post Response'}
                                            </Button>
                                        </div>
                                    </div>
                                )}

                                {/* Persistent Merchant Reply */}
                                {review.merchantReply && (
                                    <div className="bg-primary/5 p-4 rounded-lg border border-primary/10">
                                        <div className="flex items-center gap-2 text-sm font-bold text-primary mb-2">
                                            <CheckCircle2 className="h-4 w-4" />
                                            Response from Merchant
                                        </div>
                                        <p className="text-sm italic">
                                            "{review.merchantReply.comment}"
                                        </p>
                                        <div className="text-[10px] text-muted-foreground mt-2">
                                            Sent on {format(new Date(review.merchantReply.createdAt), 'MMM dd, yyyy')}
                                        </div>
                                    </div>
                                )}
                            </CardFooter>
                        </Card>
                    ))
                ) : (
                    <div className="py-20 text-center text-muted-foreground border-2 border-dashed rounded-lg">
                        <Star className="h-10 w-10 mx-auto mb-4 opacity-20" />
                        <p>No reviews found matching your filters.</p>
                    </div>
                )}
            </div>

            <div className="flex items-center justify-between text-xs text-muted-foreground pt-2">
                <span className="text-[11px] sm:text-xs">
                    Showing <span className="text-foreground font-medium">{filteredCount}</span> review{filteredCount === 1 ? '' : 's'}
                </span>
                <span className="text-[11px] sm:text-xs text-right">
                    Visible: <span className="text-foreground font-medium">{visibleCount}</span>
                    {hiddenCount > 0 && (
                        <> · Hidden: <span className="text-orange-500 font-medium">{hiddenCount}</span></>
                    )}
                </span>
            </div>
        </div>
    );
};

VendorReviews.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorReviews;
