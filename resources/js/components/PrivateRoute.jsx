import React from 'react';
import { Navigate } from 'react-router-dom';

const PrivateRoute = ({ page: Page }) => {
    const isAuthenticated = !!localStorage.getItem('authToken');

    return isAuthenticated ? <Page /> : <Navigate to="/login" />;
};

export default PrivateRoute;
