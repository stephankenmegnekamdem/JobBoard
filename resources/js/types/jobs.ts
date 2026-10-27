export type Company = {
    id: number;
    name: string;
    location?: string | null;
    website?: string | null;
    description?: string | null;
};
export type Job = {
    id: number;
    title: string;
    short_description: string;
    location: string | null;
    salary: string | null;
    working_time: string | null;
    published_at: string | null;
    company: Company;
};
export type JobDetail = Job & {
    description: string;
    keywords: string | null;
    link: string | null;
    image: string | null;
    expires_at: string | null;
    contact: { name: string };
};
export type Paginated<T> = {
    data: T[];
    meta: { current_page: number; last_page: number; total: number };
};
