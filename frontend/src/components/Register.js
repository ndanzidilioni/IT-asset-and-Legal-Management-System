import React, { useState, useEffect } from 'react';
import API from '../services/api';
import { Link } from 'react-router-dom';
import './Register.css';

function Register(){
    const [fname,setFname] = useState('');
    const [mname,setMname] = useState('');
    const [lname,setLname] = useState('');
    const [email,setEmail] = useState('');
    const [username,setUsername] = useState('');
    const [password,setPassword] = useState('');
    const [role,setRole] = useState('user');
    const [loading,setLoading] = useState(false);

    const handleSubmit = async e => {
        e.preventDefault();
        setLoading(true);
        try{
            const res = await API.post('/register',{fname,mname,lname,email,username,password,role});
            if(res.data.success) {
                alert(res.data.message || 'Registration successful! Please login with your credentials.');
                window.location.href = '/login';
            }
        }catch(err){
            if(err.response) {
                if(err.response.data.errors && typeof err.response.data.errors === 'object') {
                    // Handle validation errors
                    const errorMessages = Object.values(err.response.data.errors).flat().join('\n');
                    alert('Registration failed:\n' + errorMessages);
                } else {
                    alert(err.response.data.message || `Registration failed: ${err.response.status}`);
                }
            } else {
                alert('Network error. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    }

    // Add register-page class to body for styling
    useEffect(() => {
        document.body.classList.add('register-page');
        return () => {
            document.body.classList.remove('register-page');
        };
    }, []);

    return (
        <div className="register-container">
          {/* Left Side - Organization Branding */}
          <div className="register-branding">
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
                <h3>Join Our System</h3>
                <p>Create your account to access our integrated management platform</p>
                <div className="features-list">
                  <div className="feature-item">✓ Legal Case Management</div>
                  <div className="feature-item">✓ IT Asset Tracking</div>
                  <div className="feature-item">✓ Administrative Tools</div>
                </div>
              </div>
            </div>
          </div>
          
          {/* Right Side - Registration Form */}
          <div className="register-form-section">
            <div className="register-form-container">
              <div className="form-header">
                <h2>Create Account</h2>
                <p>Join Muhimbili Orthopaedic Institute</p>
              </div>
              
              <form onSubmit={handleSubmit} className="register-form">
                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="fname">First Name *</label>
                    <input 
                      id="fname"
                      type="text"
                      placeholder="Enter your first name" 
                      value={fname} 
                      onChange={e=>setFname(e.target.value)} 
                      required 
                      className="form-input"
                    />
                  </div>
                  
                  <div className="form-group">
                    <label htmlFor="mname">Middle Name</label>
                    <input 
                      id="mname"
                      type="text"
                      placeholder="Enter your middle name" 
                      value={mname} 
                      onChange={e=>setMname(e.target.value)} 
                      className="form-input"
                    />
                  </div>
                </div>
                
                <div className="form-group">
                  <label htmlFor="lname">Last Name *</label>
                  <input 
                    id="lname"
                    type="text"
                    placeholder="Enter your last name" 
                    value={lname} 
                    onChange={e=>setLname(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <div className="form-group">
                  <label htmlFor="email">Email Address *</label>
                  <input 
                    id="email"
                    type="email"
                    placeholder="Enter your email address" 
                    value={email} 
                    onChange={e=>setEmail(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <div className="form-group">
                  <label htmlFor="username">Username *</label>
                  <input 
                    id="username"
                    type="text"
                    placeholder="Choose a username" 
                    value={username} 
                    onChange={e=>setUsername(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <div className="form-group">
                  <label htmlFor="password">Password *</label>
                  <input 
                    id="password"
                    type="password"
                    placeholder="Create a secure password" 
                    value={password} 
                    onChange={e=>setPassword(e.target.value)} 
                    required 
                    className="form-input"
                  />
                </div>
                
                <div className="form-group">
                  <label htmlFor="role">Account Type</label>
                  <select 
                    id="role"
                    value={role} 
                    onChange={e=>setRole(e.target.value)}
                    className="form-select"
                  >
                    <option value="user">Standard User</option>
                    <option value="admin">Administrator</option>
                    <option value="lawyer">Legal Professional</option>
                    <option value="ict">ICT Specialist</option>
                  </select>
                </div>
                
                <button type="submit" className="register-button" disabled={loading}>
                  {loading ? 'Creating Account...' : 'Create Account'}
                </button>
                
                <div className="form-footer">
                  <Link to="/login" className="login-link">
                    Already have an account? Sign in here
                  </Link>
                </div>
              </form>
              
              <div className="security-notice">
                <p>🔒 Your information is secure and will be used in accordance with our privacy policy</p>
              </div>
            </div>
          </div>
        </div>
    )
}

export default Register;
