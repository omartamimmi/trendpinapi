import { Link } from '@inertiajs/react';

export default function Pagination({ data }) {
    if (!data.links || data.links.length <= 3) {
        return null;
    }

    return (
        <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div className="flex-1 flex justify-between sm:hidden">
                {data.prev_page_url && (
                    <Link
                        href={data.prev_page_url}
                        className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Previous
                    </Link>
                )}
                {data.next_page_url && (
                    <Link
                        href={data.next_page_url}
                        className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Next
                    </Link>
                )}
            </div>
            <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm text-gray-700">
                        Showing <span className="font-medium">{data.from}</span> to{' '}
                        <span className="font-medium">{data.to}</span> of{' '}
                        <span className="font-medium">{data.total}</span> results
                    </p>
                </div>
                <div>
                    <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        {data.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url || '#'}
                                disabled={!link.url}
                                className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                    link.active
                                        ? 'z-10 bg-pink-50 border-pink-500 text-pink-600'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                } ${index === 0 ? 'rounded-l-md' : ''} ${
                                    index === data.links.length - 1 ? 'rounded-r-md' : ''
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </nav>
                </div>
            </div>
        </div>
    );
}
