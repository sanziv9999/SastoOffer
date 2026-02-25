import { useState, useRef, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/hooks/use-toast';
import {
  ArrowLeft, Upload, X, ImagePlus, Sparkles, Loader2,
  Bold, Italic, List, ListOrdered, Link, AlignLeft, AlignCenter,
  Megaphone, Star, TrendingUp, Clock, MapPin, Tag, Info
} from 'lucide-react';

const SUGGESTED_TAGS_MAP: Record<string, string[]> = {
  food: ['restaurant', 'dining', 'gourmet', 'lunch', 'dinner', 'brunch', 'takeout', 'delivery', 'cuisine', 'foodie'],
  beauty: ['spa', 'salon', 'skincare', 'wellness', 'massage', 'facial', 'haircut', 'manicure', 'grooming', 'beauty-treatment'],
  travel: ['hotel', 'vacation', 'getaway', 'resort', 'flight', 'booking', 'adventure', 'tourism', 'weekend-trip', 'staycation'],
  electronics: ['gadget', 'tech', 'smartphone', 'laptop', 'accessories', 'smart-home', 'wearable', 'audio', 'gaming', 'tablet'],
  entertainment: ['movies', 'concert', 'event', 'tickets', 'show', 'fun', 'nightlife', 'experience', 'activity', 'theme-park'],
};

const DEAL_TYPE_TAGS: Record<string, string[]> = {
  percentage: ['discount', 'percent-off', 'savings'],
  fixed: ['flat-price', 'fixed-deal', 'value-deal'],
  bogo: ['buy-one-get-one', 'bogo', '2-for-1'],
  bundle: ['combo', 'bundle-deal', 'package'],
  flash: ['flash-sale', 'limited-time', 'urgent', 'today-only'],
};

const CreateDeal = () => {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Form state
  const [title, setTitle] = useState('');
  const [shortDesc, setShortDesc] = useState('');
  const [category, setCategory] = useState('food');
  const [dealType, setDealType] = useState('percentage');
  const [tags, setTags] = useState<string[]>([]);
  const [tagInput, setTagInput] = useState('');
  const [isGeneratingTags, setIsGeneratingTags] = useState(false);

  // Photo state
  const [featurePhoto, setFeaturePhoto] = useState<string | null>(null);
  const [gallery, setGallery] = useState<string[]>([]);
  const featureInputRef = useRef<HTMLInputElement>(null);
  const galleryInputRef = useRef<HTMLInputElement>(null);

  // Rich text
  const editorRef = useRef<HTMLDivElement>(null);

  // Promotion
  const [requestHomepagePromo, setRequestHomepagePromo] = useState(false);
  const [requestFeatured, setRequestFeatured] = useState(false);
  const [requestUrgentDeal, setRequestUrgentDeal] = useState(false);
  const [requestEmailBlast, setRequestEmailBlast] = useState(false);

  // Extras
  const [redemptionInstructions, setRedemptionInstructions] = useState('');
  const [termsConditions, setTermsConditions] = useState('');
  const [locationAddress, setLocationAddress] = useState('');

  const handleFileToBase64 = (file: File): Promise<string> => {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result as string);
      reader.readAsDataURL(file);
    });
  };

  const handleFeaturePhoto = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const base64 = await handleFileToBase64(file);
      setFeaturePhoto(base64);
    }
  };

  const handleGalleryPhotos = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (files) {
      const newImages = await Promise.all(Array.from(files).map(handleFileToBase64));
      setGallery(prev => [...prev, ...newImages].slice(0, 8));
    }
  };

  const removeGalleryImage = (index: number) => {
    setGallery(prev => prev.filter((_, i) => i !== index));
  };

  const generateAITags = useCallback(async () => {
    setIsGeneratingTags(true);
    await new Promise(r => setTimeout(r, 800));
    const catTags = SUGGESTED_TAGS_MAP[category] || SUGGESTED_TAGS_MAP.food;
    const typeTags = DEAL_TYPE_TAGS[dealType] || DEAL_TYPE_TAGS.percentage;
    const titleWords = title.toLowerCase().split(/\s+/).filter(w => w.length > 3);
    const allSuggestions = [...new Set([...typeTags, ...catTags.slice(0, 5), ...titleWords.slice(0, 3)])];
    const newTags = allSuggestions.filter(t => !tags.includes(t));
    setTags(prev => [...new Set([...prev, ...newTags])].slice(0, 15));
    setIsGeneratingTags(false);
    toast({ title: 'Tags Generated', description: `${newTags.length} AI-suggested tags added for better search & SEO.` });
  }, [category, dealType, title, tags, toast]);

  const addTag = () => {
    const t = tagInput.trim().toLowerCase().replace(/\s+/g, '-');
    if (t && !tags.includes(t)) {
      setTags(prev => [...prev, t]);
      setTagInput('');
    }
  };

  const removeTag = (tag: string) => setTags(prev => prev.filter(t => t !== tag));

  const execCommand = (command: string, value?: string) => {
    document.execCommand(command, false, value);
    editorRef.current?.focus();
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    await new Promise(r => setTimeout(r, 1000));
    toast({ title: 'Deal Created!', description: 'Your deal has been submitted for review.' });
    setIsSubmitting(false);
    navigate('/vendor/deals');
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate(-1)}>
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Create New Deal</h1>
          <p className="text-muted-foreground">Fill in the details to create and publish your deal</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* ─── Feature Photo & Gallery ─── */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><ImagePlus className="h-5 w-5 text-primary" /> Photos</CardTitle>
            <CardDescription>Add a cover photo and up to 8 gallery images</CardDescription>
          </CardHeader>
          <CardContent className="space-y-5">
            {/* Feature Photo */}
            <div>
              <Label className="mb-2 block text-sm font-medium">Feature Photo <span className="text-destructive">*</span></Label>
              <input ref={featureInputRef} type="file" accept="image/*" className="hidden" onChange={handleFeaturePhoto} />
              {featurePhoto ? (
                <div className="relative w-full max-w-md rounded-xl overflow-hidden border group">
                  <img src={featurePhoto} alt="Feature" className="w-full h-52 object-cover" />
                  <button type="button" onClick={() => setFeaturePhoto(null)}
                    className="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ) : (
                <button type="button" onClick={() => featureInputRef.current?.click()}
                  className="w-full max-w-md h-44 border-2 border-dashed rounded-xl flex flex-col items-center justify-center gap-2 text-muted-foreground hover:border-primary hover:text-primary transition-colors">
                  <Upload className="h-8 w-8" />
                  <span className="text-sm font-medium">Click to upload feature photo</span>
                  <span className="text-xs">JPG, PNG or WebP — max 5 MB</span>
                </button>
              )}
            </div>

            <Separator />

            {/* Gallery */}
            <div>
              <Label className="mb-2 block text-sm font-medium">Gallery (up to 8 images)</Label>
              <input ref={galleryInputRef} type="file" accept="image/*" multiple className="hidden" onChange={handleGalleryPhotos} />
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {gallery.map((img, i) => (
                  <div key={i} className="relative aspect-square rounded-lg overflow-hidden border group">
                    <img src={img} alt={`Gallery ${i + 1}`} className="w-full h-full object-cover" />
                    <button type="button" onClick={() => removeGalleryImage(i)}
                      className="absolute top-1 right-1 bg-black/60 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                      <X className="h-3 w-3" />
                    </button>
                  </div>
                ))}
                {gallery.length < 8 && (
                  <button type="button" onClick={() => galleryInputRef.current?.click()}
                    className="aspect-square border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:border-primary hover:text-primary transition-colors">
                    <ImagePlus className="h-5 w-5" />
                    <span className="text-xs">Add</span>
                  </button>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {/* ─── Basic Information ─── */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Info className="h-5 w-5 text-primary" /> Basic Information</CardTitle>
            <CardDescription>Core details about your deal</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label>Deal Title <span className="text-destructive">*</span></Label>
              <Input placeholder="e.g., 50% Off Gourmet Dinner for Two" value={title} onChange={e => setTitle(e.target.value)} required />
              <p className="text-xs text-muted-foreground">Keep it short, catchy, and include the discount value</p>
            </div>
            <div className="space-y-2">
              <Label>Short Description <span className="text-destructive">*</span></Label>
              <Input placeholder="Brief one-liner about the deal" value={shortDesc} onChange={e => setShortDesc(e.target.value)} required maxLength={120} />
              <p className="text-xs text-muted-foreground">{shortDesc.length}/120 characters — shown in deal cards</p>
            </div>

            {/* Rich Text Editor */}
            <div className="space-y-2">
              <Label>Full Description <span className="text-destructive">*</span></Label>
              <div className="border rounded-lg overflow-hidden">
                <div className="flex flex-wrap items-center gap-1 px-2 py-1.5 bg-muted/50 border-b">
                  <button type="button" onClick={() => execCommand('bold')} className="p-1.5 rounded hover:bg-background transition-colors" title="Bold"><Bold className="h-4 w-4" /></button>
                  <button type="button" onClick={() => execCommand('italic')} className="p-1.5 rounded hover:bg-background transition-colors" title="Italic"><Italic className="h-4 w-4" /></button>
                  <Separator orientation="vertical" className="h-5 mx-1" />
                  <button type="button" onClick={() => execCommand('insertUnorderedList')} className="p-1.5 rounded hover:bg-background transition-colors" title="Bullet list"><List className="h-4 w-4" /></button>
                  <button type="button" onClick={() => execCommand('insertOrderedList')} className="p-1.5 rounded hover:bg-background transition-colors" title="Numbered list"><ListOrdered className="h-4 w-4" /></button>
                  <Separator orientation="vertical" className="h-5 mx-1" />
                  <button type="button" onClick={() => execCommand('justifyLeft')} className="p-1.5 rounded hover:bg-background transition-colors" title="Align left"><AlignLeft className="h-4 w-4" /></button>
                  <button type="button" onClick={() => execCommand('justifyCenter')} className="p-1.5 rounded hover:bg-background transition-colors" title="Align center"><AlignCenter className="h-4 w-4" /></button>
                  <Separator orientation="vertical" className="h-5 mx-1" />
                  <button type="button" onClick={() => {
                    const url = prompt('Enter URL:');
                    if (url) execCommand('createLink', url);
                  }} className="p-1.5 rounded hover:bg-background transition-colors" title="Insert link"><Link className="h-4 w-4" /></button>
                </div>
                <div ref={editorRef} contentEditable suppressContentEditableWarning
                  className="min-h-[140px] px-3 py-2 text-sm focus:outline-none prose prose-sm max-w-none"
                  data-placeholder="Describe what's included, highlights, and any fine print…" />
              </div>
              <p className="text-xs text-muted-foreground">Use formatting to make your deal description scannable</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Deal Type</Label>
                <Select value={dealType} onValueChange={setDealType}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="percentage">Percentage Discount</SelectItem>
                    <SelectItem value="fixed">Fixed Price</SelectItem>
                    <SelectItem value="bogo">Buy One Get One</SelectItem>
                    <SelectItem value="bundle">Bundle</SelectItem>
                    <SelectItem value="flash">⚡ Flash Sale</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Category</Label>
                <Select value={category} onValueChange={setCategory}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="food">Food & Dining</SelectItem>
                    <SelectItem value="beauty">Beauty & Spa</SelectItem>
                    <SelectItem value="travel">Travel</SelectItem>
                    <SelectItem value="electronics">Electronics</SelectItem>
                    <SelectItem value="entertainment">Entertainment</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* ─── AI Tags ─── */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Tag className="h-5 w-5 text-primary" /> Tags & SEO</CardTitle>
            <CardDescription>Tags help customers find your deal via search and improve SEO ranking</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex gap-2">
              <Input placeholder="Add a tag…" value={tagInput} onChange={e => setTagInput(e.target.value)}
                onKeyDown={e => { if (e.key === 'Enter') { e.preventDefault(); addTag(); } }} />
              <Button type="button" variant="outline" onClick={addTag}>Add</Button>
              <Button type="button" variant="default" onClick={generateAITags} disabled={isGeneratingTags} className="gap-2 shrink-0">
                {isGeneratingTags ? <Loader2 className="h-4 w-4 animate-spin" /> : <Sparkles className="h-4 w-4" />}
                AI Suggest
              </Button>
            </div>
            {tags.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {tags.map(tag => (
                  <Badge key={tag} variant="secondary" className="gap-1 pl-2.5 pr-1.5 py-1">
                    {tag}
                    <button type="button" onClick={() => removeTag(tag)} className="ml-0.5 hover:text-destructive"><X className="h-3 w-3" /></button>
                  </Badge>
                ))}
              </div>
            )}
            <p className="text-xs text-muted-foreground flex items-center gap-1">
              <Sparkles className="h-3 w-3" /> Click "AI Suggest" to auto-generate tags based on your deal title, category, and type
            </p>
          </CardContent>
        </Card>

        {/* ─── Pricing ─── */}
        <Card>
          <CardHeader>
            <CardTitle>Pricing</CardTitle>
            <CardDescription>Set original and discounted prices</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label>Original Price ($) <span className="text-destructive">*</span></Label>
                <Input type="number" placeholder="0.00" min="0" step="0.01" required />
              </div>
              <div className="space-y-2">
                <Label>Discounted Price ($) <span className="text-destructive">*</span></Label>
                <Input type="number" placeholder="0.00" min="0" step="0.01" required />
              </div>
              <div className="space-y-2">
                <Label>Max Quantity</Label>
                <Input type="number" placeholder="Unlimited" min="1" />
                <p className="text-xs text-muted-foreground">Leave empty for unlimited</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* ─── Schedule ─── */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Clock className="h-5 w-5 text-primary" /> Schedule</CardTitle>
            <CardDescription>When should this deal be active?</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Start Date <span className="text-destructive">*</span></Label>
                <Input type="date" required />
              </div>
              <div className="space-y-2">
                <Label>End Date <span className="text-destructive">*</span></Label>
                <Input type="date" required />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* ─── Location & Redemption ─── */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><MapPin className="h-5 w-5 text-primary" /> Location & Redemption</CardTitle>
            <CardDescription>Where and how can customers redeem this deal?</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label>Location / Address</Label>
              <Input placeholder="e.g., 123 Main St, Downtown" value={locationAddress} onChange={e => setLocationAddress(e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Redemption Instructions</Label>
              <Textarea placeholder="How should customers redeem this deal? e.g., Show coupon code at checkout…" rows={3}
                value={redemptionInstructions} onChange={e => setRedemptionInstructions(e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Terms & Conditions</Label>
              <Textarea placeholder="Any restrictions, blackout dates, or fine print…" rows={3}
                value={termsConditions} onChange={e => setTermsConditions(e.target.value)} />
            </div>
          </CardContent>
        </Card>

        {/* ─── Promotion ─── */}
        <Card className="border-primary/30 bg-primary/[0.02]">
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Megaphone className="h-5 w-5 text-primary" /> Promotion & Visibility</CardTitle>
            <CardDescription>Boost your deal's reach — optional promotional add-ons</CardDescription>
          </CardHeader>
          <CardContent className="space-y-5">
            <div className="flex items-center justify-between gap-4 p-3 rounded-lg border bg-background">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-primary/10"><Star className="h-5 w-5 text-primary" /></div>
                <div>
                  <p className="font-medium text-sm">Homepage Featured Banner</p>
                  <p className="text-xs text-muted-foreground">Display your deal in the featured carousel on the homepage</p>
                </div>
              </div>
              <Switch checked={requestHomepagePromo} onCheckedChange={setRequestHomepagePromo} />
            </div>

            <div className="flex items-center justify-between gap-4 p-3 rounded-lg border bg-background">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-primary/10"><TrendingUp className="h-5 w-5 text-primary" /></div>
                <div>
                  <p className="font-medium text-sm">Featured Deal Badge</p>
                  <p className="text-xs text-muted-foreground">Mark as "Featured" with a special badge in listings</p>
                </div>
              </div>
              <Switch checked={requestFeatured} onCheckedChange={setRequestFeatured} />
            </div>

            <div className="flex items-center justify-between gap-4 p-3 rounded-lg border bg-background">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-primary/10"><Clock className="h-5 w-5 text-primary" /></div>
                <div>
                  <p className="font-medium text-sm">Urgent / Flash Highlight</p>
                  <p className="text-xs text-muted-foreground">Add urgency indicators like countdown timers</p>
                </div>
              </div>
              <Switch checked={requestUrgentDeal} onCheckedChange={setRequestUrgentDeal} />
            </div>

            <div className="flex items-center justify-between gap-4 p-3 rounded-lg border bg-background">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-lg bg-primary/10"><Megaphone className="h-5 w-5 text-primary" /></div>
                <div>
                  <p className="font-medium text-sm">Email Blast to Subscribers</p>
                  <p className="text-xs text-muted-foreground">Send this deal to all subscribed customers via email</p>
                </div>
              </div>
              <Switch checked={requestEmailBlast} onCheckedChange={setRequestEmailBlast} />
            </div>

            {(requestHomepagePromo || requestFeatured || requestUrgentDeal || requestEmailBlast) && (
              <p className="text-xs text-muted-foreground bg-muted/50 p-2 rounded-md">
                ⓘ Promotion requests will be reviewed by the admin team. You'll be notified once approved.
              </p>
            )}
          </CardContent>
        </Card>

        {/* ─── Submit ─── */}
        <div className="flex flex-col sm:flex-row justify-end gap-3 pb-8">
          <Button type="button" variant="outline" onClick={() => navigate(-1)}>Cancel</Button>
          <Button type="button" variant="secondary">Save as Draft</Button>
          <Button type="submit" disabled={isSubmitting} className="gap-2">
            {isSubmitting ? <><Loader2 className="h-4 w-4 animate-spin" /> Creating…</> : 'Submit for Review'}
          </Button>
        </div>
      </form>
    </div>
  );
};

export default CreateDeal;
