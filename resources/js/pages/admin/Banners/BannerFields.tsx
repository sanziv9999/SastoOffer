import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { ImageIcon, Upload } from 'lucide-react';
import React from 'react';

export type CategoryOption = { id: number; label: string };

export type BannerFormData = {
  title: string;
  text: string;
  is_featured: boolean;
  sort_order: number | '';
  category_id: number | '';
  image: File | null;
  remove_image: boolean;
};

type Props = {
  data: BannerFormData;
  setData: <K extends keyof BannerFormData>(key: K, value: BannerFormData[K]) => void;
  errors: Partial<Record<keyof BannerFormData | 'image', string>>;
  existingImageUrl?: string | null;
  categoryOptions: CategoryOption[];
};

const NONE = '__none__';

const BannerFields = ({ data, setData, errors, existingImageUrl, categoryOptions }: Props) => {
  const inputRef = React.useRef<HTMLInputElement>(null);
  const [preview, setPreview] = React.useState<string | null>(null);

  React.useEffect(() => {
    if (data.image instanceof File) {
      const url = URL.createObjectURL(data.image);
      setPreview(url);
      return () => URL.revokeObjectURL(url);
    }
    setPreview(null);
    return undefined;
  }, [data.image]);

  const displayUrl = data.remove_image ? null : preview || existingImageUrl || null;

  return (
    <div className="space-y-6">
      <div className="grid gap-6 lg:grid-cols-5">
        <div className="space-y-4 lg:col-span-3">
          <div className="space-y-2">
            <Label htmlFor="banner-title">Title</Label>
            <Input
              id="banner-title"
              value={data.title}
              onChange={(e) => setData('title', e.target.value)}
              placeholder="e.g. Summer flash sale"
              className="text-base"
            />
            {errors.title && <p className="text-xs text-destructive">{errors.title}</p>}
          </div>

          <div className="space-y-2">
            <Label htmlFor="banner-text">Supporting text</Label>
            <Textarea
              id="banner-text"
              value={data.text}
              onChange={(e) => setData('text', e.target.value)}
              placeholder="Short line shown under the title on the homepage."
              rows={4}
              className="resize-y min-h-[100px]"
            />
            {errors.text && <p className="text-xs text-destructive">{errors.text}</p>}
          </div>

          <div className="space-y-2">
            <Label htmlFor="banner-category">Category (optional)</Label>
            <Select
              value={data.category_id === '' || data.category_id === null ? NONE : String(data.category_id)}
              onValueChange={(v) =>
                setData('category_id', v === NONE ? '' : parseInt(v, 10))
              }
            >
              <SelectTrigger id="banner-category" className="w-full">
                <SelectValue placeholder="No category — general promo" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value={NONE}>No category — general promo</SelectItem>
                {categoryOptions.map((opt) => (
                  <SelectItem key={opt.id} value={String(opt.id)}>
                    {opt.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <p className="text-xs text-muted-foreground">
              Link this slide to a category for targeted promotions or deep links later.
            </p>
            {errors.category_id && <p className="text-xs text-destructive">{errors.category_id}</p>}
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="banner-order">Display order</Label>
              <Input
                id="banner-order"
                type="number"
                min={0}
                value={data.sort_order === '' ? '' : data.sort_order}
                onChange={(e) => {
                  const v = e.target.value;
                  setData('sort_order', v === '' ? '' : parseInt(v, 10) || 0);
                }}
              />
              <p className="text-xs text-muted-foreground">Lower numbers appear first in the carousel.</p>
              {errors.sort_order && <p className="text-xs text-destructive">{errors.sort_order}</p>}
            </div>
          </div>

          <div className="flex items-center justify-between rounded-lg border bg-muted/30 px-4 py-3">
            <div className="space-y-0.5">
              <Label htmlFor="banner-featured" className="text-base">
                Show on landing page
              </Label>
              <p className="text-xs text-muted-foreground">Featured banners appear in the homepage hero carousel.</p>
            </div>
            <Switch
              id="banner-featured"
              checked={data.is_featured}
              onCheckedChange={(v) => setData('is_featured', v)}
            />
          </div>
          {errors.is_featured && <p className="text-xs text-destructive">{errors.is_featured}</p>}
        </div>

        <div className="space-y-2 lg:col-span-2">
          <Label>Hero image</Label>
          <div
            className={cn(
              'relative flex min-h-[200px] flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-muted/20 transition-colors',
              'hover:border-primary/40 hover:bg-muted/40',
              errors.image && 'border-destructive'
            )}
          >
            {displayUrl ? (
              <img src={displayUrl} alt="" className="h-full w-full max-h-[280px] object-cover" />
            ) : (
              <div className="flex flex-col items-center gap-2 p-8 text-center text-muted-foreground">
                <ImageIcon className="h-10 w-10 opacity-40" />
                <span className="text-sm">No image yet</span>
              </div>
            )}
          </div>

          <input
            ref={inputRef}
            type="file"
            accept="image/*"
            className="hidden"
            onChange={(e) => {
              const f = e.target.files?.[0] ?? null;
              setData('image', f);
              if (f) setData('remove_image', false);
            }}
          />

          <div className="flex flex-wrap gap-2">
            <button
              type="button"
              className="inline-flex items-center gap-2 rounded-md border bg-background px-3 py-2 text-sm font-medium shadow-sm transition hover:bg-muted"
              onClick={() => inputRef.current?.click()}
            >
              <Upload className="h-4 w-4" />
              {existingImageUrl || preview ? 'Replace image' : 'Upload image'}
            </button>
            {(existingImageUrl || preview) && !data.remove_image && (
              <button
                type="button"
                className="text-sm text-muted-foreground underline-offset-4 hover:underline"
                onClick={() => {
                  setData('image', null);
                  if (inputRef.current) inputRef.current.value = '';
                  if (existingImageUrl) setData('remove_image', true);
                }}
              >
                Remove
              </button>
            )}
          </div>
          {errors.image && <p className="text-xs text-destructive">{errors.image}</p>}
          <p className="text-xs text-muted-foreground">Recommended 1600×600 or wider. Max 5 MB.</p>
        </div>
      </div>
    </div>
  );
};

export default BannerFields;
