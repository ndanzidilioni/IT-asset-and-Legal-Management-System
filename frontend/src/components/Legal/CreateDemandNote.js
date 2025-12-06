import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import './DemandNoteForm.css';

const CreateDemandNote = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  const [formData, setFormData] = useState({
    client_name: '',
    client_number: '',
    claim_reference: '',
    amount_claimed: '',
    due_date: '',
    nature_of_claim: 'Works',
    agency_of_claim: '',
    notice_to_institute_suit: '',
    time_given_to_settle: '',
    settlement_action_taken: '',
    remarks: '',
    late_fee: 0
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
    
    try {
      setLoading(true);
      setError('');
      setSuccess('');
      
      const response = await legalApi.demandNotes.create(formData);
      
      if (response.success) {
        setSuccess('Demand note created successfully!');
        setTimeout(() => {
          navigate('/legal/demand-notes');
        }, 1500);
      }
    } catch (err) {
      console.error('Create error:', err.response?.data);
      const errorMessage = err.response?.data?.message || err.message;
      const validationErrors = err.response?.data?.errors;
      
      if (validationErrors) {
        const errorList = Object.values(validationErrors).flat().join(', ');
        setError('Validation failed: ' + errorList);
      } else {
        setError('Failed to create demand note: ' + errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="demand-note-form-container">
      <div className="form-header">
        <h1>📋 Create Demand Note</h1>
        <button onClick={() => navigate('/legal/demand-notes')} className="btn-back">
          ← Back to List
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}

      <form onSubmit={handleSubmit} className="demand-note-form">
        <div className="form-section">
          <h3>Client Information</h3>
          
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="client_name">Client Name *</label>
              <input
                type="text"
                id="client_name"
                name="client_name"
                value={formData.client_name}
                onChange={handleChange}
                required
                placeholder="Enter client full name"
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="client_number">Client Number</label>
              <input
                type="text"
                id="client_number"
                name="client_number"
                value={formData.client_number}
                onChange={handleChange}
                placeholder="e.g., CLT-2025-001"
              />
            </div>
          </div>
        </div>

        <div className="form-section">
          <h3>Claim Details</h3>
          
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="claim_reference">Claim Reference</label>
              <input
                type="text"
                id="claim_reference"
                name="claim_reference"
                value={formData.claim_reference}
                onChange={handleChange}
                placeholder="e.g., REF-2025-001"
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="nature_of_claim">Nature of Claim *</label>
              <select
                id="nature_of_claim"
                name="nature_of_claim"
                value={formData.nature_of_claim}
                onChange={handleChange}
                required
              >
                <option value="Works">Works</option>
                <option value="Supply of Goods">Supply of Goods</option>
                <option value="Services">Services</option>
                <option value="Consultancy">Consultancy</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="amount_claimed">Amount Claimed (TZS) *</label>
              <input
                type="number"
                id="amount_claimed"
                name="amount_claimed"
                value={formData.amount_claimed}
                onChange={handleChange}
                required
                min="0"
                step="0.01"
                placeholder="0.00"
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="late_fee">Late Fee (TZS)</label>
              <input
                type="number"
                id="late_fee"
                name="late_fee"
                value={formData.late_fee}
                onChange={handleChange}
                min="0"
                step="0.01"
                placeholder="0.00"
              />
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="due_date">Due Date *</label>
              <input
                type="date"
                id="due_date"
                name="due_date"
                value={formData.due_date}
                onChange={handleChange}
                required
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="agency_of_claim">Agency of Claim</label>
              <input
                type="text"
                id="agency_of_claim"
                name="agency_of_claim"
                value={formData.agency_of_claim}
                onChange={handleChange}
                placeholder="Organization/Agency name"
              />
            </div>
          </div>
        </div>

        <div className="form-section">
          <h3>Legal Notice & Settlement</h3>
          
          <div className="form-group">
            <label htmlFor="notice_to_institute_suit">Notice to Institute Suit</label>
            <textarea
              id="notice_to_institute_suit"
              name="notice_to_institute_suit"
              value={formData.notice_to_institute_suit}
              onChange={handleChange}
              rows="4"
              placeholder="Details of legal notice issued..."
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="time_given_to_settle">Time Given to Settle</label>
            <input
              type="text"
              id="time_given_to_settle"
              name="time_given_to_settle"
              value={formData.time_given_to_settle}
              onChange={handleChange}
              placeholder="e.g., 30 days, 60 days"
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="settlement_action_taken">Settlement/Action Taken</label>
            <textarea
              id="settlement_action_taken"
              name="settlement_action_taken"
              value={formData.settlement_action_taken}
              onChange={handleChange}
              rows="4"
              placeholder="Describe actions taken or settlement details..."
            />
          </div>
        </div>

        <div className="form-section">
          <h3>Additional Information</h3>
          
          <div className="form-group">
            <label htmlFor="remarks">Remarks</label>
            <textarea
              id="remarks"
              name="remarks"
              value={formData.remarks}
              onChange={handleChange}
              rows="3"
              placeholder="Additional notes or comments..."
            />
          </div>
        </div>

        <div className="form-actions">
          <button type="button" onClick={() => navigate('/legal/demand-notes')} className="btn-cancel">
            Cancel
          </button>
          <button type="submit" disabled={loading} className="btn-submit">
            {loading ? 'Creating...' : '✓ Create Demand Note'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateDemandNote;
