import { useState, useRef, useCallback, useEffect } from 'react';
import { useForm, usePage, router } from '@inertiajs/react';
import Link from '@/components/Link';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import {
  ArrowLeft, X, Sparkles, Loader2,
  Bold, Italic, List, ListOrdered,
  Tag, Info, Save,
  ImagePlus, Star, GripVertical
} from 'lucide-react';
import DashboardLayout from '@/layouts/DashboardLayout';

// AI Suggest deterministic: tags come from backend inference only.

type DealImageItem = {
  id: string;
  preview: string;
  existingId?: number;
  file?: File;
};

const EditDeal = () => {
  const { categories, deal: existingDeal, vendorDefaults } = usePage().props as any;

  const { data, setData } = useForm({
    title: existingDeal?.title || '',
    shortDesc: existingDeal?.shortDesc || '',
    description: existingDeal?.description || '',
    categoryId: existingDeal?.categoryId?.toString() || categories?.[0]?.id?.toString() || '',
    businessType: vendorDefaults?.businessType ?? '',
    district: vendorDefaults?.district ?? '',
    tole: vendorDefaults?.tole ?? '',
    tags: (existingDeal?.tags || []) as string[],
    basePrice: existingDeal?.basePrice || '',
    maxQuantity: existingDeal?.maxQuantity || '',
    status: existingDeal?.status || 'active',
    requestFeatured: existingDeal?.requestFeatured || false,
  });
  const [submitting, setSubmitting] = useState(false);

  // Existing images from DB
  const existingImages: any[] = existingDeal?.images || [];
  const sortedExisting = [...existingImages].sort(
    (a: any, b: any) => Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0)
  );
  const [dealImages, setDealImages] = useState<DealImageItem[]>(
    sortedExisting.map((img: any) => ({
      id: `existing:${img.id}`,
      existingId: img.id,
      preview: img.image_url,
    }))
  );
  const [featuredImageId, setFeaturedImageId] = useState<string | null>(() => {
    const explicit = sortedExisting.find((img: any) => img.attribute_name === 'feature_photo');
    if (explicit) return `existing:${explicit.id}`;
    return sortedExisting[0] ? `existing:${sortedExisting[0].id}` : null;
  });
  const [draggingImageId, setDraggingImageId] = useState<string | null>(null);

  const imagesInputRef = useRef<HTMLInputElement>(null);

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

    try {
      const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content;
      const res = await fetch('/vendor/deals/suggest-metadata', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf || '',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          title: data.title,
          description: data.description,
        }),
      });

      if (!res.ok) throw new Error(`AI suggest failed: ${res.status}`);

      const json = await res.json();
      const suggestion = json?.suggestion;

      if (suggestion?.categoryId) {
        setData('categoryId', suggestion.categoryId.toString());
      }

      if (suggestion?.businessType) setData('businessType', suggestion.businessType);
      if (suggestion?.district) setData('district', suggestion.district);
      if (suggestion?.tole) setData('tole', suggestion.tole);

      if (Array.isArray(suggestion?.tags)) {
        const merged = [...data.tags, ...suggestion.tags].map((t) => t.toString()).map((t) => t.trim()).filter(Boolean);
        setData('tags', [...new Set(merged)].slice(0, 15));
      }

      toast.success('AI metadata suggested (tags/category/location).');
    } catch (e) {
      // Minimal fallback: add address/business type tags (no static chips).
      const normalize = (v: string) =>
        v
          .toString()
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-|-$/g, '');

      const addressTags = [data.businessType, data.district, data.tole]
        .filter(Boolean)
        .map((v) => normalize(v as string))
        .filter(Boolean);

      setData('tags', [...new Set([...data.tags, ...addressTags])].slice(0, 15));
      toast.error('AI suggest failed, added only address tags.');
    } finally {
      setIsGeneratingTags(false);
    }
  }, [data.categoryId, data.title, data.tags, data.description, data.businessType, data.district, data.tole, categories]);

  const addTag = () => {
    const t = tagInput.trim().toLowerCase().replace(/\s+/g, '-');
    if (t && !data.tags.includes(t)) {
      setData('tags', [...data.tags, t]);
      setTagInput('');
    }
  };

  const removeTag = (tag: string) => setData('tags', data.tags.filter(t => t !== tag));
  const clearAllTags = () => setData('tags', []);

  const execCommand = (command: string, target: 'shortDesc' | 'description', value?: string) => {
    document.execCommand(command, false, value);
    if (target === 'shortDesc' && shortEditorRef.current) {
      setData('shortDesc', shortEditorRef.current.innerHTML);
    } else if (target === 'description' && editorRef.current) {
      setData('description', editorRef.current.innerHTML);
    }
  };

  const handleDealImages = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files) return;

    const available = Math.max(0, 8 - dealImages.length);
    if (available <= 0) return;

    const incoming: DealImageItem[] = Array.from(files)
      .slice(0, available)
      .map((file) => ({
        id: `new:${Date.now()}-${Math.random().toString(36).slice(2)}`,
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
    if (target?.file && target.preview) {
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

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (submitting) return;
    setSubmitting(true);

    const ordered = [...dealImages].slice(0, 8);
    const orderKeys: string[] = [];
    const newFiles: File[] = [];
    let newIdx = 0;
    let featuredKey = '';

    ordered.forEach((img) => {
      let key = '';
      if (img.existingId) {
        key = `existing:${img.existingId}`;
      } else if (img.file) {
        key = `new:${newIdx}`;
        newFiles.push(img.file);
        newIdx += 1;
      }
      if (!key) return;
      orderKeys.push(key);
      if (img.id === featuredImageId) {
        featuredKey = key;
      }
    });

    if (!featuredKey) {
      featuredKey = orderKeys[0] ?? '';
    }

    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('title', data.title);
    fd.append('shortDesc', data.shortDesc);
    fd.append('description', data.description);
    fd.append('categoryId', String(data.categoryId));
    data.tags.forEach((t, i) => fd.append(`tags[${i}]`, t));
    fd.append('basePrice', String(data.basePrice));
    fd.append('maxQuantity', String(data.maxQuantity));
    fd.append('status', data.status);
    fd.append('requestFeatured', data.requestFeatured ? '1' : '0');
    fd.append('business_type', data.businessType ?? '');
    fd.append('district', data.district ?? '');
    fd.append('tole', data.tole ?? '');
    newFiles.forEach((file, i) => fd.append(`images[${i}]`, file));
    fd.append('image_order', JSON.stringify(orderKeys));
    fd.append('featured_image_key', featuredKey);

    router.post(`/vendor/deals/${existingDeal.id}`, fd, {
      forceFormData: true,
      onSuccess: () => toast.success('Deal updated successfully!'),
      onFinish: () => setSubmitting(false),
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
                        <X className="h-3 w-3" />
                      </button>
                    </div>
                    {featuredImageId === img.id && (
                      <div className="absolute bottom-1 left-1 rounded bg-amber-500/90 px-1.5 py-0.5 text-[10px] font-medium text-white">
                        Featured
                      </div>
                    )}
                    {img.existingId ? (
                      <div className="absolute bottom-1 right-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">
                        Saved
                      </div>
                    ) : (
                      <div className="absolute bottom-1 right-1 rounded bg-primary/80 px-1.5 py-0.5 text-[10px] text-white">
                        New
                      </div>
                    )}
                  </div>
                ))}
                {dealImages.length < 8 && (
                  <button type="button" onClick={() => imagesInputRef.current?.click()}
                    className="aspect-square border-2 border-dashed rounded-lg flex flex-col items-center justify-center gap-1 text-muted-foreground hover:border-primary hover:text-primary transition-colors">
                    <ImagePlus className="h-5 w-5" />
                    <span className="text-xs">Add Images</span>
                  </button>
                )}
              </div>
              <p className="mt-2 text-xs text-muted-foreground">
                Reorder by drag-and-drop. Featured image is starred; otherwise first image is featured.
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
              <Button type="button" variant="outline" onClick={clearAllTags} disabled={data.tags.length === 0}>
                Clear All
              </Button>
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
          <Button type="submit" size="lg" disabled={submitting} className="px-8 shadow-lg shadow-primary/20">
            {submitting ? (
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
