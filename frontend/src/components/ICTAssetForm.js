import React, { useState, useEffect } from 'react';
import API from '../services/api';
import DropdownWithAdd from './DropdownWithAdd';
import './ICTAssetForm.css';

const ICTAssetForm = ({ assetId = null, onSuccess, onCancel }) => {
  const [formData, setFormData] = useState({
    // Basic Information
    asset_number: '',
    asset_description: '',
    building: '',
    floor: '',
    department: '',
    room: '',
    condition: 'good',
    status: 'active',
    notes: '',
    
    // ICT Asset Classification
    brand: '',
    model: '',
    serial_number: '',
    
    // Technical Specifications
    processor: '',
    memory: '',
    storage: '',
    operating_system: '',
    ip_address: '',
    mac_address: '',
    
    
    // Asset Lifecycle
    deployment_date: '',
    last_maintenance_date: '',
    next_maintenance_date: '',
    assigned_to: '',
    location_details: '',
    
    // Additional ICT Fields
    network_zone: '',
    is_critical: false,
    backup_status: '',
    technical_notes: '',
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitMessage, setSubmitMessage] = useState('');
  const [isEditMode, setIsEditMode] = useState(false);
  const [activeTab, setActiveTab] = useState('basic');

  useEffect(() => {
    if (assetId) {
      setIsEditMode(true);
      fetchAsset();
    }
  }, [assetId]);

  const fetchAsset = async () => {
    try {
      const response = await API.get(`/it-assets/${assetId}`);
      if (response.data.success) {
        const asset = response.data.data;
        setFormData({
          asset_number: asset.asset_number || '',
          asset_description: asset.asset_description || '',
          building: asset.building || '',
          floor: asset.floor || '',
          department: asset.department || '',
          room: asset.room || '',
          condition: asset.condition || 'good',
          status: asset.status || 'active',
          notes: asset.notes || '',
          brand: asset.brand || '',
          model: asset.model || '',
          serial_number: asset.serial_number || '',
          processor: asset.processor || '',
          memory: asset.memory || '',
          storage: asset.storage || '',
          operating_system: asset.operating_system || '',
          ip_address: asset.ip_address || '',
          mac_address: asset.mac_address || '',
          deployment_date: asset.deployment_date || '',
          last_maintenance_date: asset.last_maintenance_date || '',
          next_maintenance_date: asset.next_maintenance_date || '',
          assigned_to: asset.assigned_to || '',
          location_details: asset.location_details || '',
          network_zone: asset.network_zone || '',
          is_critical: asset.is_critical || false,
          backup_status: asset.backup_status || '',
          technical_notes: asset.technical_notes || '',
        });
      }
    } catch (error) {
      setSubmitMessage('Error loading asset data');
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitMessage('');

    try {
      const endpoint = isEditMode ? `/it-assets/${assetId}` : '/it-assets';
      const method = isEditMode ? 'put' : 'post';
      
      const response = await API[method](endpoint, formData);
      
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
            brand: '',
            model: '',
            serial_number: '',
            processor: '',
            memory: '',
            storage: '',
            operating_system: '',
            ip_address: '',
            mac_address: '',
            deployment_date: '',
            last_maintenance_date: '',
            next_maintenance_date: '',
            assigned_to: '',
            location_details: '',
            network_zone: '',
            is_critical: false,
            backup_status: '',
            technical_notes: '',
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

  const tabs = [
    { id: 'basic', label: 'Basic Info', icon: '📋' },
    { id: 'technical', label: 'Technical', icon: '⚙️' },
    { id: 'lifecycle', label: 'Lifecycle', icon: '🔄' },
    { id: 'ict', label: 'ICT Details', icon: '💻' },
  ];

  return (
    <div className="ict-asset-form-container">
      <div className="ict-asset-form-card">
        <h2 className="form-title">
          {isEditMode ? 'Edit ICT Asset' : 'Add New ICT Asset'}
        </h2>

        {/* Tab Navigation */}
        <div className="form-tabs">
          {tabs.map(tab => (
            <button
              key={tab.id}
              type="button"
              className={`tab-button ${activeTab === tab.id ? 'active' : ''}`}
              onClick={() => setActiveTab(tab.id)}
            >
              <span className="tab-icon">{tab.icon}</span>
              <span className="tab-label">{tab.label}</span>
            </button>
          ))}
        </div>

        <form onSubmit={handleSubmit} className="ict-asset-form">
          {/* Basic Information Tab */}
          {activeTab === 'basic' && (
            <div className="tab-content">
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
                  <input
                    type="text"
                    id="building"
                    name="building"
                    value={formData.building}
                    onChange={handleChange}
                    required
                    className="form-input"
                    placeholder="Enter building name"
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
            </div>
          )}

          {/* Technical Specifications Tab */}
          {activeTab === 'technical' && (
            <div className="tab-content">
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="processor" className="form-label">
                    Processor
                  </label>
                  <input
                    type="text"
                    id="processor"
                    name="processor"
                    value={formData.processor}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., Intel Core i7-10700K"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="memory" className="form-label">
                    Memory
                  </label>
                  <input
                    type="text"
                    id="memory"
                    name="memory"
                    value={formData.memory}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., 16GB DDR4"
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="storage" className="form-label">
                    Storage
                  </label>
                  <input
                    type="text"
                    id="storage"
                    name="storage"
                    value={formData.storage}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., 512GB SSD"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="operating_system" className="form-label">
                    Operating System
                  </label>
                  <input
                    type="text"
                    id="operating_system"
                    name="operating_system"
                    value={formData.operating_system}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., Windows 11 Pro"
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="ip_address" className="form-label">
                    IP Address
                  </label>
                  <input
                    type="text"
                    id="ip_address"
                    name="ip_address"
                    value={formData.ip_address}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., 192.168.1.100"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="mac_address" className="form-label">
                    MAC Address
                  </label>
                  <input
                    type="text"
                    id="mac_address"
                    name="mac_address"
                    value={formData.mac_address}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., 00:1B:44:11:3A:B7"
                  />
                </div>
              </div>

              <div className="form-group">
                <label htmlFor="technical_notes" className="form-label">
                  Technical Notes
                </label>
                <textarea
                  id="technical_notes"
                  name="technical_notes"
                  value={formData.technical_notes}
                  onChange={handleChange}
                  className="form-textarea"
                  rows="4"
                  placeholder="Technical specifications, configurations, or special requirements..."
                />
              </div>
            </div>
          )}


          {/* Asset Lifecycle Tab */}
          {activeTab === 'lifecycle' && (
            <div className="tab-content">
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="deployment_date" className="form-label">
                    Deployment Date
                  </label>
                  <input
                    type="date"
                    id="deployment_date"
                    name="deployment_date"
                    value={formData.deployment_date}
                    onChange={handleChange}
                    className="form-input"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="assigned_to" className="form-label">
                    Assigned To
                  </label>
                  <input
                    type="text"
                    id="assigned_to"
                    name="assigned_to"
                    value={formData.assigned_to}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="User or department name"
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="last_maintenance_date" className="form-label">
                    Last Maintenance Date
                  </label>
                  <input
                    type="date"
                    id="last_maintenance_date"
                    name="last_maintenance_date"
                    value={formData.last_maintenance_date}
                    onChange={handleChange}
                    className="form-input"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="next_maintenance_date" className="form-label">
                    Next Maintenance Date
                  </label>
                  <input
                    type="date"
                    id="next_maintenance_date"
                    name="next_maintenance_date"
                    value={formData.next_maintenance_date}
                    onChange={handleChange}
                    className="form-input"
                  />
                </div>
              </div>


              <div className="form-group">
                <label htmlFor="location_details" className="form-label">
                  Location Details
                </label>
                <input
                  type="text"
                  id="location_details"
                  name="location_details"
                  value={formData.location_details}
                  onChange={handleChange}
                  className="form-input"
                  placeholder="Additional location information"
                />
              </div>
            </div>
          )}

          {/* ICT Details Tab */}
          {activeTab === 'ict' && (
            <div className="tab-content">

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="brand" className="form-label">
                    Brand
                  </label>
                  <input
                    type="text"
                    id="brand"
                    name="brand"
                    value={formData.brand}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., Dell, HP, Lenovo"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="model" className="form-label">
                    Model
                  </label>
                  <input
                    type="text"
                    id="model"
                    name="model"
                    value={formData.model}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="e.g., OptiPlex 7090"
                  />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="serial_number" className="form-label">
                    Serial Number
                  </label>
                  <input
                    type="text"
                    id="serial_number"
                    name="serial_number"
                    value={formData.serial_number}
                    onChange={handleChange}
                    className="form-input"
                    placeholder="Asset serial number"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="network_zone" className="form-label">
                    Network Zone
                  </label>
                  <select
                    id="network_zone"
                    name="network_zone"
                    value={formData.network_zone}
                    onChange={handleChange}
                    className="form-select"
                  >
                    <option value="">Select Network Zone</option>
                    <option value="DMZ">DMZ</option>
                    <option value="Internal">Internal</option>
                    <option value="External">External</option>
                    <option value="Management">Management</option>
                  </select>
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="backup_status" className="form-label">
                    Backup Status
                  </label>
                  <select
                    id="backup_status"
                    name="backup_status"
                    value={formData.backup_status}
                    onChange={handleChange}
                    className="form-select"
                  >
                    <option value="">Select Backup Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Not Required">Not Required</option>
                    <option value="Unknown">Unknown</option>
                  </select>
                </div>

                <div className="form-group checkbox-group">
                  <label className="checkbox-label">
                    <input
                      type="checkbox"
                      name="is_critical"
                      checked={formData.is_critical}
                      onChange={handleChange}
                      className="checkbox-input"
                    />
                    <span className="checkbox-text">Critical Asset</span>
                  </label>
                </div>
              </div>
            </div>
          )}

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

export default ICTAssetForm;
