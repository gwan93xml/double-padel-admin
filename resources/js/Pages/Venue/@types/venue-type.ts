export interface VenueType {
  id?: string | number;
  slug: string;
  name: string;
  description: string;
  province: string;
  city: string;
  address: string;
  latitude: number;
  longitude: number;
  min_price?: number | null;
  max_price?: number | null;
  average_rating?: number | null;
  created_at?: string;
  updated_at?: string;
}
