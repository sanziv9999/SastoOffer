import { useState, useRef, useCallback, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import {
  ArrowLeft, Upload, X, ImagePlus, Sparkles, Loader2,
  Bold, Italic, List, ListOrdered,
  Megaphone, Clock, Tag, Info, Percent, Banknote, ShoppingCart
} from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

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
  const { categories, offerTypes } = usePage().props as any;
  const { data, setData, post, processing } = useForm({
    title: '',
    shortDesc: '',
    description: '',
    categoryId: categories?.[0]?.id || '',
    offerTypeId: offerTypes?.[0]?.id || '',
    uiType: 'percentage' as 'percentage' | 'fixed' | 'bogo' | 'flash' | 'bundle',
    tags: [] as string[],
    originalPrice: '',
    discountedPrice: '',
    discountPercentage: '',
    maxQuantity: '',
    status: 'active',
    startDate: '',
    endDate: '',
    locationAddress: '',
    redemptionInstructions: '',
    termsConditions: '',
    featurePhoto: null as File | null,
    gallery: [] as File[],
  });

  const [tagInput, setTagInput] = useState('');
  const [isGeneratingTags, setIsGeneratingTags] = useState(false);
  const [featurePreview, setFeaturePreview] = useState<string | null>(null);
  const [galleryPreviews, setGalleryPreviews] = useState<string[]>([]);

  const featureInputRef = useRef<HTMLInputElement>(null);
  const galleryInputRef = useRef<HTMLInputElement>(null);
  const shortEditorRef = useRef<HTMLDivElement>(null);
  const editorRef = useRef<HTMLDivElement>(null);

  const getUIType = (id: string | number): 'percentage' | 'fixed' | 'bogo' | 'flash' | 'bundle' => {
    const name = offerTypes?.find((ot: any) => ot.id.toString() === id.toString())?.name;
    if (name === 'percentage_discount') return 'percentage';
    if (name === 'fixed_amount_discount') return 'fixed';
    if (name === 'bogo') return 'bogo';
    if (name === 'flash_sale') return 'flash';
    return 'fixed';
  };

  const uiType = getUIType(data.offerTypeId);

  // Sync uiType in form data
  useEffect(() => {
    setData('uiType', uiType);
  }, [data.offerTypeId, uiType]);

  // Auto-calculate prices based on deal type
  useEffect(() => {
    if (uiType === 'percentage') {
      const original = parseFloat(data.originalPrice);
      const percent = parseFloat(data.discountPercentage);
      if (!isNaN(original) && !isNaN(percent)) {
        const discounted = original - (original * percent / 100);
        if (data.discountedPrice !== discounted.toFixed(2)) {
          setData(d => ({ ...d, discountedPrice: discounted.toFixed(2) }));
        }
      }
    } else if (uiType === 'fixed' || uiType === 'flash' || uiType === 'bundle') {
      const original = parseFloat(data.originalPrice);
      const discounted = parseFloat(data.discountedPrice);
      if (!isNaN(original) && !isNaN(discounted) && original > 0) {
        const percent = ((original - discounted) / original) * 100;
        if (data.discountPercentage !== percent.toFixed(0)) {
          setData(d => ({ ...d, discountPercentage: percent.toFixed(0) }));
        }
      }
    }
  }, [data.originalPrice, data.discountPercentage, data.discountedPrice, uiType]);

  const handleFeaturePhoto = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setData('featurePhoto', file);
      const reader = new FileReader();
      reader.onloadend = () => setFeaturePreview(reader.result as string);
      reader.readAsDataURL(file);
    }
  };

  const handleGalleryPhotos = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (files) {
      const newFiles = Array.from(files);
      setData('gallery', [...data.gallery, ...newFiles].slice(0, 8));

      newFiles.forEach(file => {
        const reader = new FileReader();
        reader.onloadend = () => setGalleryPreviews(prev => [...prev, reader.result as string].slice(0, 8));
        reader.readAsDataURL(file);
      });
    }
  };

  const removeGalleryImage = (index: number) => {
    setData('gallery', data.gallery.filter((_, i) => i !== index));
    setGalleryPreviews(prev => prev.filter((_, i) => i !== index));
  };

  const generateAITags = useCallback(async () => {
    setIsGeneratingTags(true);
    await new Promise(r => setTimeout(r, 800));

    const selectedCategory = categories?.find((c: any) => c.id.toString() === data.categoryId.toString());
    const categorySlug = selectedCategory?.slug || 'food';
    const catTags = SUGGESTED_TAGS_MAP[categorySlug] || SUGGESTED_TAGS_MAP.food;
    const typeTags = DEAL_TYPE_TAGS[uiType] || DEAL_TYPE_TAGS.percentage;
    const titleWords = data.title.toLowerCase().split(/\s+/).filter(w => w.length > 3);
    const allSuggestions = [...new Set([...typeTags, ...catTags.slice(0, 5), ...titleWords.slice(0, 3)])];
    const newTags = allSuggestions.filter(t => !data.tags.includes(t));
    setData('tags', [...new Set([...data.tags, ...newTags])].slice(0, 15));
    setIsGeneratingTags(false);
    toast.success(`${newTags.length} AI-suggested tags added.`);
  }, [data.categoryId, data.offerTypeId, uiType, data.title, data.tags, categories]);

  const addTag = () => {
    const t = tagInput.trim().toLowerCase().replace(/\s+/g, '-');
    if (t && !data.tags.includes(t)) {
      setData('tags', [...data.tags, t]);
      setTagInput('');
    }
  };

  const removeTag = (tag: string) => setData('tags', data.tags.filter(t => t !== tag));

  const execCommand = (command: string, target: 'shortDesc' | 'description', value?: string) => {
    document.execCommand(command, false, value);
    if (target === 'shortDesc' && shortEditorRef.current) {
      setData('shortDesc', shortEditorRef.current.innerHTML);
    } else if (target === 'description' && editorRef.current) {
      setData('description', editorRef.current.innerHTML);
    }
  };

  const onShortDescChange = () => {
    if (shortEditorRef.current) {
      setData('shortDesc', shortEditorRef.current.innerHTML);
    }
  };

  const onDescriptionChange = () => {
    if (editorRef.current) {
      setData('description', editorRef.current.innerHTML);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/vendor/deals', {
      forceFormData: true,
      onSuccess: () => toast.success('Deal created and published successfully!'),
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" asChild>
          <Link href="/vendor/deals"><ArrowLeft className="h-4 w-4" /></Link>
        </Button>
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Create New Deal</h1>
          <p className="text-muted-foreground">Fill in the details to create and publish your deal</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Photos */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><ImagePlus className="h-5 w-5 text-primary" /> Photos</CardTitle>
            <CardDescription>Add a cover photo and up to 8 gallery images</CardDescription>
          </CardHeader>
          <CardContent className="space-y-5">
            <div>
              <Label className="mb-2 block text-sm font-medium">Feature Photo *</Label>
              <input ref={featureInputRef} type="file" accept="image/*" className="hidden" onChange={handleFeaturePhoto} />
              {featurePreview ? (
                <div className="relative w-full max-w-md rounded-xl overflow-hidden border group">
                  <img src={featurePreview} alt="Feature" className="w-full h-52 object-cover" />
                  <button type="button" onClick={() => { setData('featurePhoto', null); setFeaturePreview(null); }}
                    className="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ) : (
                <button type="button" onClick={() => featureInputRef.current?.click()}
                  className="w-full max-w-md h-44 border-2 border-dashed rounded-xl flex flex-col items-center justify-center gap-2 text-muted-foreground hover:border-primary hover:text-primary transition-colors">
                  <Upload className="h-8 w-8" />
                  <span className="text-sm font-medium">Click to upload feature photo</span>
                </button>
              )}
            </div>

            <Separator />

            <div>
              <Label className="mb-2 block text-sm font-medium">Gallery (up to 8 images)</Label>
              <input ref={galleryInputRef} type="file" accept="image/*" multiple className="hidden" onChange={handleGalleryPhotos} />
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {galleryPreviews.map((img, i) => (
                  <div key={i} className="relative aspect-square rounded-lg overflow-hidden border group">
                    <img src={img} alt={`Gallery ${i + 1}`} className="w-full h-full object-cover" />
                    <button type="button" onClick={() => removeGalleryImage(i)}
                      className="absolute top-1 right-1 bg-black/60 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                      <X className="h-3 w-3" />
                    </button>
                  </div>
                ))}
                {galleryPreviews.length < 8 && (
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

        {/* Basic Information */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Info className="h-5 w-5 text-primary" /> Basic Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label>Deal Title *</Label>
              <Input placeholder="Catchy title (e.g. 50% Off Special Lunch Combo)" value={data.title} onChange={e => setData('title', e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label>Short Description *</Label>
              <div className="border rounded-lg overflow-hidden">
                <div className="flex flex-wrap items-center gap-1 px-2 py-1.5 bg-muted/50 border-b">
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('bold', 'shortDesc')} className="p-1.5 rounded hover:bg-background"><Bold className="h-4 w-4" /></button>
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('italic', 'shortDesc')} className="p-1.5 rounded hover:bg-background"><Italic className="h-4 w-4" /></button>
                  <Separator orientation="vertical" className="h-5 mx-1" />
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('insertUnorderedList', 'shortDesc')} className="p-1.5 rounded hover:bg-background"><List className="h-4 w-4" /></button>
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('insertOrderedList', 'shortDesc')} className="p-1.5 rounded hover:bg-background"><ListOrdered className="h-4 w-4" /></button>
                </div>
                <div ref={shortEditorRef} contentEditable suppressContentEditableWarning
                  onInput={onShortDescChange}
                  className="min-h-[80px] px-3 py-2 text-sm focus:outline-none prose prose-sm max-w-none [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-5 [&_ol]:pl-5" />
              </div>
            </div>

            <div className="space-y-2">
              <Label>Full Description *</Label>
              <div className="border rounded-lg overflow-hidden">
                <div className="flex flex-wrap items-center gap-1 px-2 py-1.5 bg-muted/50 border-b">
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('bold', 'description')} className="p-1.5 rounded hover:bg-background"><Bold className="h-4 w-4" /></button>
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('italic', 'description')} className="p-1.5 rounded hover:bg-background"><Italic className="h-4 w-4" /></button>
                  <Separator orientation="vertical" className="h-5 mx-1" />
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('insertUnorderedList', 'description')} className="p-1.5 rounded hover:bg-background"><List className="h-4 w-4" /></button>
                  <button type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => execCommand('insertOrderedList', 'description')} className="p-1.5 rounded hover:bg-background"><ListOrdered className="h-4 w-4" /></button>
                </div>
                <div ref={editorRef} contentEditable suppressContentEditableWarning
                  onInput={onDescriptionChange}
                  className="min-h-[140px] px-3 py-2 text-sm focus:outline-none prose prose-sm max-w-none [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-5 [&_ol]:pl-5" />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label>Deal Type</Label>
                <Select value={data.offerTypeId?.toString()} onValueChange={v => {
                  setData(d => ({ ...d, offerTypeId: v, discountPercentage: '', discountedPrice: '' }));
                }}>
                  <SelectTrigger><SelectValue placeholder="Select type" /></SelectTrigger>
                  <SelectContent>
                    {offerTypes?.map((ot: any) => (
                      <SelectItem key={ot.id} value={ot.id.toString()}>{ot.display_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Category</Label>
                <Select value={data.categoryId?.toString()} onValueChange={v => setData('categoryId', v)}>
                  <SelectTrigger><SelectValue placeholder="Select a category" /></SelectTrigger>
                  <SelectContent>
                    {categories?.map((cat: any) => (
                      <SelectItem key={cat.id} value={cat.id.toString()}>{cat.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Status</Label>
                <Select value={data.status} onValueChange={v => setData('status', v)}>
                  <SelectTrigger><SelectValue placeholder="Select status" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                    <SelectItem value="draft">Draft</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Dynamic Pricing Section */}
        <Card className="overflow-hidden">
          <CardHeader className="bg-primary/5 pb-4">
            <CardTitle className="flex items-center gap-2">
              {uiType === 'percentage' && <Percent className="h-5 w-5 text-primary" />}
              {(uiType === 'fixed' || uiType === 'flash') && <Banknote className="h-5 w-5 text-primary" />}
              {(uiType === 'bogo' || uiType === 'bundle') && <ShoppingCart className="h-5 w-5 text-primary" />}
              Pricing Details
            </CardTitle>
            <CardDescription>
              {uiType === 'percentage' && "Set the original price and the discount percentage."}
              {uiType === 'fixed' && "Set the original price and the final offer price."}
              {uiType === 'bogo' && "Set the unit price for the buy-one-get-one offer."}
              {uiType === 'bundle' && "Set the bundle's total original value and special price."}
            </CardDescription>
          </CardHeader>
          <CardContent className="pt-6">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">

              {/* Original Price - Always shown except maybe BOGO can be different, but let's keep it for value comparison */}
              <div className="space-y-2">
                <Label className="text-sm font-semibold">
                  {uiType === 'bundle' ? 'Total Bundle Value ($)' : 'Original Price ($) *'}
                </Label>
                <div className="relative">
                  <span className="absolute left-3 top-2.5 text-muted-foreground">$</span>
                  <Input
                    type="number"
                    placeholder="0.00"
                    className="pl-7"
                    value={data.originalPrice}
                    onChange={e => setData('originalPrice', e.target.value)}
                    required
                  />
                </div>
              </div>

              {/* Dynamic Column 2 */}
              {uiType === 'percentage' ? (
                <div className="space-y-2">
                  <Label className="text-sm font-semibold text-primary flex items-center gap-1">
                    <Percent className="h-3.5 w-3.5" /> Discount Percentage (%) *
                  </Label>
                  <div className="relative">
                    <Input
                      type="number"
                      placeholder="e.g. 50"
                      value={data.discountPercentage}
                      onChange={e => setData('discountPercentage', e.target.value)}
                      required
                    />
                    <span className="absolute right-3 top-2.5 text-muted-foreground">%</span>
                  </div>
                </div>
              ) : (
                <div className="space-y-2">
                  <Label className="text-sm font-semibold text-primary">
                    {uiType === 'bogo' ? 'Unit Price ($) *' : 'Offer Price ($) *'}
                  </Label>
                  <div className="relative">
                    <span className="absolute left-3 top-2.5 text-muted-foreground">$</span>
                    <Input
                      type="number"
                      placeholder="0.00"
                      className="pl-7"
                      value={data.discountedPrice}
                      onChange={e => setData('discountedPrice', e.target.value)}
                      required
                    />
                  </div>
                </div>
              )}

              {/* Dynamic Column 3 - Calculation Results/Max Qty */}
              <div className="space-y-2">
                <Label className="text-sm font-semibold">Max Quantity Available</Label>
                <Input
                  type="number"
                  placeholder="Unlimited"
                  value={data.maxQuantity}
                  onChange={e => setData('maxQuantity', e.target.value)}
                />
                <p className="text-[10px] text-muted-foreground">Leave empty for unlimited</p>
              </div>

            </div>

            {/* Price Preview / Summary */}
            <div className="mt-6 p-4 rounded-lg bg-muted/30 border border-dashed flex flex-col sm:flex-row items-center justify-between gap-4">
              <div className="flex items-center gap-4">
                <div className="text-center sm:text-left">
                  <p className="text-xs text-muted-foreground uppercase tracking-wider font-bold">You're offering</p>
                  <div className="flex items-baseline gap-2">
                    <span className="text-2xl font-bold text-primary">
                      {uiType === 'percentage' ? `${data.discountPercentage || 0}% OFF` :
                        uiType === 'bogo' ? 'BOGO FREE' :
                          `$${(data.originalPrice ? parseFloat(data.originalPrice) - parseFloat(data.discountedPrice || '0') : 0).toFixed(2)} savings`}
                    </span>
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <div className="text-right">
                  <p className="text-xs text-muted-foreground">Customer pays</p>
                  <p className="text-xl font-bold">${parseFloat(data.discountedPrice || '0').toFixed(2)}</p>
                </div>
                {uiType === 'percentage' && data.originalPrice && (
                  <Badge variant="outline" className="text-xs line-through opacity-70">
                    ${parseFloat(data.originalPrice).toFixed(2)}
                  </Badge>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Tags */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Tag className="h-5 w-5 text-primary" /> Tags & SEO</CardTitle>
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
            {data.tags.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {data.tags.map(tag => (
                  <Badge key={tag} variant="secondary" className="gap-1 pl-2.5 pr-1.5 py-1">
                    {tag}
                    <button type="button" onClick={() => removeTag(tag)} className="ml-0.5 hover:text-destructive"><X className="h-3 w-3" /></button>
                  </Badge>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Schedule */}
        <Card>
          <CardHeader><CardTitle className="flex items-center gap-2"><Clock className="h-5 w-5 text-primary" /> Schedule</CardTitle></CardHeader>
          <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Start Date *</Label>
              <Input type="date" value={data.startDate} onChange={e => setData('startDate', e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label>End Date *</Label>
              <Input type="date" value={data.endDate} onChange={e => setData('endDate', e.target.value)} required />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-3 pb-8">
          <Button type="button" variant="outline" asChild><Link href="/vendor/deals">Cancel</Link></Button>
          <Button type="submit" size="lg" disabled={processing} className="px-8 shadow-lg shadow-primary/20">
            {processing ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Sending...
              </>
            ) : 'Submit for Review'}
          </Button>
        </div>
      </form>
    </div>
  );
};

CreateDeal.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default CreateDeal;
