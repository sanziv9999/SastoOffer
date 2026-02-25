
export type UserRole = 'visitor' | 'user' | 'vendor' | 'admin' | 'super_admin';

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  avatar?: string;
  createdAt: Date;
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  icon?: string;
  parentId?: string;
}

export interface Location {
  id: string;
  name: string;
  address: string;
  city: string;
  state: string;
  country: string;
  zipCode: string;
  latitude?: number;
  longitude?: number;
}

export interface Vendor {
  id: string;
  userId: string;
  businessName: string;
  description: string;
  logo?: string;
  coverImage?: string;
  contactEmail: string;
  contactPhone?: string;
  website?: string;
  socialLinks?: {
    facebook?: string;
    twitter?: string;
    instagram?: string;
    linkedin?: string;
  };
  location?: Location;
  averageRating: number;
  createdAt: Date;
}

export type DealType = 'percentage' | 'fixed' | 'bogo' | 'bundle' | 'flash';
export type DealStatus = 'draft' | 'pending' | 'active' | 'expired' | 'rejected';

export interface Deal {
  id: string;
  title: string;
  description: string;
  shortDescription: string;
  vendorId: string;
  categoryId: string;
  type: DealType;
  originalPrice: number;
  discountedPrice: number;
  discountPercentage?: number;
  startDate: Date;
  endDate: Date;
  image: string;
  images?: string[];
  maxQuantity?: number;
  quantitySold?: number;
  locationId?: string;
  status: DealStatus;
  featured: boolean;
  tags?: string[];
  averageRating?: number;
  redemptionInstructions?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface Purchase {
  id: string;
  userId: string;
  dealId: string;
  quantity: number;
  totalPrice: number;
  couponCode: string;
  status: 'pending' | 'completed' | 'refunded' | 'cancelled';
  redeemed: boolean;
  redeemedAt?: Date;
  createdAt: Date;
}

export interface Review {
  id: string;
  userId: string;
  dealId: string;
  vendorId: string;
  rating: number;
  comment: string;
  images?: string[];
  createdAt: Date;
  updatedAt: Date;
}
