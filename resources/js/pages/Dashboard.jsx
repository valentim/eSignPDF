import React from 'react';
import Upload from '../components/Upload';
import DocumentList from '../components/DocumentList';

import '@css/app.css';
import 'react-toastify/dist/ReactToastify.css';

const handleUpload = (signingUrl) => {
    window.location.href = signingUrl;
}

const Dashboard = () => {
    return (
        <div>
            <h1>PDF Signature</h1>
            <Upload onUpload={handleUpload} />
            <DocumentList />
        </div>
    );
};

export default Dashboard;
