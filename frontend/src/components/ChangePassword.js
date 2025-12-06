import React, { useState } from 'react';
import API from '../services/api';
import './ChangePassword.css';

const ChangePassword = ({ onPasswordChanged, onCancel }) => {
  const [formData, setFormData] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    // Basic validation
    if (formData.new_password !== formData.new_password_confirmation) {
      setError('New passwords do not match');
      setLoading(false);
      return;
    }

    if (formData.new_password.length < 8) {
      setError('New password must be at least 8 characters long');
      setLoading(false);
      return;
    }

    try {
      // Backend gets user from Bearer token, no need to send user_id
      // Must include new_password_confirmation for Laravel validation
      const requestData = {
        current_password: formData.current_password,
        new_password: formData.new_password,
        new_password_confirmation: formData.new_password_confirmation
      };
      
      console.log('🔐 Sending password change request:', {
        ...requestData,
        current_password: '***',
        new_password: '***',
        new_password_confirmation: '***'
      });
      
      const response = await API.post('/change-password', requestData);
      
      if (response.data.success) {
        setSuccess('Password changed successfully!');
        setTimeout(() => {
          onPasswordChanged();
        }, 1500);
      }
    } catch (err) {
      console.error('Password change error:', err);
      if (err.response?.data?.message) {
        setError(err.response.data.message);
        
        // If there are specific validation errors, show them
        if (err.response?.data?.errors && Array.isArray(err.response.data.errors)) {
          const errorList = err.response.data.errors.join('\n• ');
          setError(`${err.response.data.message}\n\n• ${errorList}`);
        }
      } else if (err.response?.data?.errors) {
        // Handle array of errors
        const errors = Array.isArray(err.response.data.errors) 
          ? err.response.data.errors 
          : Object.values(err.response.data.errors).flat();
        setError('Password validation failed:\n• ' + errors.join('\n• '));
      } else {
        setError('Failed to change password. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="change-password-overlay">
      <div className="change-password-modal">
        <div className="modal-header">
          <h2>Change Password</h2>
          <p className="modal-subtitle">
            You must change your password before continuing.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="change-password-form">
          {error && (
            <div className="error-message">
              <span className="error-icon">⚠️</span>
              {error}
            </div>
          )}

          {success && (
            <div className="success-message">
              <span className="success-icon">✅</span>
              {success}
            </div>
          )}

          <div className="form-group">
            <label htmlFor="current_password">Current Password</label>
            <input
              type="password"
              id="current_password"
              name="current_password"
              value={formData.current_password}
              onChange={handleChange}
              required
              disabled={loading}
              placeholder="Enter your current password"
            />
          </div>

          <div className="form-group">
            <label htmlFor="new_password">New Password</label>
            <input
              type="password"
              id="new_password"
              name="new_password"
              value={formData.new_password}
              onChange={handleChange}
              required
              disabled={loading}
              placeholder="Enter your new password (min 8 characters)"
            />
          </div>

          <div className="form-group">
            <label htmlFor="new_password_confirmation">Confirm New Password</label>
            <input
              type="password"
              id="new_password_confirmation"
              name="new_password_confirmation"
              value={formData.new_password_confirmation}
              onChange={handleChange}
              required
              disabled={loading}
              placeholder="Confirm your new password"
            />
          </div>

          <div className="password-requirements">
            <h4>Password Requirements:</h4>
            <ul>
              <li>✅ At least 8 characters long</li>
              <li>✅ One uppercase letter (A-Z)</li>
              <li>✅ One lowercase letter (a-z)</li>
              <li>✅ One number (0-9)</li>
              <li>✅ One special character (!@#$%^&*)</li>
            </ul>
            <p style={{ marginTop: '10px', fontSize: '0.9em', color: '#666' }}>
              <strong>Example valid passwords:</strong> Admin123! • Password123! • MyP@ssw0rd
            </p>
          </div>

          <div className="form-actions">
            <button
              type="submit"
              className="change-password-btn"
              disabled={loading || !formData.current_password || !formData.new_password || !formData.new_password_confirmation}
            >
              {loading ? 'Changing Password...' : 'Change Password'}
            </button>
            {onCancel && (
              <button
                type="button"
                className="cancel-btn"
                onClick={onCancel}
                disabled={loading}
              >
                Cancel
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
};

export default ChangePassword;
