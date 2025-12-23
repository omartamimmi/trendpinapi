import { useState, useRef, useCallback } from 'react';
import axios from 'axios';

interface MediaFile {
    id: number;
    file_name: string;
    file_type: string;
    url: string;
    thumbnail_url: string;
}

interface MediaGalleryProps {
    value: MediaFile[];
    onChange: (media: MediaFile[]) => void;
    accept?: string;
    maxSize?: number; // in MB per file
    maxFiles?: number;
    label?: string;
    placeholder?: string;
    className?: string;
    disabled?: boolean;
    error?: string;
    columns?: 2 | 3 | 4 | 5;
}

export default function MediaGallery({
    value = [],
    onChange,
    accept = 'image/*',
    maxSize = 5,
    maxFiles = 10,
    label,
    placeholder = 'Click to upload or drag images',
    className = '',
    disabled = false,
    error,
    columns = 4,
}: MediaGalleryProps) {
    const [uploading, setUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [dragActive, setDragActive] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const inputRef = useRef<HTMLInputElement>(null);
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

    const columnClasses = {
        2: 'grid-cols-2',
        3: 'grid-cols-3',
        4: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4',
        5: 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5',
    };

    const handleUpload = useCallback(async (files: FileList | File[]) => {
        if (!files || files.length === 0) return;

        const filesToUpload = Array.from(files).slice(0, maxFiles - value.length);

        if (filesToUpload.length === 0) {
            setUploadError(`Maximum ${maxFiles} files allowed`);
            return;
        }

        // Validate files
        for (const file of filesToUpload) {
            if (file.size > maxSize * 1024 * 1024) {
                setUploadError(`Each file must be less than ${maxSize}MB`);
                return;
            }
        }

        setUploading(true);
        setUploadError(null);
        setUploadProgress(0);

        const formData = new FormData();
        filesToUpload.forEach(file => {
            formData.append('files[]', file);
        });

        try {
            const response = await axios.post('/api/v1/file/upload-multiple', formData, {
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

            const uploadedMedia = response.data.media.map((m: any) => ({
                id: m.id,
                file_name: m.file_name,
                file_type: m.file_type,
                url: m.url,
                thumbnail_url: m.thumbnail_url,
            }));

            onChange([...value, ...uploadedMedia]);
        } catch (err: any) {
            setUploadError(err.response?.data?.message || 'Upload failed');
        } finally {
            setUploading(false);
            setUploadProgress(0);
        }
    }, [maxSize, maxFiles, value, onChange]);

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

        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            handleUpload(files);
        }
    }, [disabled, handleUpload]);

    const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            handleUpload(files);
        }
        e.target.value = '';
    }, [handleUpload]);

    const handleRemove = useCallback((index: number) => {
        const newValue = [...value];
        newValue.splice(index, 1);
        onChange(newValue);
    }, [value, onChange]);

    // Drag and drop reordering
    const handleItemDragStart = (e: React.DragEvent, index: number) => {
        setDraggedIndex(index);
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleItemDragOver = (e: React.DragEvent, index: number) => {
        e.preventDefault();
        if (draggedIndex === null || draggedIndex === index) return;

        const newValue = [...value];
        const draggedItem = newValue[draggedIndex];
        newValue.splice(draggedIndex, 1);
        newValue.splice(index, 0, draggedItem);
        onChange(newValue);
        setDraggedIndex(index);
    };

    const handleItemDragEnd = () => {
        setDraggedIndex(null);
    };

    const displayError = error || uploadError;

    return (
        <div className={className}>
            {label && (
                <label className="block text-sm font-medium text-gray-700 mb-2">
                    {label}
                    {maxFiles && (
                        <span className="text-gray-400 font-normal ml-2">
                            ({value.length}/{maxFiles})
                        </span>
                    )}
                </label>
            )}

            {/* Gallery Grid */}
            {value.length > 0 && (
                <div className={`grid ${columnClasses[columns]} gap-3 mb-3`}>
                    {value.map((media, index) => (
                        <div
                            key={media.id}
                            draggable={!disabled}
                            onDragStart={(e) => handleItemDragStart(e, index)}
                            onDragOver={(e) => handleItemDragOver(e, index)}
                            onDragEnd={handleItemDragEnd}
                            className={`relative group aspect-square rounded-xl overflow-hidden border-2 border-gray-200 bg-gray-50 cursor-move transition-all
                                ${draggedIndex === index ? 'opacity-50 scale-95' : 'opacity-100'}
                            `}
                        >
                            {media.file_type?.startsWith('image/') ? (
                                <img
                                    src={media.thumbnail_url || media.url}
                                    alt={media.file_name}
                                    className="w-full h-full object-cover"
                                    draggable={false}
                                />
                            ) : (
                                <div className="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <svg className="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span className="text-xs truncate max-w-full px-2">{media.file_name}</span>
                                </div>
                            )}

                            {/* Order badge */}
                            <div className="absolute top-2 left-2 w-6 h-6 bg-black/60 text-white text-xs rounded-full flex items-center justify-center font-medium">
                                {index + 1}
                            </div>

                            {/* Remove button */}
                            {!disabled && (
                                <button
                                    type="button"
                                    onClick={() => handleRemove(index)}
                                    className="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all shadow-md"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}

                            {/* Drag handle indicator */}
                            <div className="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Upload Area */}
            {value.length < maxFiles && (
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
                        multiple
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
                                <p className="text-xs text-gray-400">
                                    Max {maxSize}MB per file, {maxFiles - value.length} more allowed
                                </p>
                            </>
                        )}
                    </div>
                </div>
            )}

            {displayError && (
                <p className="mt-2 text-sm text-red-500">{displayError}</p>
            )}

            {value.length > 0 && !disabled && (
                <p className="mt-2 text-xs text-gray-400">
                    Drag images to reorder. First image will be the main image.
                </p>
            )}
        </div>
    );
}
