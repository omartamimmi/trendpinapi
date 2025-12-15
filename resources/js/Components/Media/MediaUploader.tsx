import { useState, useRef, useCallback } from 'react';
import axios from 'axios';

interface MediaFile {
    id: number;
    file_name: string;
    file_type: string;
    url: string;
    thumbnail_url: string;
}

interface MediaUploaderProps {
    value?: MediaFile | null;
    onChange: (media: MediaFile | null) => void;
    accept?: string;
    maxSize?: number; // in MB
    label?: string;
    placeholder?: string;
    className?: string;
    disabled?: boolean;
    error?: string;
    showPreview?: boolean;
    previewSize?: 'small' | 'medium' | 'large';
}

export default function MediaUploader({
    value,
    onChange,
    accept = 'image/*',
    maxSize = 5,
    label,
    placeholder = 'Click to upload or drag and drop',
    className = '',
    disabled = false,
    error,
    showPreview = true,
    previewSize = 'medium',
}: MediaUploaderProps) {
    const [uploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [dragActive, setDragActive] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const previewSizes = {
        small: 'w-24 h-24',
        medium: 'w-40 h-40',
        large: 'w-64 h-64',
    };

    const handleUpload = useCallback(async (file: File) => {
        if (!file) return;

        // Validate file size
        if (file.size > maxSize * 1024 * 1024) {
            setUploadError(`File size must be less than ${maxSize}MB`);
            return;
        }

        // Validate file type
        const acceptedTypes = accept.split(',').map(t => t.trim());
        const isValidType = acceptedTypes.some(type => {
            if (type === 'image/*') return file.type.startsWith('image/');
            if (type === 'application/pdf') return file.type === 'application/pdf';
            return file.type === type || file.name.endsWith(type.replace('*.', '.'));
        });

        if (!isValidType) {
            setUploadError('Invalid file type');
            return;
        }

        setUploading(true);
        setUploadError(null);
        setUploadProgress(0);

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post('/api/v1/file/store', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        setUploadProgress(progress);
                    }
                },
            });

            const media = response.data.media;
            onChange({
                id: media.id,
                file_name: media.file_name,
                file_type: media.file_type,
                url: media.url,
                thumbnail_url: media.thumbnail_url,
            });
        } catch (err: any) {
            setUploadError(err.response?.data?.message || 'Upload failed');
        } finally {
            setUploading(false);
            setUploadProgress(0);
        }
    }, [accept, maxSize, onChange]);

    const handleDrag = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    }, []);

    const handleDrop = useCallback((e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (disabled) return;

        const file = e.dataTransfer.files?.[0];
        if (file) {
            handleUpload(file);
        }
    }, [disabled, handleUpload]);

    const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            handleUpload(file);
        }
        // Reset input value so same file can be selected again
        e.target.value = '';
    }, [handleUpload]);

    const handleRemove = useCallback(() => {
        onChange(null);
    }, [onChange]);

    const displayError = error || uploadError;

    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {label}
                </label>
            )}

            {/* Preview or Upload Area */}
            {value && showPreview ? (
                <div className="relative inline-block">
                    <div className={`${previewSizes[previewSize]} rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50`}>
                        {value.file_type?.startsWith('image/') ? (
                            <img
                                src={value.url || value.thumbnail_url}
                                alt={value.file_name}
                                className="w-full h-full object-cover"
                            />
                        ) : (
                            <div className="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                <svg className="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span className="text-xs truncate max-w-full px-2">{value.file_name}</span>
                            </div>
                        )}
                    </div>
                    {!disabled && (
                        <button
                            type="button"
                            onClick={handleRemove}
                            className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-md"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    )}
                </div>
            ) : (
                <div
                    className={`relative border-2 border-dashed rounded-xl p-6 transition-all cursor-pointer
                        ${dragActive ? 'border-pink-500 bg-pink-50' : 'border-gray-300 hover:border-pink-400 hover:bg-gray-50'}
                        ${disabled ? 'opacity-50 cursor-not-allowed' : ''}
                        ${displayError ? 'border-red-300 bg-red-50' : ''}
                    `}
                    onDragEnter={handleDrag}
                    onDragLeave={handleDrag}
                    onDragOver={handleDrag}
                    onDrop={handleDrop}
                    onClick={() => !disabled && inputRef.current?.click()}
                >
                    <input
                        ref={inputRef}
                        type="file"
                        accept={accept}
                        onChange={handleChange}
                        disabled={disabled}
                        className="hidden"
                    />

                    <div className="flex flex-col items-center justify-center text-center">
                        {uploading ? (
                            <>
                                <div className="w-12 h-12 mb-3">
                                    <svg className="animate-spin w-full h-full text-pink-500" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                </div>
                                <div className="w-full max-w-xs bg-gray-200 rounded-full h-2 mb-2">
                                    <div
                                        className="bg-pink-500 h-2 rounded-full transition-all"
                                        style={{ width: `${uploadProgress}%` }}
                                    />
                                </div>
                                <span className="text-sm text-gray-500">Uploading... {uploadProgress}%</span>
                            </>
                        ) : (
                            <>
                                <div className={`w-12 h-12 mb-3 rounded-full flex items-center justify-center ${dragActive ? 'bg-pink-100' : 'bg-gray-100'}`}>
                                    <svg className={`w-6 h-6 ${dragActive ? 'text-pink-500' : 'text-gray-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p className="text-sm text-gray-600 mb-1">{placeholder}</p>
                                <p className="text-xs text-gray-400">Max {maxSize}MB</p>
                            </>
                        )}
                    </div>
                </div>
            )}

            {displayError && (
                <p className="mt-2 text-sm text-red-500">{displayError}</p>
            )}
        </div>
    );
}
