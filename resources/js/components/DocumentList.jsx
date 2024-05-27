import React, { useState, useEffect } from 'react';
import { format } from 'date-fns';
import axios from 'axios';
import { ToastContainer, toast } from 'react-toastify';

const DocumentList = () => {
    const [documents, setDocuments] = useState([]);

    useEffect(() => {
        const fetchDocuments = async () => {
            const response = await axios.get('/documents');
            console.log(response)
            setDocuments(response.data);
        };

        fetchDocuments();
    }, []);

    const handleSign = async (documentUuid) => {
        try {
            const response = await axios.post(`/documents/${documentUuid}/sign`);
            window.location.href = response.data.document.signing_page_url;
        } catch (error) {
            toast.error('Error signing the document');
        }
    };

    const handleDownload = async (documentUuid, type) => {
        try {
            const response = await axios.get(`/documents/${documentUuid}/download?type=${type}`);
            window.location.href = response.data.url;
        } catch (error) {
            toast.error('Error downloading the document');
        }
    };

    const handleDelete = async (documentUuid) => {
        try {
            await axios.delete(`/documents/${documentUuid}`);
            setDocuments((prevDocuments) => prevDocuments.filter((document) => document.uuid !== documentUuid));
        } catch (error) {
            toast.error('Error deleting the document');
        }
    }

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return format(date, 'PPpp');
    };

    return (
        <div className="document-list-container">
        {documents.map((document, index) => (
            <div key={index} className="document-list-item">
            <div className="document-icon-name">
                <span className="document-icon">📄</span>
                <span className="document-name">{document.filename}</span>
                <span className="document-date">Uploaded At: {formatDate(document.created_at)}</span>
                {document.signed_at && (
                    <span className="document-date">Signed At: {formatDate(document.signed_at)}</span>
                )}
            </div>
            <div className="document-actions">
            <button
                    className="document-button"
                    onClick={() => document.signed_at ? handleDownload(document.uuid, 'signed') : handleSign(document.uuid)}
                >
                    {document.signed_at ? 'Download Signed PDF' : 'Sign PDF'}
                </button>
                <button
                className="document-button"
                onClick={() => handleDownload(document.uuid, 'original')}
                >
                Download Original
                </button>
                <button
                className="document-button delete"
                onClick={() => handleDelete(document.uuid)}
                >
                Delete
                </button>
            </div>
            </div>
        ))}
        <ToastContainer />
    </div>
    );
};

export default DocumentList;
