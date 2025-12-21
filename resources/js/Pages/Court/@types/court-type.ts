export interface CourtType {
  id?: string | number;
  venue_id: number;
  name: string;
  court_type: string;
  price_per_hour: number;
  capacity: number;
  status: 'available' | 'maintenance' | 'closed';
  image?: string | null;
  created_at?: string;
  updated_at?: string;
  venue?: {
    id: number;
    name: string;
  };
}
