import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/CreateCase.css';

const EditClient = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
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

  useEffect(() => {
    loadClient();
  }, [id]);

  const loadClient = async () => {
    try {
      const response = await legalApi.clients.getById(id);
      if (response.success) {
        const client = response.data;
        setFormData({
          name: client.name || '',
          email: client.email || '',
          phone: client.phone || '',
          address: client.address || '',
          client_type: client.client_type || 'Individual',
          status: client.status || 'Active',
          company: client.company || '',
          id_number: client.id_number || '',
          notes: client.notes || ''
        });
        setLoading(false);
      } else {
        setError('Client not found');
        setLoading(false);
      }
    } catch (err) {
      console.error('Error loading client:', err);
      setError('Failed to load client');
      setLoading(false);
    }
  };

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
    setSaving(true);

    try {
      if (!formData.name || !formData.phone) {
        setError('Name and Phone are required');
        setSaving(false);
        return;
      }

      const response = await legalApi.clients.update(id, formData);

      if (response.success) {
        setSuccess(`Client ${formData.name} updated successfully!`);
        setTimeout(() => {
          navigate('/legal/clients');
        }, 2000);
      } else {
        setError(response.message || 'Failed to update client');
      }
    } catch (err) {
      console.error('Error updating client:', err);
      setError(err.message || 'An error occurred while updating the client');
    } finally {
      setSaving(false);
    }
  };

  const handleCancel = () => {
    navigate('/legal/clients');
  };

  if (loading) {
    return (
      <div className="create-case-container">
        <div className="loading">Loading client data...</div>
      </div>
    );
  }

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>✏️ Edit Client</h1>
        <p>Update client information</p>
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
              required
            />
          </div>

          <div className="form-group">
            <label>Email Address</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
            />
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
              required
            />
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
            />
          </div>

          <div className="form-group">
            <label>ID/Passport Number</label>
            <input
              type="text"
              name="id_number"
              value={formData.id_number}
              onChange={handleChange}
            />
          </div>
        </div>

        {/* Address */}
        <div className="form-group">
          <label>Address</label>
          <textarea
            name="address"
            value={formData.address}
            onChange={handleChange}
            rows="3"
          />
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
              <option value="VIP">VIP</option>
              <option value="Inactive">Inactive</option>
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
            rows="4"
          />
        </div>

        {/* Form Actions */}
        <div className="form-actions">
          <button
            type="button"
            onClick={handleCancel}
            className="btn btn-secondary"
            disabled={saving}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={saving}
          >
            {saving ? '⏳ Updating Client...' : '✅ Update Client'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default EditClient;
