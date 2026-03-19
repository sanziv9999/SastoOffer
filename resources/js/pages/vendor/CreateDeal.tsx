import { useState, useRef, useCallback, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import Link from '@/components/Link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import {
  ArrowLeft, Upload, X, ImagePlus, Sparkles, Loader2,
  Bold, Italic, List, ListOrdered,
  Tag, Info, Star, GripVertical
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

type LocalDealImage = {
  id: string;
  file: File;
  preview: string;
};

const CreateDeal = () => {
  const { categories } = usePage().props as any;
  const { data, setData, post, processing, transform } = useForm({
    title: '',
    shortDesc: '',
    description: '',
    categoryId: categories?.[0]?.id || '',
    tags: [] as string[],
    basePrice: '',
    maxQuantity: '',
    status: 'active',
    locationAddress: '',
    redemptionInstructions: '',
    termsConditions: '',
    images: [] as File[],
    image_order: '',
    featured_image_key: '',
  });

  const [tagInput, setTagInput] = useState('');
  const [isGeneratingTags, setIsGeneratingTags] = useState(false);
  const [dealImages, setDealImages] = useState<LocalDealImage[]>([]);
  const [featuredImageId, setFeaturedImageId] = useState<string | null>(null);
  const [draggingImageId, setDraggingImageId] = useState<string | null>(null);

  const imagesInputRef = useRef<HTMLInputElement>(null);
  const shortEditorRef = useRef<HTMLDivElement>(null);
  const editorRef = useRef<HTMLDivElement>(null);

  // Offers are managed in the next step (Offers screen).

  const handleDealImages = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files) return;

    const available = Math.max(0, 8 - dealImages.length);
    if (available <= 0) return;

    const incoming = Array.from(files).slice(0, available).map((file) => ({
      id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
      file,
      preview: URL.createObjectURL(file),
    }));

    const next = [...dealImages, ...incoming];
    setDealImages(next);
    if (!featuredImageId && next.length > 0) {
      setFeaturedImageId(next[0].id);
    }

    e.target.value = '';
  };

  const removeImage = (id: string) => {
    const target = dealImages.find((img) => img.id === id);
    if (target?.preview) {
      URL.revokeObjectURL(target.preview);
    }
    const next = dealImages.filter((img) => img.id !== id);
    setDealImages(next);
    if (featuredImageId === id) {
      setFeaturedImageId(next[0]?.id ?? null);
    }
  };

  const moveImage = (dragId: string, dropId: string) => {
    if (dragId === dropId) return;
    const from = dealImages.findIndex((img) => img.id === dragId);
    const to = dealImages.findIndex((img) => img.id === dropId);
    if (from < 0 || to < 0) return;
    const next = [...dealImages];
    const [moved] = next.splice(from, 1);
    next.splice(to, 0, moved);
    setDealImages(next);
  };

  const generateAITags = useCallback(async () => {
    setIsGeneratingTags(true);
    await new Promise(r => setTimeout(r, 800));

    const selectedCategory = categories?.find((c: any) => c.id.toString() === data.categoryId.toString());
    const categorySlug = selectedCategory?.slug || 'food';
    const catTags = SUGGESTED_TAGS_MAP[categorySlug] || SUGGESTED_TAGS_MAP.food;
    const typeTags = DEAL_TYPE_TAGS.percentage;
    const titleWords = data.title.toLowerCase().split(/\s+/).filter(w => w.length > 3);
    const allSuggestions = [...new Set([...typeTags, ...catTags.slice(0, 5), ...titleWords.slice(0, 3)])];
    const newTags = allSuggestions.filter(t => !data.tags.includes(t));
    setData('tags', [...new Set([...data.tags, ...newTags])].slice(0, 15));
    setIsGeneratingTags(false);
    toast.success(`${newTags.length} AI-suggested tags added.`);
  }, [data.categoryId, data.title, data.tags, categories]);

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

    const orderedImages = [...dealImages];
    const newFiles = orderedImages.map((img) => img.file);
    const imageOrder = orderedImages.map((_, index) => `new:${index}`);
    const featuredIndex = orderedImages.findIndex((img) => img.id === featuredImageId);
    const featuredKey = featuredIndex >= 0 ? `new:${featuredIndex}` : (imageOrder[0] ?? '');

    transform((form) => ({
      ...form,
      images: newFiles,
      image_order: JSON.stringify(imageOrder),
      featured_image_key: featuredKey,
    }));

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
            <CardDescription>
              Add up to 8 images, drag to reorder, and star one as featured.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-5">
            <div>
              <Label className="mb-2 block text-sm font-medium">Deal Images (max 8)</Label>
              <input
                ref={imagesInputRef}
                type="file"
                accept="image/*"
                multiple
                className="hidden"
                onChange={handleDealImages}
              />
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {dealImages.map((img, index) => (
                  <div
                    key={img.id}
                    draggable
                    onDragStart={() => setDraggingImageId(img.id)}
                    onDragOver={(e) => e.preventDefault()}
                    onDrop={() => {
                      if (draggingImageId) moveImage(draggingImageId, img.id);
                      setDraggingImageId(null);
                    }}
                    className="relative aspect-square rounded-lg overflow-hidden border group bg-muted"
                  >
                    <img src={img.preview} alt={`Deal ${index + 1}`} className="w-full h-full object-cover" />
                    <div className="absolute top-1 left-1 flex items-center gap-1">
                      <button
                        type="button"
                        onClick={() => setFeaturedImageId(img.id)}
                        className={`rounded-full p-1 ${featuredImageId === img.id ? 'bg-amber-500 text-white' : 'bg-black/60 text-white'}`}
                        title="Mark as featured"
                      >
                        <Star className="h-3.5 w-3.5" fill={featuredImageId === img.id ? 'currentColor' : 'none'} />
                      </button>
                      <span className="rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">
                        #{index + 1}
                      </span>
                    </div>
                    <div className="absolute top-1 right-1 flex items-center gap-1">
                      <span className="rounded-full bg-black/60 p-1 text-white" title="Drag to reorder">
                        <GripVertical className="h-3.5 w-3.5" />
                      </span>
                      <button
                        type="button"
                        onClick={() => removeImage(img.id)}
                        className="rounded-full bg-black/60 p-1 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Remove image"
                      >
                        <X className="h-3.5 w-3.5" />
                      </button>
                    </div>
                    {featuredImageId === img.id && (
                      <div className="absolute bottom-1 left-1 rounded bg-amber-500/90 px-1.5 py-0.5 text-[10px] font-medium text-white">
                        Featured
                      </div>
                    )}
                  </div>
                ))}
                {dealImages.length < 8 && (
                  <button
                    type="button"
                    onClick={() => imagesInputRef.current?.click()}
                    className="aspect-square border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:border-primary hover:text-primary transition-colors"
                  >
                    <Upload className="h-5 w-5" />
                    <span className="text-xs">Add Images</span>
                  </button>
                )}
              </div>
              <p className="mt-2 text-xs text-muted-foreground">
                Featured image is the starred one. If no star is selected, first image becomes featured.
              </p>
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

              <div className="space-y-2">
                <Label>Base price (Rs.) *</Label>
                <div className="relative">
                  <span className="absolute left-3 top-2.5 text-muted-foreground">Rs.</span>
                  <Input
                    type="number"
                    placeholder="0.00"
                    className="pl-12"
                    value={data.basePrice}
                    onChange={(e) => setData('basePrice', e.target.value)}
                    required
                  />
                </div>
                <p className="text-[10px] text-muted-foreground">
                  This is the original price shown when no offers exist. Offers will use this price automatically.
                </p>
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
