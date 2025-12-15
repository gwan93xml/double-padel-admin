export default function EmptyTable() {
    return (
        <div className='flex justify-center flex-col items-center'>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="120"
                height="100"
                viewBox="0 0 120 100"
                fill="none"
            >
                <rect x="20" y="10" width="80" height="80" rx="4" fill="#E2E8F0" />
                <rect x="30" y="20" width="60" height="8" rx="2" fill="#CBD5E0" />
                <rect x="30" y="36" width="40" height="8" rx="2" fill="#CBD5E0" />
                <rect x="30" y="52" width="50" height="8" rx="2" fill="#CBD5E0" />
                <rect x="30" y="68" width="30" height="8" rx="2" fill="#CBD5E0" />
                <circle cx="100" cy="80" r="18" fill="#3B82F6" />
                <path
                    d="M94 80H106M100 74V86"
                    stroke="white"
                    strokeWidth="2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
            </svg>

        </div>
    );
}