export { default as MediaUploader } from './MediaUploader';
export { default as MediaGallery } from './MediaGallery';

export interface MediaFile {
    id: number;
    file_name: string;
    file_type: string;
    url: string;
    thumbnail_url: string;
}
