import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/ViewCase.css';

const ViewContract = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [contract, setContract] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
  const [documents, setDocuments] = useState([]);

  useEffect(() => {
    loadContract();
    loadDocuments();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const loadContract = async () => {
    try {
      const response = await legalApi.contracts.getById(id);
      if (response.success) {
        const contractData = response.data;
        
        // Extract additional fields from description
        const extractFromDescription = (description, pattern) => {
          if (!description) return null;
          const match = description.match(pattern);
          return match ? match[1] : null;
        };
        
        // Enhance contract data with extracted fields for display
        const enhancedContract = {
          ...contractData,
          tender_number: extractFromDescription(contractData.description, /Tender Number: ([^\n]+)/) || contractData.contract_number,
          contract_year: extractFromDescription(contractData.description, /Year: ([^\n]+)/) || 'N/A',
          category: extractFromDescription(contractData.description, /Category: ([^\n]+)/) || 'N/A',
          project_name: contractData.title,
          supplier_contractor: contractData.client_name,
          contract_amount: contractData.contract_value,
          date_signed: contractData.start_date,
          completion_date: contractData.end_date
        };
        
        setContract(enhancedContract);
      } else {
        setError('Contract not found');
      }
      setLoading(false);
    } catch (err) {
      console.error('Error loading contract:', err);
      setError('Failed to load contract details');
      setLoading(false);
    }
  };

  const loadDocuments = async () => {
    try {
      const response = await legalApi.contracts.getDocuments(id);
      if (response.success) {
        setDocuments(response.data || []);
      }
    } catch (err) {
      console.error('Error loading documents:', err);
    }
  };

  const formatCurrency = (amount) => {
    return 'Tsh ' + new Intl.NumberFormat('en-TZ', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const handleDelete = async () => {
    try {
      const response = await legalApi.contracts.delete(id);
      if (response.success) {
        alert('Contract deleted successfully!');
        navigate('/legal/contracts');
      } else {
        alert('Failed to delete contract: ' + response.message);
      }
    } catch (err) {
      console.error('Error deleting contract:', err);
      alert('Failed to delete contract');
    }
    setShowDeleteConfirm(false);
  };

  if (loading) {
    return <div className="loading-container">Loading contract details...</div>;
  }

  if (error || !contract) {
    return (
      <div className="error-container">
        <h2>❌ {error || 'Contract not found'}</h2>
        <button onClick={() => navigate('/legal/contracts')} className="btn btn-primary">
          Back to Contracts
        </button>
      </div>
    );
  }

  return (
    <div className="view-case-container">
      <div className="view-case-header">
        <div>
          <h1>📋 {contract.tender_number}</h1>
          <span className={`status-badge status-${contract.status}`}>
            {contract.status}
          </span>
        </div>
        <div className="header-actions">
          <button 
            onClick={() => navigate(`/legal/contracts/edit/${id}`)}
            className="btn btn-success"
            style={{marginRight: '10px'}}
          >
            ✏️ Edit
          </button>
          <button 
            onClick={() => setShowDeleteConfirm(true)}
            className="btn btn-danger"
            style={{marginRight: '10px'}}
          >
            🗑️ Delete
          </button>
          <button 
            onClick={() => navigate('/legal/contracts')}
            className="btn btn-outline"
          >
            ← Back to Register
          </button>
        </div>
      </div>

      <div className="case-details-grid">
        {/* Project Information */}
        <div className="detail-card full-width">
          <h3>📝 Project Information</h3>
          <div className="detail-row">
            <span className="label">Project Name:</span>
            <span className="value"><strong>{contract.project_name}</strong></span>
          </div>
          <div className="detail-row">
            <span className="label">Supplier/Contractor:</span>
            <span className="value">{contract.supplier_contractor}</span>
          </div>
          <div className="detail-row">
            <span className="label">Contract Amount:</span>
            <span className="value highlight"><strong>{contract.contract_amount ? formatCurrency(contract.contract_amount) : 'Not specified'}</strong></span>
          </div>
          <div className="detail-row">
            <span className="label">Contract Year:</span>
            <span className="value">{contract.contract_year}</span>
          </div>
          <div className="detail-row">
            <span className="label">Category:</span>
            <span className="value">
              <span className={`badge badge-${contract.category === 'PMU' ? 'primary' : 'info'}`}>
                {contract.category}
              </span>
            </span>
          </div>
        </div>

        {/* Important Dates */}
        <div className="detail-card">
          <h3>📅 Important Dates</h3>
          <div className="detail-row">
            <span className="label">Date Received:</span>
            <span className="value">{contract.date_received || '-'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Date Vetted:</span>
            <span className="value">{contract.date_vetted || '-'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Date Signed:</span>
            <span className="value">{contract.date_signed || '-'}</span>
          </div>
        </div>

        {/* Project Timeline */}
        <div className="detail-card">
          <h3>⏱️ Project Timeline</h3>
          <div className="detail-row">
            <span className="label">Commencement Date:</span>
            <span className="value">{contract.commencement_date || '-'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Completion Date:</span>
            <span className="value">{contract.completion_date || '-'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Status:</span>
            <span className={`value ${contract.status === 'Active' ? 'highlight' : ''}`}>
              {contract.status}
            </span>
          </div>
        </div>

        {/* Attached Documents */}
        {documents.length > 0 && (
          <div className="detail-card full-width">
            <h3>📎 Attached Documents ({documents.length})</h3>
            <div className="documents-list">
              {documents.map((doc) => (
                <div key={doc.id} className="document-item-view">
                  <div className="document-info-view">
                    <span className="document-icon-large">📄</span>
                    <div className="document-details-view">
                      <strong>{doc.document_name}</strong>
                      <div className="document-meta">
                        <span>Type: {doc.document_type}</span>
                        <span>Uploaded: {new Date(doc.created_at).toLocaleDateString()}</span>
                        {doc.file_size && <span>Size: {(doc.file_size / 1024).toFixed(2)} KB</span>}
                      </div>
                    </div>
                  </div>
                  <button
                    onClick={() => legalApi.contracts.downloadDocument(doc.id)}
                    className="btn btn-info btn-sm"
                    style={{minWidth: '120px'}}
                  >
                    ⬇️ Download
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Audit Trail */}
        <div className="detail-card">
          <h3>👤 Audit Trail</h3>
          <div className="detail-row">
            <span className="label">Created By:</span>
            <span className="value">{contract.created_by || 'System'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Created At:</span>
            <span className="value">{new Date(contract.created_at).toLocaleString()}</span>
          </div>
          {contract.updated_by && (
            <div className="detail-row">
              <span className="label">Last Updated By:</span>
              <span className="value">{contract.updated_by}</span>
            </div>
          )}
          <div className="detail-row">
            <span className="label">Last Updated:</span>
            <span className="value">{new Date(contract.updated_at).toLocaleString()}</span>
          </div>
        </div>

        {/* Notes */}
        {contract.notes && (
          <div className="detail-card full-width">
            <h3>📝 Notes</h3>
            <p className="remarks-text">{contract.notes}</p>
          </div>
        )}
      </div>

      {/* Delete Confirmation Modal */}
      {showDeleteConfirm && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0,0,0,0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000
        }}>
          <div style={{
            background: 'white',
            padding: '30px',
            borderRadius: '12px',
            maxWidth: '500px',
            width: '90%'
          }}>
            <h3 style={{marginTop: 0}}>⚠️ Confirm Delete</h3>
            <p>Are you sure you want to delete contract <strong>{contract.tender_number}</strong>?</p>
            <p style={{color: '#dc2626'}}>This action cannot be undone.</p>
            <div style={{display: 'flex', gap: '10px', marginTop: '20px'}}>
              <button 
                onClick={() => setShowDeleteConfirm(false)}
                className="btn btn-secondary"
                style={{flex: 1}}
              >
                Cancel
              </button>
              <button 
                onClick={handleDelete}
                className="btn btn-danger"
                style={{flex: 1}}
              >
                🗑️ Delete Contract
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ViewContract;
