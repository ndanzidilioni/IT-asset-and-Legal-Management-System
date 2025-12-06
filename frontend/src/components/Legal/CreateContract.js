import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import authService from '../../services/authService';
import '../../styles/CreateCase.css';

const CreateContract = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [document, setDocument] = useState(null);
  
  const currentUser = authService.getCurrentUser();
  const currentYear = new Date().getFullYear();
  const currentFiscalYear = `${currentYear}/${currentYear + 1}`;
  
  const [formData, setFormData] = useState({
    tender_number: '',
    project_name: '',
    supplier_contractor: '',
    contract_amount: '',
    contract_year: currentFiscalYear,
    category: 'PMU',
    date_received: '',
    date_vetted: '',
    date_signed: '',
    commencement_date: '',
    completion_date: '',
    status: 'Draft',
    notes: ''
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
      setDocument(file);
    }
  };

  const generateTenderNumber = () => {
    const year = formData.contract_year;
    const random = Math.floor(Math.random() * 900) + 100;
    const tenderNum = `TEND-${year}-${random}`;
    setFormData(prev => ({
      ...prev,
      tender_number: tenderNum
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      // Validate required fields
      if (!formData.tender_number || !formData.project_name || 
          !formData.supplier_contractor) {
        setError('Please fill all required fields (Tender Number, Project Name, Supplier/Contractor)');
        setLoading(false);
        return;
      }

      // Map frontend fields to backend schema (matching contracts-api.php)
      const contractData = {
        contract_number: formData.tender_number,
        title: formData.project_name,
        client_name: formData.supplier_contractor,
        contract_value: formData.contract_amount ? parseFloat(formData.contract_amount) : null,
        start_date: formData.date_signed || null,
        end_date: formData.completion_date || null,
        status: formData.status,
        description: `Tender Number: ${formData.tender_number}\nYear: ${formData.contract_year}\nCategory: ${formData.category}\nDate Received: ${formData.date_received || 'N/A'}\nDate Vetted: ${formData.date_vetted || 'N/A'}\nCommencement Date: ${formData.commencement_date || 'N/A'}`,
        notes: formData.notes,
        created_by: currentUser?.id || 1
      };

      console.log('Current user:', currentUser);
      console.log('Sending contract data:', contractData);
      console.log('Auth token:', localStorage.getItem('token') ? 'Present' : 'Missing');

      // Create contract
      const response = await legalApi.contracts.create(contractData);
      console.log('✅ Contract creation response:', response);

      if (response && (response.success === true || response.message)) {
        const contractId = response.data?.id;
        const contractNumber = response.data?.contract_number || formData.tender_number;
        
        // Upload document if provided
        if (document && contractId) {
          try {
            const uploadResponse = await legalApi.contracts.uploadDocument(contractId, document);
            if (uploadResponse.success) {
              setSuccess(`✅ Contract ${contractNumber} and document uploaded successfully!`);
            } else {
              setSuccess(`✅ Contract ${contractNumber} created successfully! (Document upload failed)`);
            }
          } catch (uploadErr) {
            console.error('Document upload error:', uploadErr);
            setSuccess(`✅ Contract ${contractNumber} created successfully! (Document upload failed)`);
          }
        } else {
          setSuccess(`✅ Contract ${contractNumber} created successfully!`);
        }

        // Clear form
        setFormData({
          tender_number: '',
          client_name: '',
          description: '',
          contract_value: '',
          start_date: '',
          completion_date: '',
          contract_year: new Date().getFullYear(),
          category: '',
          date_received: '',
          date_vetted: '',
          commencement_date: '',
          status: 'Draft',
          notes: ''
        });
        setDocument(null);

        // Navigate after showing success
        setTimeout(() => {
          navigate('/legal/contracts');
        }, 2000);
      } else {
        setError(`❌ ${response?.message || 'Failed to create contract'}`);
      }
    } catch (err) {
      console.error('Error creating contract:', err);
      
      // Handle validation errors
      if (err.response?.data?.errors) {
        const validationErrors = err.response.data.errors;
        const errorMessages = Object.entries(validationErrors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n');
        setError(`Validation errors:\n${errorMessages}`);
      } else {
        setError(err.message || 'An error occurred while creating the contract');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate('/legal/contracts');
  };

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>📋 Create New Contract</h1>
        <p>Register a new tender/contract in the system</p>
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
        {/* Tender Number and Year */}
        <div className="form-row">
          <div className="form-group">
            <label>Tender Number <span className="required">*</span></label>
            <div style={{display: 'flex', gap: '10px'}}>
              <input
                type="text"
                name="tender_number"
                value={formData.tender_number}
                onChange={handleChange}
                placeholder="e.g., TEND-2024-001"
                required
                style={{flex: 1}}
              />
              <button
                type="button"
                onClick={generateTenderNumber}
                className="btn btn-secondary"
                style={{minWidth: '120px'}}
              >
                🔄 Generate
              </button>
            </div>
            <small>Unique tender identification number</small>
          </div>

          <div className="form-group">
            <label>Contract Year <span className="required">*</span></label>
            <select
              name="contract_year"
              value={formData.contract_year}
              onChange={handleChange}
              required
            >
              {[...Array(5)].map((_, i) => {
                const startYear = currentYear - i;
                const endYear = startYear + 1;
                const fiscalYear = `${startYear}/${endYear}`;
                return <option key={fiscalYear} value={fiscalYear}>{fiscalYear}</option>;
              })}
            </select>
            <small>Fiscal year (e.g., 2025/2026)</small>
          </div>
        </div>

        {/* Project Name */}
        <div className="form-group">
          <label>Project Name <span className="required">*</span></label>
          <input
            type="text"
            name="project_name"
            value={formData.project_name}
            onChange={handleChange}
            placeholder="e.g., Road Construction Project"
            required
          />
          <small>Full name of the project</small>
        </div>

        {/* Supplier/Contractor and Category */}
        <div className="form-row">
          <div className="form-group">
            <label>Supplier/Contractor <span className="required">*</span></label>
            <input
              type="text"
              name="supplier_contractor"
              value={formData.supplier_contractor}
              onChange={handleChange}
              placeholder="e.g., ABC Construction Ltd"
              required
            />
            <small>Company or individual name</small>
          </div>

          <div className="form-group">
            <label>Category <span className="required">*</span></label>
            <select
              name="category"
              value={formData.category}
              onChange={handleChange}
              required
            >
              <option value="PMU">PMU</option>
              <option value="non-PMU">non-PMU</option>
            </select>
            <small>Contract category</small>
          </div>
        </div>

        {/* Contract Amount */}
        <div className="form-group">
          <label>Contract Amount (TZS)</label>
          <input
            type="number"
            name="contract_amount"
            value={formData.contract_amount}
            onChange={handleChange}
            placeholder="e.g., 500000000"
            step="0.01"
          />
          <small>Total contract value in Tanzania Shillings (optional)</small>
        </div>

        {/* Dates Row 1 */}
        <div className="form-row">
          <div className="form-group">
            <label>Date Received</label>
            <input
              type="date"
              name="date_received"
              value={formData.date_received}
              onChange={handleChange}
            />
            <small>When tender was received</small>
          </div>

          <div className="form-group">
            <label>Date Vetted</label>
            <input
              type="date"
              name="date_vetted"
              value={formData.date_vetted}
              onChange={handleChange}
            />
            <small>When tender was vetted</small>
          </div>
        </div>

        {/* Dates Row 2 */}
        <div className="form-row">
          <div className="form-group">
            <label>Date Signed</label>
            <input
              type="date"
              name="date_signed"
              value={formData.date_signed}
              onChange={handleChange}
            />
            <small>Contract signing date</small>
          </div>

          <div className="form-group">
            <label>Commencement Date</label>
            <input
              type="date"
              name="commencement_date"
              value={formData.commencement_date}
              onChange={handleChange}
            />
            <small>Project start date</small>
          </div>
        </div>

        {/* Completion Date and Status */}
        <div className="form-row">
          <div className="form-group">
            <label>Completion Date</label>
            <input
              type="date"
              name="completion_date"
              value={formData.completion_date}
              onChange={handleChange}
            />
            <small>Expected/actual completion date</small>
          </div>

          <div className="form-group">
            <label>Status</label>
            <select
              name="status"
              value={formData.status}
              onChange={handleChange}
            >
              <option value="Draft">Draft</option>
              <option value="Under Review">Under Review</option>
              <option value="Signed">Signed</option>
              <option value="Active">Active</option>
              <option value="Completed">Completed</option>
              <option value="Expired">Expired</option>
              <option value="Terminated">Terminated</option>
            </select>
          </div>
        </div>

        {/* Document Upload */}
        <div className="form-group">
          <label>Upload Contract Document</label>
          <div className="file-upload-area">
            <input
              type="file"
              id="contract-document"
              onChange={handleFileChange}
              accept=".pdf,.doc,.docx,.xls,.xlsx"
            />
            <label htmlFor="contract-document" className="file-upload-label">
              📎 Click to browse or drag file here
              <small>Accepted formats: PDF, DOC, DOCX, XLS, XLSX (Max 10MB)</small>
            </label>
            {document && (
              <div className="selected-files">
                <strong>Selected file:</strong>
                <p>📄 {document.name} ({(document.size / 1024 / 1024).toFixed(2)} MB)</p>
              </div>
            )}
          </div>
        </div>

        {/* Notes */}
        <div className="form-group">
          <label>Notes</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            placeholder="Additional notes or remarks about this contract..."
            rows="4"
          />
          <small>Internal notes and comments</small>
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
            {loading ? '⏳ Creating Contract...' : '✅ Create Contract'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateContract;
