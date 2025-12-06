import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import authService from '../../services/authService';
import '../../styles/CreateCase.css';

const EditContract = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [document, setDocument] = useState(null);
  const [loadingContract, setLoadingContract] = useState(true);
  const [existingDocuments, setExistingDocuments] = useState([]);
  const [deletingDoc, setDeletingDoc] = useState(null);
  
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

  useEffect(() => {
    loadContract();
    loadDocuments();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const loadContract = async () => {
    try {
      console.log('🔍 EditContract loading contract with ID:', id);
      const response = await legalApi.contracts.getById(id);
      console.log('📋 EditContract contract response:', response);
      
      if (response.success && response.data) {
        const contract = response.data;
        
        // Helper function to convert invalid dates to empty string
        const formatDate = (date) => {
          if (!date || date === '0000-00-00' || date === 'null') return '';
          return date;
        };
        
        // Extract tender number from description if available
        const extractTenderNumber = (description) => {
          if (!description) return '';
          const match = description.match(/Tender Number: ([^\n]+)/);
          return match ? match[1] : '';
        };
        
        const extractYear = (description) => {
          if (!description) return currentFiscalYear;
          // Try to match fiscal year format first (e.g., 2025/2026)
          const fiscalMatch = description.match(/Year: (\d{4}\/\d{4})/);
          if (fiscalMatch) return fiscalMatch[1];
          // Fall back to single year format
          const match = description.match(/Year: (\d{4})/);
          return match ? `${match[1]}/${parseInt(match[1]) + 1}` : currentFiscalYear;
        };
        
        const extractCategory = (description) => {
          if (!description) return 'PMU';
          const match = description.match(/Category: ([^\n]+)/);
          return match ? match[1] : 'PMU';
        };
        
        const extractDateReceived = (description) => {
          if (!description) return '';
          const match = description.match(/Date Received: ([^\n]+)/);
          return match ? formatDate(match[1]) : '';
        };
        
        const extractDateVetted = (description) => {
          if (!description) return '';
          const match = description.match(/Date Vetted: ([^\n]+)/);
          return match ? formatDate(match[1]) : '';
        };
        
        console.log('✅ EditContract mapping contract data:', contract);
        
        setFormData({
          tender_number: contract.contract_number || extractTenderNumber(contract.description) || '',
          project_name: contract.title || '',
          supplier_contractor: contract.client_name || '',
          contract_amount: contract.contract_value || '',
          contract_year: extractYear(contract.description),
          category: extractCategory(contract.description),
          date_received: extractDateReceived(contract.description),
          date_vetted: extractDateVetted(contract.description),
          date_signed: formatDate(contract.start_date),
          commencement_date: formatDate(contract.start_date),
          completion_date: formatDate(contract.end_date),
          status: contract.status || 'Draft',
          notes: contract.notes || ''
        });
        
        console.log('✅ EditContract form data set:', {
          tender_number: contract.contract_number || extractTenderNumber(contract.description) || '',
          project_name: contract.title || '',
          supplier_contractor: contract.client_name || '',
          contract_amount: contract.contract_value || '',
          contract_year: extractYear(contract.description),
          category: extractCategory(contract.description),
          date_received: extractDateReceived(contract.description),
          date_vetted: extractDateVetted(contract.description),
          date_signed: formatDate(contract.start_date),
          commencement_date: formatDate(contract.start_date),
          completion_date: formatDate(contract.end_date),
          status: contract.status || 'Draft',
          notes: contract.notes || ''
        });
      } else {
        console.error('Contract not found or invalid response:', response);
        setError(`Contract not found. Please check if contract ID ${id} exists.`);
      }
      setLoadingContract(false);
    } catch (err) {
      console.error('Error loading contract:', err);
      setError(`Failed to load contract: ${err.message || 'Unknown error'}`);
      setLoadingContract(false);
    }
  };

  const loadDocuments = async () => {
    try {
      const response = await legalApi.contracts.getDocuments(id);
      if (response.success) {
        setExistingDocuments(response.data || []);
      }
    } catch (err) {
      console.error('Error loading documents:', err);
    }
  };

  const handleDeleteDocument = async (docId) => {
    if (!window.confirm('Are you sure you want to delete this document?')) {
      return;
    }
    
    setDeletingDoc(docId);
    try {
      const response = await legalApi.contracts.deleteDocument(docId);
      if (response.success) {
        setExistingDocuments(prev => prev.filter(doc => doc.id !== docId));
        alert('Document deleted successfully!');
      } else {
        alert('Failed to delete document');
      }
    } catch (err) {
      console.error('Error deleting document:', err);
      alert('Error deleting document');
    } finally {
      setDeletingDoc(null);
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
    const file = e.target.files[0];
    if (file) {
      setDocument(file);
    }
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
        notes: formData.notes
      };

      // Update contract
      const response = await legalApi.contracts.update(id, contractData);

      if (response.success) {
        // Upload document if provided
        if (document) {
          try {
            const uploadResponse = await legalApi.contracts.uploadDocument(id, document);
            if (uploadResponse.success) {
              setSuccess(`Contract ${formData.tender_number} and document uploaded successfully!`);
              // Reload documents to show the newly uploaded one
              await loadDocuments();
              // Clear the file input
              setDocument(null);
              // Clear the file input element
              const fileInput = window.document.getElementById('contract-document');
              if (fileInput) fileInput.value = '';
            } else {
              setSuccess(`Contract updated but document upload failed: ${uploadResponse.message}`);
            }
          } catch (uploadErr) {
            console.error('Document upload error:', uploadErr);
            setSuccess(`Contract updated but document upload failed`);
          }
        } else {
          setSuccess(`Contract ${formData.tender_number} updated successfully!`);
        }

        setTimeout(() => {
          navigate(`/legal/contracts/view/${id}`);
        }, 2000);
      } else {
        setError(response.message || 'Failed to update contract');
      }
    } catch (err) {
      console.error('Error updating contract:', err);
      setError(err.message || 'An error occurred while updating the contract');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate(`/legal/contracts/view/${id}`);
  };

  if (loadingContract) {
    return <div className="loading">Loading contract...</div>;
  }

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>✏️ Edit Contract</h1>
        <p>Update contract information</p>
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
            <input
              type="text"
              name="tender_number"
              value={formData.tender_number}
              onChange={handleChange}
              placeholder="e.g., TEND-2024-001"
              required
            />
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
              <option value="Vetted">Vetted</option>
              <option value="Signed">Signed</option>
              <option value="Active">Active</option>
              <option value="Completed">Completed</option>
              <option value="Terminated">Terminated</option>
            </select>
          </div>
        </div>

        {/* Existing Documents */}
        {existingDocuments.length > 0 && (
          <div className="form-group">
            <label>Existing Documents</label>
            <div className="existing-documents-list">
              {existingDocuments.map((doc) => (
                <div key={doc.id} className="document-item">
                  <div className="document-info">
                    <span className="document-icon">📄</span>
                    <div className="document-details">
                      <strong>{doc.document_name}</strong>
                      <small>
                        Uploaded: {new Date(doc.created_at).toLocaleDateString()} | 
                        Size: {doc.file_size ? (doc.file_size / 1024).toFixed(2) + ' KB' : 'N/A'}
                      </small>
                    </div>
                  </div>
                  <div className="document-actions">
                    <button
                      type="button"
                      onClick={() => legalApi.contracts.downloadDocument(doc.id)}
                      className="btn-sm btn-info"
                      title="Download"
                    >
                      ⬇️ Download
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDeleteDocument(doc.id)}
                      className="btn-sm btn-danger"
                      disabled={deletingDoc === doc.id}
                      title="Delete"
                    >
                      {deletingDoc === doc.id ? '⏳' : '🗑️'} Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Document Upload */}
        <div className="form-group">
          <label>Upload New Contract Document</label>
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
            {loading ? '⏳ Updating Contract...' : '✅ Update Contract'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default EditContract;
