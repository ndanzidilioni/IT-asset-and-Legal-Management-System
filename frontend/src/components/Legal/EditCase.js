import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/CreateCase.css';

const EditCase = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [documents, setDocuments] = useState([]);
  const [existingDocuments, setExistingDocuments] = useState([]);
  
  const [formData, setFormData] = useState({
    case_number: '',
    parties: '',
    nature_of_case: 'Civil',
    amount_in_claim: '',
    date_filed: '',
    current_status: 'Pending Hearing',
    next_hearing_date: '',
    any_appeal: 'No',
    remarks: '',
    assigned_lawyer: ''
  });

  useEffect(() => {
    loadCase();
  }, [id]);

  const loadCase = async () => {
    try {
      console.log('🔍 EditCase loading case with ID:', id);
      const response = await legalApi.cases.getById(id);
      console.log('📋 EditCase API response:', response);
      
      if (response.success) {
        const caseData = response.data;
        console.log('✅ EditCase case data:', caseData);
        
        // Map API response fields to form fields
        setFormData({
          case_number: caseData.case_number || caseData.reference_number || '',
          parties: caseData.parties || caseData.client_name || '',
          nature_of_case: caseData.nature_of_case || caseData.case_type || caseData.type || 'Civil',
          amount_in_claim: caseData.amount_in_claim || caseData.estimated_value || '',
          date_filed: caseData.date_filed || caseData.filing_date || '',
          current_status: caseData.current_status || caseData.status || 'Pending Hearing',
          next_hearing_date: caseData.next_hearing_date || caseData.hearing_date || '',
          any_appeal: caseData.any_appeal || 'No',
          remarks: caseData.remarks || caseData.notes || caseData.case_description || caseData.description || '',
          assigned_lawyer: caseData.assigned_lawyer || caseData.assigned_lawyer_id || caseData.assigned_to || ''
        });
        
        console.log('✅ EditCase form data set:', {
          case_number: caseData.case_number || caseData.reference_number || '',
          parties: caseData.parties || caseData.client_name || '',
          nature_of_case: caseData.nature_of_case || caseData.case_type || caseData.type || 'Civil',
          amount_in_claim: caseData.amount_in_claim || caseData.estimated_value || '',
          date_filed: caseData.date_filed || caseData.filing_date || '',
          current_status: caseData.current_status || caseData.status || 'Pending Hearing',
          next_hearing_date: caseData.next_hearing_date || caseData.hearing_date || '',
          any_appeal: caseData.any_appeal || 'No',
          remarks: caseData.remarks || caseData.notes || caseData.case_description || caseData.description || '',
          assigned_lawyer: caseData.assigned_lawyer || caseData.assigned_lawyer_id || caseData.assigned_to || ''
        });
        
        // Load existing documents for this case
        try {
          const documentsResponse = await legalApi.cases.getDocuments(id);
          if (documentsResponse.success) {
            setExistingDocuments(documentsResponse.data || []);
            console.log('✅ EditCase documents loaded:', documentsResponse.data);
          }
        } catch (docErr) {
          console.error('⚠️ Failed to load documents:', docErr);
          // Don't fail the whole form if documents can't be loaded
        }
        
        setLoading(false);
      } else {
        console.log('❌ EditCase API returned success=false:', response);
        setError('Case not found: ' + (response.message || 'Unknown error'));
        setLoading(false);
      }
    } catch (err) {
      console.error('🚨 EditCase error loading case:', err);
      setError('Failed to load case: ' + (err.message || 'Network error'));
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

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    setDocuments(files);
  };

  const handleDeleteDocument = async (docId) => {
    if (!window.confirm('Are you sure you want to delete this document?')) {
      return;
    }
    
    try {
      console.log('🗑️ EditCase deleting document:', docId);
      const response = await legalApi.cases.deleteDocument(docId);
      
      if (response.success) {
        setExistingDocuments(prev => prev.filter(doc => doc.id !== docId));
        setSuccess('Document deleted successfully');
        setTimeout(() => setSuccess(''), 3000);
      } else {
        setError('Failed to delete document: ' + (response.message || 'Unknown error'));
      }
    } catch (err) {
      console.error('🚨 EditCase error deleting document:', err);
      setError('Error deleting document: ' + err.message);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSaving(true);

    try {
      // Validate required fields
      if (!formData.case_number || !formData.parties || !formData.nature_of_case) {
        setError('Case Number, Parties, and Nature of Case are required');
        setSaving(false);
        return;
      }

      // Prepare data for submission
      const caseData = {
        ...formData,
        amount_in_claim: formData.amount_in_claim ? parseFloat(formData.amount_in_claim) : null,
        date_filed: formData.date_filed || null,
        next_hearing_date: formData.next_hearing_date || null
      };

      // Update case
      console.log('🔄 EditCase updating case with ID:', id);
      console.log('🔄 EditCase update data:', caseData);
      const response = await legalApi.cases.update(id, caseData);
      console.log('📋 EditCase update response:', response);

      if (response.success) {
        // Upload documents if any
        if (documents.length > 0) {
          try {
            const uploadResponse = await legalApi.cases.uploadDocument(id, documents[0]);
            if (uploadResponse.success) {
              setSuccess(`Case ${formData.case_number} and document uploaded successfully!`);
            } else {
              setSuccess(`Case updated but document upload failed: ${uploadResponse.message}`);
            }
          } catch (uploadErr) {
            console.error('Document upload error:', uploadErr);
            setSuccess(`Case updated but document upload failed`);
          }
        } else {
          setSuccess(`Case ${formData.case_number} updated successfully!`);
        }

        // Redirect after success
        setTimeout(() => {
          navigate('/legal/cases');
        }, 2000);
      } else {
        setError(response.message || 'Failed to update case');
      }
    } catch (err) {
      console.error('Error updating case:', err);
      setError(err.message || 'An error occurred while updating the case');
    } finally {
      setSaving(false);
    }
  };

  const handleCancel = () => {
    navigate('/legal/cases');
  };

  if (loading) {
    return (
      <div className="create-case-container">
        <div className="loading">Loading case data...</div>
      </div>
    );
  }

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>✏️ Edit Case</h1>
        <p>Update case details below</p>
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
        {/* Case Number */}
        <div className="form-row">
          <div className="form-group">
            <label>Case Number <span className="required">*</span></label>
            <input
              type="text"
              name="case_number"
              value={formData.case_number}
              onChange={handleChange}
              placeholder="e.g., CASE-2024-001"
              required
            />
            <small>Unique identifier for this case</small>
          </div>
        </div>

        {/* Parties */}
        <div className="form-group">
          <label>Parties <span className="required">*</span></label>
          <textarea
            name="parties"
            value={formData.parties}
            onChange={handleChange}
            placeholder="e.g., John Smith (Plaintiff) vs. Acme Corporation (Defendant)"
            rows="3"
            required
          />
          <small>List all parties involved in the case</small>
        </div>

        {/* Nature of Case and Amount */}
        <div className="form-row">
          <div className="form-group">
            <label>Nature of Case <span className="required">*</span></label>
            <select
              name="nature_of_case"
              value={formData.nature_of_case}
              onChange={handleChange}
              required
            >
              <option value="Civil">Civil</option>
              <option value="Criminal">Criminal</option>
              <option value="Bankruptcy">Bankruptcy</option>
            </select>
          </div>

          <div className="form-group">
            <label>Amount in Claim (TZS)</label>
            <input
              type="number"
              name="amount_in_claim"
              value={formData.amount_in_claim}
              onChange={handleChange}
              placeholder="e.g., 50000000"
              step="0.01"
            />
            <small>Amount in Tanzania Shillings (optional)</small>
          </div>
        </div>

        {/* Dates */}
        <div className="form-row">
          <div className="form-group">
            <label>Date Filed</label>
            <input
              type="date"
              name="date_filed"
              value={formData.date_filed}
              onChange={handleChange}
            />
          </div>

          <div className="form-group">
            <label>Next Hearing Date</label>
            <input
              type="date"
              name="next_hearing_date"
              value={formData.next_hearing_date}
              onChange={handleChange}
            />
          </div>
        </div>

        {/* Status and Appeal */}
        <div className="form-row">
          <div className="form-group">
            <label>Current Status <span className="required">*</span></label>
            <select
              name="current_status"
              value={formData.current_status}
              onChange={handleChange}
              required
            >
              <option value="Pending Hearing">Pending Hearing</option>
              <option value="Pending Appeal">Pending Appeal</option>
              <option value="Active">Active</option>
              <option value="Closed">Closed</option>
              <option value="Completed">Completed</option>
            </select>
          </div>

          <div className="form-group">
            <label>Any Appeal?</label>
            <select
              name="any_appeal"
              value={formData.any_appeal}
              onChange={handleChange}
            >
              <option value="No">No</option>
              <option value="Yes">Yes</option>
              <option value="Pending">Pending</option>
            </select>
          </div>
        </div>

        {/* Assigned Lawyer */}
        <div className="form-group">
          <label>Assigned Lawyer</label>
          <input
            type="text"
            name="assigned_lawyer"
            value={formData.assigned_lawyer}
            onChange={handleChange}
            placeholder="e.g., Sarah Johnson"
          />
          <small>Lawyer handling this case</small>
        </div>

        {/* Remarks */}
        <div className="form-group">
          <label>Remarks</label>
          <textarea
            name="remarks"
            value={formData.remarks}
            onChange={handleChange}
            placeholder="Additional notes or comments about the case..."
            rows="4"
          />
        </div>

        {/* Existing Documents */}
        {existingDocuments.length > 0 && (
          <div className="form-group">
            <label>📎 Existing Documents ({existingDocuments.length})</label>
            <div style={{
              border: '1px solid #e5e7eb',
              borderRadius: '8px',
              padding: '15px',
              backgroundColor: '#f9fafb',
              marginTop: '10px'
            }}>
              {existingDocuments.map((doc) => (
                <div key={doc.id} style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: '10px',
                  borderBottom: '1px solid #e5e7eb',
                  marginBottom: '10px'
                }}>
                  <div style={{flex: 1}}>
                    <div style={{fontWeight: '600', marginBottom: '3px'}}>
                      📄 {doc.document_name || doc.file_name}
                    </div>
                    <div style={{fontSize: '13px', color: '#6b7280'}}>
                      Type: {doc.document_type || 'N/A'} | 
                      Size: {doc.file_size ? (doc.file_size / 1024).toFixed(2) : '0'} KB
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleDeleteDocument(doc.id)}
                    style={{
                      padding: '6px 12px',
                      backgroundColor: '#dc2626',
                      color: 'white',
                      border: 'none',
                      borderRadius: '5px',
                      cursor: 'pointer',
                      fontSize: '13px',
                      marginLeft: '10px'
                    }}
                  >
                    🗑️ Delete
                  </button>
                </div>
              ))}
            </div>
            <small style={{color: '#6b7280', display: 'block', marginTop: '10px'}}>
              Delete unwanted documents before uploading new ones to avoid duplicates
            </small>
          </div>
        )}

        {/* Upload Documents */}
        <div className="form-group">
          <label>Upload Additional Documents</label>
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
            disabled={saving}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={saving}
          >
            {saving ? '⏳ Updating Case...' : '✅ Update Case'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default EditCase;
