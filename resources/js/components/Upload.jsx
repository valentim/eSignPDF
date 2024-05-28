import React, { useState, useEffect, useRef } from 'react';
import Uppy from '@uppy/core';
import { DragDrop, ProgressBar } from '@uppy/react';
import XHRUpload from '@uppy/xhr-upload';
import { ToastContainer, toast } from 'react-toastify';

import '@uppy/core/dist/style.css';
import '@uppy/drag-drop/dist/style.css';
import '@uppy/progress-bar/dist/style.min.css';

const Upload = ({ onUpload }) => {
    const uppyRef = useRef(null);
    const [isUppyReady, setIsUppyReady] = useState(false);

    useEffect(() => {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

        const uppy = new Uppy({
            debug: true,
            allowMultipleUploadBatches: false,
            autoProceed: true,
            restrictions: {
                maxNumberOfFiles: 1,
                allowedFileTypes: ['application/pdf']
            }
        }).use(XHRUpload, {
            endpoint: '/api/documents',
            fieldName: 'file',
            headers: {
                Authorization: `Bearer ${localStorage.getItem('authToken')}`,
                accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            getResponseError(responseText) {
                const response = JSON.parse(responseText);
                if (response.error) {
                    toast.error(response.error);
                    return;
                }

                toast.error('An error occurred while uploading the file. Please try again.');
            },
            formData: true
        });

        uppy.on('complete', (result) => {
            const { successful, failed } = result;
            const files = failed.concat(successful);
            files.forEach(file => {
                uppy.removeFile(file.id);
            });

            if (successful.length > 0) {
                const signingUrl = successful[0].response.body.document.signing_page_url;
                onUpload(signingUrl);
            }
        });

        uppyRef.current = uppy;
        setIsUppyReady(true);

        return () => {
            uppy.close();
        };
    }, [onUpload]);

    return (
        <div>
            {isUppyReady && (
                <>
                    <DragDrop uppy={uppyRef.current} note='PDFs only and up to 10MB' />
                    <ProgressBar uppy={uppyRef.current} hideAfterFinish/>
                    <ToastContainer />
                </>
            )}
        </div>
    );
};

export default Upload;