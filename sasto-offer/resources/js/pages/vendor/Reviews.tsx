
import { useState } from 'react';
import {
    Star,
    Reply,
    MoreVertical,
    Filter,
    CheckCircle2,
    ThumbsUp,
    Search
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
import { Input } from '@/components/ui/input';
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
    deals: any[];
}

const VendorReviews = ({ reviews, deals }: VendorReviewsProps) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [replyingTo, setReplyingTo] = useState<string | null>(null);
    const [replyText, setReplyText] = useState('');

    const getDealTitle = (dealId: string) => {
        return deals?.find(d => d.id === dealId)?.title || 'Unknown Deal';
    };

    const handleReply = (reviewId: string) => {
        setReplyingTo(reviewId);
        setReplyText('');
    };

    const submitReply = (reviewId: string) => {
        // Mock reply submission
        console.log(`Replying to review ${reviewId}: ${replyText}`);
        setReplyingTo(null);
        setReplyText('');
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Customer Reviews</h1>
                    <p className="text-muted-foreground">Manage your reputation and engage with your customers.</p>
                </div>

                <div className="flex flex-col sm:flex-row gap-2">
                    <Card className="flex items-center px-4 py-2 gap-4">
                        <div className="flex flex-col">
                            <span className="text-sm font-medium text-muted-foreground uppercase text-[10px] tracking-wider">Average Rating</span>
                            <div className="flex items-center gap-1.5">
                                <span className="text-xl font-bold">4.8</span>
                                <div className="flex">
                                    {[1, 2, 3, 4, 5].map((i) => (
                                        <Star key={i} className={`h-3 w-3 ${i <= 4 ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`} />
                                    ))}
                                </div>
                            </div>
                        </div>
                        <Separator orientation="vertical" className="h-8" />
                        <div className="flex flex-col">
                            <span className="text-sm font-medium text-muted-foreground uppercase text-[10px] tracking-wider">Total Reviews</span>
                            <span className="text-xl font-bold">{reviews?.length || 0}</span>
                        </div>
                    </Card>
                </div>
            </div>

            {/* Filters */}
            <Card>
                <CardContent className="p-4 flex flex-col md:flex-row gap-4">
                    <div className="relative flex-grow">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            placeholder="Search reviews or customers..."
                            className="pl-9"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <div className="flex items-center gap-2">
                        <Select defaultValue="all">
                            <SelectTrigger className="w-[140px]">
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
                        <Select defaultValue="newest">
                            <SelectTrigger className="w-[140px]">
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
                {reviews?.map((review) => (
                    <Card key={review.id} className="overflow-hidden">
                        <CardHeader className="pb-3">
                            <div className="flex justify-between items-start">
                                <div className="flex gap-4">
                                    <div className="h-10 w-10 border rounded-full bg-muted flex items-center justify-center font-bold text-muted-foreground overflow-hidden">
                                        {review.customerName?.charAt(0) || 'C'}
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <CardTitle className="text-base">{review.customerName || 'Anonymous'}</CardTitle>
                                            <Badge variant="outline" className="text-[10px] h-4">Verified Purchase</Badge>
                                        </div>
                                        <div className="flex items-center gap-1 mt-1">
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
                                        <Button variant="ghost" size="icon" className="h-8 w-8">
                                            <MoreVertical className="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem>Report Review</DropdownMenuItem>
                                        <DropdownMenuItem>Mark as Helpful</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </CardHeader>
                        <CardContent className="pb-3 text-sm">
                            <div className="mb-2">
                                <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Deal: </span>
                                <span className="font-medium text-primary hover:underline cursor-pointer">
                                    {getDealTitle(review.dealId)}
                                </span>
                            </div>
                            <p className="leading-relaxed whitespace-pre-line border-l-4 border-muted pl-4 py-1">
                                {review.comment}
                            </p>
                        </CardContent>
                        <CardFooter className="pt-0 flex flex-col items-stretch gap-4">
                            <div className="flex items-center gap-4 text-xs text-muted-foreground">
                                <button className="flex items-center gap-1 hover:text-primary transition-colors">
                                    <ThumbsUp className="h-3 w-3" />
                                    Helpful ({Math.floor(Math.random() * 10)})
                                </button>
                                <Separator orientation="vertical" className="h-3" />
                                <button
                                    className="flex items-center gap-1 hover:text-primary transition-colors"
                                    onClick={() => handleReply(review.id)}
                                >
                                    <Reply className="h-3 w-3" />
                                    Reply to Customer
                                </button>
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
                                        <Button variant="ghost" size="sm" onClick={() => setReplyingTo(null)}>Cancel</Button>
                                        <Button size="sm" onClick={() => submitReply(review.id)}>Post Response</Button>
                                    </div>
                                </div>
                            )}

                            {/* If merchant has already replied (Mock) */}
                            {review.id === '1' && !replyingTo && (
                                <div className="bg-primary/5 p-4 rounded-lg border border-primary/10">
                                    <div className="flex items-center gap-2 text-sm font-bold text-primary mb-2">
                                        <CheckCircle2 className="h-4 w-4" />
                                        Response from Merchant
                                    </div>
                                    <p className="text-sm italic">
                                        "Thank you for your kind words, John! We're thrilled you enjoyed the 5-course dinner. We hope to see you again soon!"
                                    </p>
                                    <div className="text-[10px] text-muted-foreground mt-2">
                                        Sent on {format(new Date(review.createdAt), 'MMM dd, yyyy')}
                                    </div>
                                </div>
                            )}
                        </CardFooter>
                    </Card>
                ))}
            </div>

            {/* Pagination Placeholder */}
            <div className="flex items-center justify-center py-4">
                <div className="flex items-center gap-2">
                    <Button variant="outline" size="sm" disabled>Previous</Button>
                    <div className="flex items-center gap-1 h-8 px-3 rounded-md bg-muted text-sm font-medium">1</div>
                    <Button variant="outline" size="sm">Next</Button>
                </div>
            </div>
        </div>
    );
};

VendorReviews.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default VendorReviews;
