
import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Star, ArrowLeft, Save, Trash2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/hooks/use-toast';
import Link from '@/components/Link';
import DashboardLayout from '@/layouts/DashboardLayout';
import { Review, Deal } from '@/types';

interface EditReviewProps {
    reviews: Review[];
    deals: Deal[];
}

const EditReview = ({ reviews, deals }: EditReviewProps) => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { toast } = useToast();

    const [review, setReview] = useState<Review | null>(null);
    const [deal, setDeal] = useState<Deal | null>(null);
    const [rating, setRating] = useState(5);
    const [comment, setComment] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        const foundReview = reviews.find(r => r.id === id);
        if (foundReview) {
            setReview(foundReview);
            setRating(foundReview.rating);
            setComment(foundReview.comment);

            const foundDeal = deals.find(d => d.id === foundReview.dealId);
            if (foundDeal) setDeal(foundDeal);
        } else {
            toast({
                title: "Review not found",
                description: "The review you're trying to edit doesn't exist.",
                variant: "destructive"
            });
            navigate('/dashboard/reviews');
        }
    }, [id, reviews, deals, navigate, toast]);

    const handleSave = async () => {
        if (!comment.trim()) {
            toast({
                title: "Error",
                description: "Please provide a comment for your review.",
                variant: "destructive"
            });
            return;
        }

        setIsSubmitting(true);
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 800));

        toast({
            title: "Success",
            description: "Your review has been updated successfully."
        });

        setIsSubmitting(false);
        navigate('/dashboard/reviews');
    };

    if (!review) return null;

    return (
        <div className="space-y-6 max-w-2xl mx-auto">
            <div className="flex items-center gap-4">
                <Button variant="ghost" size="icon" asChild>
                    <Link href="/dashboard/reviews">
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                </Button>
                <h1 className="text-2xl font-bold tracking-tight">Edit Review</h1>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">
                        Review for {deal?.title || 'Unknown Deal'}
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="space-y-3">
                        <label className="text-sm font-medium">Rating</label>
                        <div className="flex items-center gap-2">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <button
                                    key={star}
                                    type="button"
                                    onClick={() => setRating(star)}
                                    className="focus:outline-none transition-transform hover:scale-110"
                                >
                                    <Star
                                        className={`h-8 w-8 ${star <= rating ? 'text-yellow-400 fill-current' : 'text-muted-foreground'
                                            }`}
                                    />
                                </button>
                            ))}
                            <span className="ml-2 text-lg font-semibold">{rating}/5</span>
                        </div>
                    </div>

                    <div className="space-y-3">
                        <label htmlFor="comment" className="text-sm font-medium">Your Feedback</label>
                        <Textarea
                            id="comment"
                            placeholder="Tell us what you thought about this deal..."
                            value={comment}
                            onChange={(e) => setComment(e.target.value)}
                            className="min-h-[150px] resize-none"
                        />
                    </div>
                </CardContent>
                <CardFooter className="flex justify-between border-t p-6 mt-6">
                    <Button variant="outline" className="text-destructive hover:bg-destructive/10" disabled={isSubmitting}>
                        <Trash2 className="h-4 w-4 mr-2" />
                        Delete Review
                    </Button>
                    <div className="flex gap-3">
                        <Button variant="ghost" onClick={() => navigate('/dashboard/reviews')} disabled={isSubmitting}>
                            Cancel
                        </Button>
                        <Button onClick={handleSave} disabled={isSubmitting}>
                            {isSubmitting ? 'Saving...' : (
                                <>
                                    <Save className="h-4 w-4 mr-2" />
                                    Save Changes
                                </>
                            )}
                        </Button>
                    </div>
                </CardFooter>
            </Card>
        </div>
    );
};

EditReview.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default EditReview;
