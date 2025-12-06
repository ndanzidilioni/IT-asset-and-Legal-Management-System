import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/CreateCase.css';

const CreateClient = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    client_type: 'Individual',
    status: 'Active',
    company: '',
    id_number: '',
    notes: ''
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      // Validate required fields
      if (!formData.name || !formData.phone) {
        setError('Name and Phone are required');
        setLoading(false);
        return;
      }

      // Create client
      const response = await legalApi.clients.create(formData);

      if (response.success || response.client) {
        setSuccess(`Client ${formData.name} created successfully!`);
        
        // Reset form and redirect
        setTimeout(() => {
          navigate('/legal/clients');
        }, 2000);
      } else {
        setError(response.message || 'Failed to create client');
      }
    } catch (err) {
      console.error('Error creating client:', err);
      
      // Handle validation errors from backend
      if (err.response && err.response.data && err.response.data.errors) {
        const errors = err.response.data.errors;
        const errorMessages = Object.values(errors).flat().join(', ');
        setError(errorMessages);
      } else {
        setError(err.message || 'An error occurred while creating the client');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate('/legal/clients');
  };

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>👤 Create New Client</h1>
        <p>Add a new client to the system</p>
      </div>

      {error && (
        <div className="alert alert-error">
          ❌ {error}
        </div>
      )}

      {success && (
        <div className="alert alert-success">
          ✅ {success}
        </div>
      )}

      <form onSubmit={handleSubmit} className="case-form">
        {/* Name and Email */}
        <div className="form-row">
          <div className="form-group">
            <label>Full Name <span className="required">*</span></label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              placeholder="e.g., John Smith"
              required
            />
            <small>Client's full legal name</small>
          </div>

          <div className="form-group">
            <label>Email Address</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              placeholder="e.g., john@example.com"
            />
            <small>Client's email (optional)</small>
          </div>
        </div>

        {/* Phone and Client Type */}
        <div className="form-row">
          <div className="form-group">
            <label>Phone Number <span className="required">*</span></label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              placeholder="e.g., +255 712 345 678"
              required
            />
            <small>Primary contact number</small>
          </div>

          <div className="form-group">
            <label>Client Type</label>
            <select
              name="client_type"
              value={formData.client_type}
              onChange={handleChange}
            >
              <option value="Individual">Individual</option>
              <option value="Corporate">Corporate</option>
              <option value="Government">Government</option>
              <option value="NGO">NGO</option>
            </select>
          </div>
        </div>

        {/* Company and ID Number */}
        <div className="form-row">
          <div className="form-group">
            <label>Company Name</label>
            <input
              type="text"
              name="company"
              value={formData.company}
              onChange={handleChange}
              placeholder="e.g., ABC Corporation"
            />
            <small>If corporate client</small>
          </div>

          <div className="form-group">
            <label>ID/Passport Number</label>
            <input
              type="text"
              name="id_number"
              value={formData.id_number}
              onChange={handleChange}
              placeholder="e.g., ID-123456789"
            />
            <small>National ID or passport</small>
          </div>
        </div>

        {/* Address */}
        <div className="form-group">
          <label>Address</label>
          <textarea
            name="address"
            value={formData.address}
            onChange={handleChange}
            placeholder="Full physical address..."
            rows="3"
          />
          <small>Client's physical address</small>
        </div>

        {/* Status */}
        <div className="form-row">
          <div className="form-group">
            <label>Status</label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
            >
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
              <option value="Prospective">Prospective</option>
              <option value="Blacklisted">Blacklisted</option>
            </select>
          </div>

          <div className="form-group">
            {/* Empty space for layout */}
          </div>
        </div>

        {/* Notes */}
        <div className="form-group">
          <label>Notes</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            placeholder="Additional notes about the client..."
            rows="4"
          />
          <small>Internal notes (not visible to client)</small>
        </div>

        {/* Form Actions */}
        <div className="form-actions">
          <button
            type="button"
            onClick={handleCancel}
            className="btn btn-secondary"
            disabled={loading}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? '⏳ Creating Client...' : '✅ Create Client'}
          </button>
        </div>
      </form>

      {/* Help Section */}
      
    </div>
  );
};

export default CreateClient;
