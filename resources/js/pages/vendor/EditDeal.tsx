import { useState, useRef, useCallback, useEffect } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import Link from '@/components/Link';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import {
  ArrowLeft, X, Sparkles, Loader2,
  Bold, Italic, List, ListOrdered,
  Tag, Info, Save,
  Upload, ImagePlus
} from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

const SUGGESTED_TAGS_MAP: Record<string, string[]> = {
  food: ['restaurant', 'dining', 'gourmet', 'lunch', 'dinner', 'brunch', 'takeout', 'delivery', 'cuisine', 'foodie'],
  beauty: ['spa', 'salon', 'skincare', 'wellness', 'massage', 'facial', 'haircut', 'manicure', 'grooming', 'beauty-treatment'],
  travel: ['hotel', 'vacation', 'getaway', 'resort', 'flight', 'booking', 'adventure', 'tourism', 'weekend-trip', 'staycation'],
  electronics: ['gadget', 'tech', 'smartphone', 'laptop', 'accessories', 'smart-home', 'wearable', 'audio', 'gaming', 'tablet'],
  entertainment: ['movies', 'concert', 'event', 'tickets', 'show', 'fun', 'nightlife', 'experience', 'activity', 'theme-park'],
};

const EditDeal = () => {
  const { categories, deal: existingDeal } = usePage().props as any;

  const { data, setData, put, processing } = useForm({
    title: existingDeal?.title || '',
    shortDesc: existingDeal?.shortDesc || '',
    description: existingDeal?.description || '',
    categoryId: existingDeal?.categoryId?.toString() || categories?.[0]?.id?.toString() || '',
    tags: (existingDeal?.tags || []) as string[],
    basePrice: existingDeal?.basePrice || '',
    maxQuantity: existingDeal?.maxQuantity || '',
    status: existingDeal?.status || 'active',
    requestFeatured: existingDeal?.requestFeatured || false,
    featurePhoto: null as File | null,
    gallery: [] as File[],
    keptGalleryIds: [] as number[],
  });

  // Existing images from DB
  const existingImages: any[] = existingDeal?.images || [];
  const existingFeature = existingImages.find((img: any) => img.attribute_name === 'feature_photo');
  const existingGallery = existingImages.filter((img: any) => img.attribute_name === 'gallery');

  // New upload previews
  const [featurePreview, setFeaturePreview] = useState<string | null>(null);
  const [galleryPreviews, setGalleryPreviews] = useState<string[]>([]);
  // Track which existing gallery images the user wants to keep (by id) in form data
  useEffect(() => {
    const ids = existingGallery.map((img: any) => img.id);
    setData('keptGalleryIds', ids);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [existingDeal?.id]);

  const featureInputRef = useRef<HTMLInputElement>(null);
  const galleryInputRef = useRef<HTMLInputElement>(null);

  const [tagInput, setTagInput] = useState('');
  const [isGeneratingTags, setIsGeneratingTags] = useState(false);

  const shortEditorRef = useRef<HTMLDivElement>(null);
  const editorRef = useRef<HTMLDivElement>(null);

  // Set rich text editor content once on mount
  useEffect(() => {
    if (shortEditorRef.current && existingDeal?.shortDesc) {
      shortEditorRef.current.innerHTML = existingDeal.shortDesc;
    }
    if (editorRef.current && existingDeal?.description) {
      editorRef.current.innerHTML = existingDeal.description;
    }
  }, []);

  const generateAITags = useCallback(async () => {
    setIsGeneratingTags(true);
    await new Promise(r => setTimeout(r, 800));
    const selectedCategory = categories?.find((c: any) => c.id.toString() === data.categoryId.toString());
    const categorySlug = selectedCategory?.slug || 'food';
    const catTags = SUGGESTED_TAGS_MAP[categorySlug] || SUGGESTED_TAGS_MAP.food;
    const titleWords = data.title.toLowerCase().split(/\s+/).filter((w: string) => w.length > 3);
    const allSuggestions = [...new Set([...catTags.slice(0, 5), ...titleWords.slice(0, 3)])];
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
      const combined = [...data.gallery, ...newFiles].slice(0, 8 - (data.keptGalleryIds?.length ?? 0));
      setData('gallery', combined);
      newFiles.forEach(file => {
        const reader = new FileReader();
        reader.onloadend = () => setGalleryPreviews(prev => [...prev, reader.result as string].slice(0, 8));
        reader.readAsDataURL(file);
      });
    }
  };

  const removeNewGalleryImage = (index: number) => {
    setData('gallery', data.gallery.filter((_: File, i: number) => i !== index));
    setGalleryPreviews(prev => prev.filter((_, i) => i !== index));
  };

  const removeExistingGalleryImage = (id: number) => {
    setData('keptGalleryIds', (data.keptGalleryIds ?? []).filter((kept: number) => kept !== id));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(`/vendor/deals/${existingDeal.id}`, {
      forceFormData: true,
      onSuccess: () => toast.success('Deal updated successfully!'),
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" asChild>
          <Link href="/vendor/deals"><ArrowLeft className="h-4 w-4" /></Link>
        </Button>
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Edit Deal</h1>
          <p className="text-muted-foreground">Update your deal details</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">

        {/* Photos */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><ImagePlus className="h-5 w-5 text-primary" /> Photos</CardTitle>
          </CardHeader>
          <CardContent className="space-y-5">
            {/* Feature Photo */}
            <div>
              <Label className="mb-2 block text-sm font-medium">Feature Photo</Label>
              <input ref={featureInputRef} type="file" accept="image/*" className="hidden" onChange={handleFeaturePhoto} />
              {featurePreview ? (
                <div className="relative w-full max-w-md rounded-xl overflow-hidden border group">
                  <img src={featurePreview} alt="Feature" className="w-full h-52 object-cover" />
                  <button type="button"
                    onClick={() => { setData('featurePhoto', null); setFeaturePreview(null); }}
                    className="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <X className="h-4 w-4" />
                  </button>
                  <div className="absolute bottom-2 left-2">
                    <span className="text-xs bg-black/60 text-white px-2 py-0.5 rounded">New image</span>
                  </div>
                </div>
              ) : existingFeature ? (
                <div className="relative w-full max-w-md rounded-xl overflow-hidden border group">
                  <img src={existingFeature.image_url} alt="Feature" className="w-full h-52 object-cover" />
                  <button type="button"
                    onClick={() => featureInputRef.current?.click()}
                    className="absolute inset-0 bg-black/0 hover:bg-black/40 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <span className="text-white font-medium text-sm bg-black/60 px-3 py-1.5 rounded-lg">
                      <Upload className="h-4 w-4 inline mr-1" />Change Photo
                    </span>
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

            {/* Gallery */}
            <div>
              <Label className="mb-2 block text-sm font-medium">Gallery (up to 8 images)</Label>
              <input ref={galleryInputRef} type="file" accept="image/*" multiple className="hidden" onChange={handleGalleryPhotos} />
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {/* Existing gallery images */}
                {existingGallery
                  .filter((img: any) => (data.keptGalleryIds ?? []).includes(img.id))
                  .map((img: any) => (
                    <div key={img.id} className="relative aspect-square rounded-lg overflow-hidden border group">
                      <img src={img.image_url} alt="Gallery" className="w-full h-full object-cover" />
                      <button type="button" onClick={() => removeExistingGalleryImage(img.id)}
                        className="absolute top-1 right-1 bg-black/60 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <X className="h-3 w-3" />
                      </button>
                      <div className="absolute bottom-1 left-1">
                        <span className="text-[10px] bg-black/60 text-white px-1.5 py-0.5 rounded">Saved</span>
                      </div>
                    </div>
                  ))}
                {/* New images */}
                {galleryPreviews.map((img, i) => (
                  <div key={`new-${i}`} className="relative aspect-square rounded-lg overflow-hidden border group">
                    <img src={img} alt={`New ${i + 1}`} className="w-full h-full object-cover" />
                    <button type="button" onClick={() => removeNewGalleryImage(i)}
                      className="absolute top-1 right-1 bg-black/60 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                      <X className="h-3 w-3" />
                    </button>
                    <div className="absolute bottom-1 left-1">
                      <span className="text-[10px] bg-primary/80 text-white px-1.5 py-0.5 rounded">New</span>
                    </div>
                  </div>
                ))}
                {/* Add more button */}
                {(((data.keptGalleryIds ?? []).length + galleryPreviews.length) < 8) && (
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
              <Input placeholder="Catchy title" value={data.title} onChange={e => setData('title', e.target.value)} required />
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
                  onInput={() => shortEditorRef.current && setData('shortDesc', shortEditorRef.current.innerHTML)}
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
                  onInput={() => editorRef.current && setData('description', editorRef.current.innerHTML)}
                  className="min-h-[140px] px-3 py-2 text-sm focus:outline-none prose prose-sm max-w-none [&_ul]:list-disc [&_ol]:list-decimal [&_ul]:pl-5 [&_ol]:pl-5" />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Base price *</Label>
                <Input
                  type="number"
                  placeholder="0.00"
                  value={data.basePrice}
                  onChange={(e) => setData('basePrice', e.target.value)}
                  required
                />
                <p className="text-[10px] text-muted-foreground">
                  This is the original price shown when no offers exist. Offers will use this price automatically.
                </p>
              </div>

              <div className="space-y-2">
                <Label>Max Quantity Available</Label>
                <Input
                  type="number"
                  placeholder="Unlimited"
                  value={data.maxQuantity}
                  onChange={e => setData('maxQuantity', e.target.value)}
                />
                <p className="text-[10px] text-muted-foreground">Leave empty for unlimited</p>
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
                Saving...
              </>
            ) : (
              <>
                <Save className="mr-2 h-4 w-4" />
                Save Changes
              </>
            )}
          </Button>
        </div>
      </form>
    </div>
  );
};

EditDeal.layout = (page: React.ReactNode) => <DashboardLayout children={page} />;

export default EditDeal;
