
export default function buildQueryParams(obj: Record<string, any>) {
    const query = new URLSearchParams();

    Object.entries(obj).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            value.forEach(v => {
                query.append(`${key}[]`, v);
            });
        } else if (value !== undefined && value !== null) {
            query.append(key, value);
        }
    });
    return query.toString();
}