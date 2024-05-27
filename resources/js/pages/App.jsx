import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './Login';
import AuthCallback from '../components/AuthCallback';
import PrivateRoute from '../components/PrivateRoute';
import Dashboard from './Dashboard';

const App = () => {
    return (
        <Router>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/auth" element={<AuthCallback />} />
                <Route path="/dashboard" element={<PrivateRoute page={Dashboard} />} />
                <Route path="/" element={<Navigate to="/dashboard" />} />
            </Routes>
        </Router>
    );
};

export default App;
