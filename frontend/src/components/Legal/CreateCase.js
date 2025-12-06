import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/CreateCase.css';

const CreateCase = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [documents, setDocuments] = useState([]);
  
  const [formData, setFormData] = useState({
    case_number: '',
    case_year: '',
    parties: '',
    case_type: 'Civil',
    amount_in_claim: '',
    client_id: '',
    court_name: '',
    filing_date: '',
    hearing_date: '',
    status: 'Active',
    priority: 'Medium',
    any_appeal: 'No',
    notes: ''
  });

  // Generate case year options (e.g., 2024/2025)
  const generateCaseYears = () => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = -2; i <= 2; i++) {
      const startYear = currentYear + i;
      const endYear = startYear + 1;
      years.push(`${startYear}/${endYear}`);
    }
    return years;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    setDocuments(files);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      // Validate required fields
      if (!formData.case_number || !formData.case_year || !formData.parties || !formData.case_type || !formData.filing_date || !formData.status) {
        setError('Case No, Case Year, Parties, Nature of Case, Date Filed, and Current Status are required');
        setLoading(false);
        return;
      }

      // Prepare data for submission
      const caseData = {
        ...formData,
        filing_date: formData.filing_date || null,
        hearing_date: formData.hearing_date || null
      };

      // Create case
      const response = await legalApi.cases.create(caseData);

      if (response.success || response.case) {
        const caseId = response.case?.id || response.data?.id;
        
        // Upload documents if any
        if (documents.length > 0 && caseId) {
          try {
            const uploadResponse = await legalApi.cases.uploadDocument(
              caseId, 
              documents[0],
              documents[0].name, // document name
              'Other' // document type - you can make this dynamic later
            );
            if (uploadResponse.message || uploadResponse.document) {
              setSuccess(`Case and document uploaded successfully!`);
            } else {
              setSuccess(`Case created but document upload failed`);
            }
          } catch (uploadErr) {
            console.error('Document upload error:', uploadErr);
            // Show error details if available
            if (uploadErr.response?.data?.errors) {
              const errors = uploadErr.response.data.errors;
              const errorMessages = Object.values(errors).flat().join(', ');
              setSuccess(`Case created successfully but document upload failed: ${errorMessages}`);
            } else {
              setSuccess(`Case created successfully but document upload failed`);
            }
          }
        } else {
          setSuccess(`Case created successfully!`);
        }

        // Reset form
        setTimeout(() => {
          navigate('/legal/cases');
        }, 2000);
      } else {
        setError(response.message || 'Failed to create case');
      }
    } catch (err) {
      console.error('Error creating case:', err);
      
      // Handle validation errors from backend
      if (err.response && err.response.data && err.response.data.errors) {
        const errors = err.response.data.errors;
        const errorMessages = Object.values(errors).flat().join(', ');
        setError(errorMessages);
      } else {
        setError(err.message || 'An error occurred while creating the case');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate('/legal/cases');
  };

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>📝 Create New Case</h1>
        <p>Fill in the case details below</p>
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
        {/* Case Number and Case Year */}
        <div className="form-row">
          <div className="form-group">
            <label>Case No <span className="required">*</span></label>
            <input
              type="text"
              name="case_number"
              value={formData.case_number}
              onChange={handleChange}
              placeholder="e.g., HC/123/2024"
              required
            />
            <small>Official case number</small>
          </div>

          <div className="form-group">
            <label>Case Year <span className="required">*</span></label>
            <select
              name="case_year"
              value={formData.case_year}
              onChange={handleChange}
              required
            >
              <option value="">Select Year</option>
              {generateCaseYears().map(year => (
                <option key={year} value={year}>{year}</option>
              ))}
            </select>
            <small>Financial/Case year</small>
          </div>
        </div>

        {/* Parties */}
        <div className="form-group">
          <label>Parties <span className="required">*</span></label>
          <input
            type="text"
            name="parties"
            value={formData.parties}
            onChange={handleChange}
            placeholder="e.g., John Doe vs ABC Corporation"
            required
          />
          <small>All parties involved in the case</small>
        </div>

        {/* Nature of Case and Amount in Claim */}
        <div className="form-row">
          <div className="form-group">
            <label>Nature of Case <span className="required">*</span></label>
            <select
              name="case_type"
              value={formData.case_type}
              onChange={handleChange}
              required
            >
              <option value="Civil">Civil</option>
              <option value="Criminal">Criminal</option>
              <option value="Corporate">Corporate</option>
              <option value="Labor">Labor</option>
            </select>
          </div>

          <div className="form-group">
            <label>Amount In Claim</label>
            <input
              type="number"
              name="amount_in_claim"
              value={formData.amount_in_claim}
              onChange={handleChange}
              placeholder="e.g., 5000000"
              step="0.01"
            />
            <small>Monetary value claimed (in TZS)</small>
          </div>
        </div>

        {/* Date Filed and Next Hearing Date */}
        <div className="form-row">
          <div className="form-group">
            <label>Date Filed <span className="required">*</span></label>
            <input
              type="date"
              name="filing_date"
              value={formData.filing_date}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label>Next Hearing Date</label>
            <input
              type="date"
              name="hearing_date"
              value={formData.hearing_date}
              onChange={handleChange}
            />
          </div>
        </div>

        {/* Current Status and Any Appeal */}
        <div className="form-row">
          <div className="form-group">
            <label>Current Status <span className="required">*</span></label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
              required
            >
              <option value="Active">Active</option>
              <option value="Pending">Pending</option>
              <option value="Completed">Completed</option>
              <option value="On Hold">On Hold</option>
              <option value="Closed">Closed</option>
              <option value="Dismissed">Dismissed</option>
            </select>
          </div>

          <div className="form-group">
            <label>Any Appeal</label>
            <select
              name="any_appeal"
              value={formData.any_appeal}
              onChange={handleChange}
            >
              <option value="No">No</option>
              <option value="Yes - Pending">Yes - Pending</option>
              <option value="Yes - Filed">Yes - Filed</option>
              <option value="Yes - Decided">Yes - Decided</option>
              <option value="Yes - Withdrawn">Yes - Withdrawn</option>
            </select>
          </div>
        </div>

        {/* Remarks */}
        <div className="form-group">
          <label>Remarks</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            placeholder="Additional remarks or comments..."
            rows="4"
          />
          <small>Any additional information or observations</small>
        </div>

        {/* Upload Documents */}
        <div className="form-group">
          <label>Upload Documents</label>
          <div className="file-upload-area">
            <input
              type="file"
              id="documents"
              multiple
              onChange={handleFileChange}
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
            />
            <label htmlFor="documents" className="file-upload-label">
              📎 Click to browse or drag files here
              <small>Accepted formats: PDF, DOC, DOCX, JPG, PNG, XLSX</small>
            </label>
            {documents.length > 0 && (
              <div className="selected-files">
                <strong>Selected files:</strong>
                <ul>
                  {documents.map((file, index) => (
                    <li key={index}>
                      📄 {file.name} ({(file.size / 1024).toFixed(2)} KB)
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
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
            {loading ? '⏳ Creating Case...' : '✅ Create Case'}
          </button>
        </div>
      </form>

      {/* Help Text */}
      
    </div>
  );
};

export default CreateCase;
