import React, { useState, useEffect } from 'react';
import API, { loginWeb, logoutWeb } from '../services/api';
import { Link } from 'react-router-dom';
import ChangePassword from './ChangePassword';
import './Login.css';

function Login(){
    const [username,setUsername] = useState('');
    const [password,setPassword] = useState('');
    const [showChangePassword, setShowChangePassword] = useState(false);

    // Add login-page class to body for styling
    useEffect(() => {
        document.body.classList.add('login-page');
        return () => {
            document.body.classList.remove('login-page');
        };
    }, []);

    const handleSubmit = async e => {
  e.preventDefault();
  console.log('🚀 Login form submitted');
  console.log('📝 Username:', username);
  console.log('📝 Password length:', password.length);
  
  try {
    console.log('🔄 Calling loginWeb...');
    const res = await loginWeb({ login: username, password });
    console.log('🎉 Login response received:', res.data);

    // Check if user must change password
    if (res.data?.must_change_password) {
      setShowChangePassword(true);
      return;
    }

    // Store user info for role-based routing
    if (res.data?.user) {
      localStorage.setItem('userInfo', JSON.stringify(res.data.user));
      console.log('👤 User role:', res.data.user.role);
      
      // Role-based routing
      switch(res.data.user.role) {
        case 'lawyer':
          console.log('🔄 Redirecting lawyer to /legal');
          window.location.href = '/legal';
          break;
        case 'admin':
          console.log('🔄 Redirecting admin to /dashboard');
          window.location.href = '/dashboard';
          break;
        case 'ict':
          console.log('🔄 Redirecting ICT to /it-assets');
          window.location.href = '/it-assets';
          break;
        case 'client':
          console.log('🔄 Redirecting client to /client');
          window.location.href = '/client';
          break;
        default:
          console.log('🔄 Redirecting to default /dashboard');
          window.location.href = '/dashboard';
      }
    } else {
      // Fallback to legal dashboard if no user data
      console.log('🔄 No user data, redirecting to /legal');
      window.location.href = '/legal';
    }
  } catch (err) {
    console.error('💥 Login error caught:', err);
    
    if (err.response) {
      console.error('📡 Server response:', err.response.data);
      alert(err.response.data.message || `Login failed: ${err.response.status}`);
    } else if (err.request) {
      console.error('🌐 Network error - no response received');
      console.error('Request details:', err.request);
      alert('Network error - cannot connect to server. Please check if the backend is running.');
    } else {
      console.error('⚠️ Other error:', err.message);
      alert('Network error.');
    }
  }
}

const handlePasswordChanged = () => {
  // Redirect based on user role after successful password change
  const userInfo = localStorage.getItem('userInfo');
  if (userInfo) {
    const user = JSON.parse(userInfo);
    switch(user.role) {
      case 'lawyer':
        window.location.href = '/legal';
        break;
      case 'admin':
        window.location.href = '/dashboard';
        break;
      case 'ict':
        window.location.href = '/it-assets';
        break;
      case 'client':
        window.location.href = '/client';
        break;
      default:
        window.location.href = '/dashboard';
    }
  } else {
    window.location.href = '/dashboard';
  }
};

const handleCancelPasswordChange = () => {
  // Logout and return to login
  logoutWeb();
  setShowChangePassword(false);
};

    return (
      <>
        <div className="login-container">
          {/* Left Side - Organization Branding */}
          <div className="login-branding">
            <div className="brand-content">
              <div className="logo-section">
                <div className="logo-placeholder">
                  <div className="medical-icon">🏥</div>
                </div>
                <h1 className="organization-name">Muhimbili Orthopaedic Institute</h1>
                <p className="organization-tagline">Excellence in Orthopaedic Care & Research</p>
              </div>
              
              <div className="contact-info">
                <div className="contact-item">
                  <span className="contact-icon">📧</span>
                  <span>info@moi.ac.tz</span>
                </div>
                <div className="contact-item">
                  <span className="contact-icon">📞</span>
                  <span>+255 22 215 2937</span>
                </div>
                <div className="contact-item">
                  <span className="contact-icon">🌐</span>
                  <span>www.moi.ac.tz</span>
                </div>
              </div>
              
              <div className="system-info">
                <h3>Management System</h3>
                <p>Integrated platform for legal, IT assets, and administrative management</p>
              </div>
            </div>
          </div>
          
          {/* Right Side - Login Form */}
          <div className="login-form-section">
            <div className="login-form-container">
              <div className="form-header">
                <h2>Welcome Back</h2>
                <p>Please sign in to your account</p>
              </div>
              
              <form onSubmit={handleSubmit} className="login-form">
                <div className="form-group">
                  <label htmlFor="username">Username or Email</label>
                  <input 
                    id="username"
                    type="text"
                    placeholder="Enter your username or email" 
                    value={username} 
                    onChange={e=>setUsername(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <div className="form-group">
                  <label htmlFor="password">Password</label>
                  <input 
                    id="password"
                    type="password" 
                    placeholder="Enter your password" 
                    value={password} 
                    onChange={e=>setPassword(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <button type="submit" className="login-button">
                  Sign In
                </button>
                
                <div className="form-footer">
                  <Link to="/register" className="register-link">
                    Don't have an account? Register here
                  </Link>
                </div>
              </form>
              
              <div className="security-notice">
                <p>🔒 Your data is protected with enterprise-grade security</p>
              </div>
            </div>
          </div>
        </div>
        
        {showChangePassword && (
          <ChangePassword
            onPasswordChanged={handlePasswordChanged}
            onCancel={handleCancelPasswordChange}
          />
        )}
      </>
    )
}

export default Login;
