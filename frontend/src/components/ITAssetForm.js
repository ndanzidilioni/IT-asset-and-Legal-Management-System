import React, { useState, useEffect } from 'react';
import API from '../services/api';
import DropdownWithAdd from './DropdownWithAdd';
import './ITAssetForm.css';

const ITAssetForm = ({ assetId = null, onSuccess, onCancel }) => {
  const [formData, setFormData] = useState({
    asset_number: '',
    asset_description: '',
    building: '',
    floor: '',
    department: '',
    room: '',
    condition: 'good',
    status: 'active',
    notes: ''
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitMessage, setSubmitMessage] = useState('');
  const [isEditMode, setIsEditMode] = useState(false);

  useEffect(() => {
    if (assetId) {
      setIsEditMode(true);
      fetchAsset();
    }
  }, [assetId]);

  const fetchAsset = async () => {
    try {
      console.log('🔍 ITAssetForm fetching asset with ID:', assetId);
      const response = await API.get(`/it-assets/${assetId}`);
      console.log('📋 ITAssetForm fetchAsset response:', response);
      
      if (response.data.success) {
        const asset = response.data.data;
        console.log('✅ ITAssetForm asset data loaded:', asset);
        setFormData({
          asset_number: asset.asset_number || '',
          asset_description: asset.asset_description || '',
          building: asset.building || '',
          floor: asset.floor || '',
          department: asset.department || '',
          room: asset.room || '',
          condition: asset.condition || 'good',
          status: asset.status || 'active',
          notes: asset.notes || ''
        });
      } else {
        console.log('❌ ITAssetForm fetchAsset failed - API returned success=false');
        setSubmitMessage('Error loading asset data: API returned success=false');
      }
    } catch (error) {
      console.error('🚨 ITAssetForm fetchAsset error:', error);
      setSubmitMessage('Error loading asset data: ' + (error.response?.data?.message || error.message));
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
    setIsSubmitting(true);
    setSubmitMessage('');

    try {
      const endpoint = isEditMode ? `/it-assets/${assetId}` : '/it-assets';
      const method = isEditMode ? 'put' : 'post';
      
      console.log('🔍 ITAssetForm submitting:', { method, endpoint, formData });
      const response = await API[method](endpoint, formData);
      console.log('📋 ITAssetForm response:', response);
      
      if (response.data.success) {
        setSubmitMessage(isEditMode ? 'Asset updated successfully!' : 'Asset created successfully!');
        if (onSuccess) {
          onSuccess(response.data.data);
        }
        if (!isEditMode) {
          // Reset form for new asset
          setFormData({
            asset_number: '',
            asset_description: '',
            building: '',
            floor: '',
            department: '',
            room: '',
            condition: 'good',
            status: 'active',
            notes: '',
          });
        }
      } else {
        setSubmitMessage(response.data.message || 'Error saving asset');
      }
    } catch (error) {
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat();
        setSubmitMessage('Validation errors: ' + errors.join(', '));
      } else {
        setSubmitMessage('Error saving asset: ' + (error.response?.data?.message || error.message));
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = () => {
    if (onCancel) {
      onCancel();
    }
  };

  return (
    <div className="it-asset-form-container">
      <div className="it-asset-form-card">
        <h2 className="form-title">
          {isEditMode ? 'Edit IT Asset' : 'Add New IT Asset'}
        </h2>

        <form onSubmit={handleSubmit} className="it-asset-form">
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="asset_number" className="form-label">
                Asset Number
              </label>
              <input
                type="text"
                id="asset_number"
                name="asset_number"
                value={formData.asset_number}
                onChange={handleChange}
                className="form-input"
                placeholder="Enter asset number (e.g., IT2025010001)"
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="asset_description" className="form-label">
                Asset Description *
              </label>
              <input
                type="text"
                id="asset_description"
                name="asset_description"
                value={formData.asset_description}
                onChange={handleChange}
                required
                className="form-input"
                placeholder="Enter asset description"
              />
            </div>
          </div>


          <div className="form-row">
            <div className="form-group">
              <label htmlFor="building" className="form-label">
                Building *
              </label>
              <DropdownWithAdd
                name="building"
                value={formData.building}
                onChange={handleChange}
                placeholder="Select or add building"
                type="building"
                required
                className="form-input"
              />
            </div>

            <div className="form-group">
              <label htmlFor="floor" className="form-label">
                Floor *
              </label>
              <DropdownWithAdd
                name="floor"
                value={formData.floor}
                onChange={handleChange}
                placeholder="Select or add floor"
                type="floor"
                required
                className="form-input"
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="department" className="form-label">
                Department *
              </label>
              <DropdownWithAdd
                name="department"
                value={formData.department}
                onChange={handleChange}
                placeholder="Select or add department"
                type="department"
                required
                className="form-input"
              />
            </div>

            <div className="form-group">
              <label htmlFor="room" className="form-label">
                Room *
              </label>
              <DropdownWithAdd
                name="room"
                value={formData.room}
                onChange={handleChange}
                placeholder="Select or add room"
                type="room"
                required
                className="form-input"
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="condition" className="form-label">
                Condition *
              </label>
              <DropdownWithAdd
                name="condition"
                value={formData.condition}
                onChange={handleChange}
                placeholder="Select or add condition"
                type="condition"
                required
                className="form-select"
              />
            </div>

            <div className="form-group">
              <label htmlFor="status" className="form-label">
                Status *
              </label>
              <DropdownWithAdd
                name="status"
                value={formData.status}
                onChange={handleChange}
                placeholder="Select or add status"
                type="status"
                required
                className="form-select"
              />
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="notes" className="form-label">
              Notes
            </label>
            <textarea
              id="notes"
              name="notes"
              value={formData.notes}
              onChange={handleChange}
              className="form-textarea"
              rows="3"
              placeholder="Additional notes about the asset..."
            />
          </div>

          {submitMessage && (
            <div className={`submit-message ${submitMessage.includes('successfully') ? 'success' : 'error'}`}>
              {submitMessage}
            </div>
          )}

          <div className="form-actions">
            <button
              type="button"
              onClick={handleCancel}
              className="cancel-button"
            >
              {isEditMode ? 'Cancel' : 'Clear Form'}
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="submit-button"
            >
              {isSubmitting ? 'Saving...' : (isEditMode ? 'Update Asset' : 'Create Asset')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ITAssetForm;


