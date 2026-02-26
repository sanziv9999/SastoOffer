@php
    $images = $imageable->relationLoaded('images') ? $imageable->images : $imageable->images()->orderBy('sort_order')->get();
@endphp
<section class="images-section" style="margin-top: 1.5rem;">
    <h2>{{ $title ?? 'Images' }}</h2>
    <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 1rem;">
        @csrf
        <input type="hidden" name="imageable_type" value="{{ $imageableType }}">
        <input type="hidden" name="imageable_id" value="{{ $imageable->getKey() }}">
        <div class="form-group">
            <label>Type</label>
            <select name="attribute_name" required>
                @foreach ($allowedAttributes as $attr)
                    <option value="{{ $attr }}">{{ ucfirst(str_replace('_', ' ', $attr)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Upload file</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Or image URL</label>
            <input type="url" name="image_url" placeholder="https://...">
        </div>
        <button type="submit">Add image</button>
    </form>
    @if ($images->isNotEmpty())
    <ul style="list-style: none; padding: 0;">
        @foreach ($images as $img)
        <li style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <img src="{{ str_starts_with($img->image_url, 'http') ? $img->image_url : asset($img->image_url) }}" alt="" style="width: 48px; height: 48px; object-fit: cover;">
            <span>{{ $img->attribute_name }}</span>
            <form action="{{ route('images.destroy', $img) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remove this image?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="padding: 0.2rem 0.5rem; font-size: 0.875rem;">Remove</button>
            </form>
        </li>
        @endforeach
    </ul>
    @else
    <p style="color: #6b7280;">No images yet.</p>
    @endif
</section>
