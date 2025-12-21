export interface CourtSchedule {
  id: number;
  court_id: number;
  user_id: number | null;
  date: string;
  start_time: string;
  end_time: string;
  price: number;
  status: 'available' | 'booked' | 'closed';
  created_at: string;
  updated_at: string;
  court: {
    id: number;
    venue_id: number;
    name: string;
    court_type: string;
    price_per_hour: number;
    capacity: number;
    status: string;
    image: string | null;
    created_at: string;
    updated_at: string;
    venue: {
      id: number;
      slug: string;
      name: string;
    };
  };
  user: {
    id: number;
    name: string;
    email: string;
  } | null;
}

export interface Court {
  id: number;
  venue_id: number;
  name: string;
  court_type: string;
  price_per_hour: number;
  capacity: number;
  status: string;
  image: string | null;
  created_at: string;
  updated_at: string;
  venue: {
    id: number;
    slug: string;
    name: string;
  };
}

export interface User {
  id: number;
  name: string;
  email: string;
}
