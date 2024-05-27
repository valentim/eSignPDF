import React from 'react';

import '@css/login.css';

const Login = () => {
    console.log('Login');
    const handleLogin = (provider) => {
        window.location.href = `auth/${provider}`;
    };

    return (
        <div className="login-container">
            <div className="login-box">
                <h2 className="login-title">Login</h2>
                <button className="login-button" onClick={ () => handleLogin('google')}>
                    Login with Google
                </button>
            </div>
        </div>
    );
};

export default Login;
