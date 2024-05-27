import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { toast } from 'react-toastify';

const AuthCallback = () => {
    const navigate = useNavigate();

    useEffect(() => {
        const fetchToken = async () => {
            try {
                const params = new URLSearchParams(location.search);
                const token = params.get('token');
                const response = await axios.get('/user');
               
                if (!response?.data.id || !token) {
                    navigate('/login');
                    return;
                }

                localStorage.setItem('authToken', token);

                navigate('/dashboard');

            } catch (error) {
                toast.error('Error fetching token');
                navigate('/login');
            }
        };

        fetchToken();
    }, [navigate, location.search]);

    return <div>Loading...</div>;
};

export default AuthCallback;
