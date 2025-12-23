import { createContext, useContext, useState, useCallback } from 'react';

const ConfirmContext = createContext(null);

export function useConfirm() {
    const context = useContext(ConfirmContext);
    if (!context) {
        throw new Error('useConfirm must be used within a ConfirmProvider');
    }
    return context;
}

export function ConfirmProvider({ children }) {
    const [dialog, setDialog] = useState(null);

    const confirm = useCallback(({
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        type = 'danger', // 'danger', 'warning', 'info'
        onConfirm,
        onCancel,
    }) => {
        setDialog({
            title,
            message,
            confirmText,
            cancelText,
            type,
            onConfirm,
            onCancel,
        });
    }, []);

    const handleConfirm = () => {
        if (dialog?.onConfirm) {
            dialog.onConfirm();
        }
        setDialog(null);
    };

    const handleCancel = () => {
        if (dialog?.onCancel) {
            dialog.onCancel();
        }
        setDialog(null);
    };

    return (
        <ConfirmContext.Provider value={confirm}>
            {children}
            {dialog && (
                <ConfirmDialog
                    {...dialog}
                    onConfirm={handleConfirm}
                    onCancel={handleCancel}
                />
            )}
        </ConfirmContext.Provider>
    );
}

function ConfirmDialog({ title, message, confirmText, cancelText, type, onConfirm, onCancel }) {
    const icons = {
        danger: (
            <div className="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
        ),
        warning: (
            <div className="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg className="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        ),
        info: (
            <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        ),
    };

    const confirmButtonStyles = {
        danger: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        warning: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500',
        info: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    };

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center">
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-black/50 backdrop-blur-sm"
                onClick={onCancel}
            />

            {/* Dialog */}
            <div
                className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                style={{
                    animation: 'scaleIn 0.2s ease-out',
                }}
            >
                <div className="p-6 text-center">
                    {icons[type]}

                    <h3 className="text-lg font-semibold text-gray-900 mb-2">
                        {title}
                    </h3>

                    <p className="text-sm text-gray-600 mb-6">
                        {message}
                    </p>

                    <div className="flex gap-3">
                        <button
                            onClick={onCancel}
                            className="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300"
                        >
                            {cancelText}
                        </button>
                        <button
                            onClick={onConfirm}
                            className={`flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 ${confirmButtonStyles[type]}`}
                        >
                            {confirmText}
                        </button>
                    </div>
                </div>

                <style>{`
                    @keyframes scaleIn {
                        from {
                            transform: scale(0.95);
                            opacity: 0;
                        }
                        to {
                            transform: scale(1);
                            opacity: 1;
                        }
                    }
                `}</style>
            </div>
        </div>
    );
}

export default ConfirmProvider;
