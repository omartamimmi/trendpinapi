import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Transaction {
    id: number;
    reference: string;
    gateway_transaction_id: string;
    amount: number;
    original_amount: number;
    discount_amount: number;
    currency: string;
    status: string;
    gateway: string;
    payment_method: string;
    card_last_four: string | null;
    card_brand: string | null;
    created_at: string;
    completed_at: string | null;
    gateway_response: any;
    user?: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    branch?: {
        id: number;
        name: string;
        brand?: {
            id: number;
            name: string;
            logo_url?: string;
        };
    };
    bank_offer?: {
        id: number;
        title: string;
        offer_type: string;
        offer_value: number;
        bank?: {
            id: number;
            name: string;
            name_ar: string;
        };
    };
    tokenized_card?: {
        id: number;
        card_last_four: string;
        card_brand: string;
        nickname: string;
    };
    qr_session?: {
        id: number;
        session_code: string;
        scanned_at: string;
        expires_at: string;
    };
}

interface Props {
    transaction: Transaction;
}

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-JO', {
        style: 'currency',
        currency: 'JOD',
    }).format(amount);
};

// Status badge component
const StatusBadge = ({ status }: { status: string }) => {
    const colors: Record<string, string> = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        refunded: 'bg-purple-100 text-purple-800',
    };

    return (
        <span className={`px-3 py-1 text-sm font-medium rounded-full ${colors[status] || 'bg-gray-100 text-gray-800'}`}>
            {status.charAt(0).toUpperCase() + status.slice(1)}
        </span>
    );
};

// Info row component
const InfoRow = ({ label, value, valueClass = '' }: { label: string; value: React.ReactNode; valueClass?: string }) => (
    <div className="flex justify-between py-3 border-b border-gray-100 last:border-0">
        <span className="text-sm text-gray-500">{label}</span>
        <span className={`text-sm font-medium text-gray-900 ${valueClass}`}>{value}</span>
    </div>
);

export default function TransactionDetails({ transaction }: Props) {
    return (
        <AdminLayout>
            <div className="max-w-4xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/qr-payment/transactions"
                            className="p-2 rounded-lg hover:bg-gray-100"
                        >
                            <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Transaction Details</h1>
                            <p className="text-sm text-gray-500 mt-1">{transaction.reference}</p>
                        </div>
                    </div>
                    <StatusBadge status={transaction.status} />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Payment Details */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="font-semibold text-gray-900">Payment Details</h2>
                        </div>
                        <div className="p-6">
                            <InfoRow label="Reference" value={transaction.reference} />
                            <InfoRow label="Gateway Transaction ID" value={transaction.gateway_transaction_id || 'N/A'} />
                            <InfoRow label="Gateway" value={transaction.gateway?.toUpperCase()} />
                            <InfoRow label="Payment Method" value={transaction.payment_method?.replace('_', ' ')} />
                            {transaction.card_last_four && (
                                <InfoRow
                                    label="Card"
                                    value={`${transaction.card_brand?.toUpperCase()} **** ${transaction.card_last_four}`}
                                />
                            )}
                            <InfoRow label="Created At" value={new Date(transaction.created_at).toLocaleString()} />
                            {transaction.completed_at && (
                                <InfoRow label="Completed At" value={new Date(transaction.completed_at).toLocaleString()} />
                            )}
                        </div>
                    </div>

                    {/* Amount Details */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="font-semibold text-gray-900">Amount Details</h2>
                        </div>
                        <div className="p-6">
                            {transaction.original_amount !== transaction.amount && (
                                <InfoRow label="Original Amount" value={formatCurrency(transaction.original_amount)} />
                            )}
                            {transaction.discount_amount > 0 && (
                                <InfoRow
                                    label="Discount Applied"
                                    value={`-${formatCurrency(transaction.discount_amount)}`}
                                    valueClass="text-green-600"
                                />
                            )}
                            <InfoRow
                                label="Final Amount"
                                value={formatCurrency(transaction.amount)}
                                valueClass="text-lg font-bold"
                            />
                            <InfoRow label="Currency" value={transaction.currency} />
                        </div>
                    </div>

                    {/* Customer Details */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="font-semibold text-gray-900">Customer Details</h2>
                        </div>
                        <div className="p-6">
                            {transaction.user ? (
                                <>
                                    <InfoRow label="Name" value={transaction.user.name} />
                                    <InfoRow label="Email" value={transaction.user.email} />
                                    <InfoRow label="Phone" value={transaction.user.phone || 'N/A'} />
                                    <InfoRow label="User ID" value={`#${transaction.user.id}`} />
                                </>
                            ) : (
                                <p className="text-sm text-gray-500 text-center py-4">Customer information not available</p>
                            )}
                        </div>
                    </div>

                    {/* Retailer Details */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="font-semibold text-gray-900">Retailer Details</h2>
                        </div>
                        <div className="p-6">
                            {transaction.branch ? (
                                <>
                                    <InfoRow label="Brand" value={transaction.branch.brand?.name || 'N/A'} />
                                    <InfoRow label="Branch" value={transaction.branch.name} />
                                    <InfoRow label="Branch ID" value={`#${transaction.branch.id}`} />
                                </>
                            ) : (
                                <p className="text-sm text-gray-500 text-center py-4">Retailer information not available</p>
                            )}
                        </div>
                    </div>

                    {/* Bank Offer Details */}
                    {transaction.bank_offer && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="font-semibold text-gray-900">Bank Offer Applied</h2>
                            </div>
                            <div className="p-6">
                                <InfoRow label="Offer" value={transaction.bank_offer.title} />
                                <InfoRow label="Bank" value={transaction.bank_offer.bank?.name || 'N/A'} />
                                <InfoRow
                                    label="Discount"
                                    value={
                                        transaction.bank_offer.offer_type === 'percentage'
                                            ? `${transaction.bank_offer.offer_value}%`
                                            : formatCurrency(transaction.bank_offer.offer_value)
                                    }
                                />
                                <InfoRow
                                    label="Amount Saved"
                                    value={formatCurrency(transaction.discount_amount)}
                                    valueClass="text-green-600 font-bold"
                                />
                            </div>
                        </div>
                    )}

                    {/* QR Session Details */}
                    {transaction.qr_session && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="font-semibold text-gray-900">QR Session</h2>
                            </div>
                            <div className="p-6">
                                <InfoRow label="Session Code" value={transaction.qr_session.session_code} />
                                <InfoRow
                                    label="Scanned At"
                                    value={new Date(transaction.qr_session.scanned_at).toLocaleString()}
                                />
                                <InfoRow
                                    label="Expiry Time"
                                    value={new Date(transaction.qr_session.expires_at).toLocaleString()}
                                />
                            </div>
                        </div>
                    )}

                    {/* Saved Card Details */}
                    {transaction.tokenized_card && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="font-semibold text-gray-900">Saved Card Used</h2>
                            </div>
                            <div className="p-6">
                                <InfoRow label="Card Nickname" value={transaction.tokenized_card.nickname || 'N/A'} />
                                <InfoRow
                                    label="Card"
                                    value={`${transaction.tokenized_card.card_brand?.toUpperCase()} **** ${transaction.tokenized_card.card_last_four}`}
                                />
                            </div>
                        </div>
                    )}
                </div>

                {/* Gateway Response (Debug) */}
                {transaction.gateway_response && Object.keys(transaction.gateway_response).length > 0 && (
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h2 className="font-semibold text-gray-900">Gateway Response (Debug)</h2>
                        </div>
                        <div className="p-6">
                            <pre className="bg-gray-50 p-4 rounded-lg overflow-x-auto text-xs text-gray-700">
                                {JSON.stringify(transaction.gateway_response, null, 2)}
                            </pre>
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="flex justify-end gap-3">
                    <Link
                        href="/admin/qr-payment/transactions"
                        className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                        Back to Transactions
                    </Link>
                    {transaction.status === 'completed' && (
                        <button
                            className="px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100"
                            onClick={() => {
                                // TODO: Implement refund
                                alert('Refund functionality coming soon');
                            }}
                        >
                            Refund Transaction
                        </button>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
