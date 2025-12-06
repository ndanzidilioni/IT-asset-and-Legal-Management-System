import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/ViewCase.css';

const ViewCase = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [caseData, setCaseData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadCase();
  }, [id]);

  const loadCase = async () => {
    try {
      console.log('Loading case with ID:', id);
      const response = await legalApi.cases.getById(id);
      console.log('Case API response:', response);
      
      if (response.success && response.data) {
        setCaseData(response.data);
        setError('');
      } else if (response.data) {
        // Sometimes response.success might not be set but data exists
        setCaseData(response.data);
        setError('');
      } else {
        setError('Case not found or no data returned');
      }
      setLoading(false);
    } catch (err) {
      console.error('Error loading case:', err);
      console.error('Error details:', err.message);
      setError(`Failed to load case details: ${err.message}`);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading-container">Loading case details...</div>;
  }

  if (error || !caseData) {
    return (
      <div className="error-container">
        <h2>❌ {error || 'Case not found'}</h2>
        <button onClick={() => navigate('/legal/cases')} className="btn btn-primary">
          Back to Cases
        </button>
      </div>
    );
  }

  return (
    <div className="view-case-container">
      <div className="view-case-header">
        <div>
          <h1>📋 {caseData.case_number || caseData.title}</h1>
          <span className={`status-badge status-${caseData.current_status || caseData.status}`}>
            {caseData.current_status || caseData.status}
          </span>
        </div>
        <div className="header-actions">
          <button 
            onClick={() => navigate(`/legal/cases/${id}/proceedings`)}
            className="btn btn-primary"
            style={{marginRight: '10px'}}
          >
            ⚖️ Court Proceedings
          </button>
          <button 
            onClick={() => navigate(`/legal/cases/edit/${id}`)}
            className="btn btn-secondary"
          >
            ✏️ Edit Case
          </button>
          <button 
            onClick={() => navigate('/legal/cases')}
            className="btn btn-outline"
          >
            ← Back to List
          </button>
        </div>
      </div>

      <div className="case-details-grid">
        {/* Parties */}
        {caseData.parties && (
          <div className="detail-card full-width">
            <h3>👥 Parties Involved</h3>
            <p>{caseData.parties}</p>
          </div>
        )}

        {/* Case Information */}
        <div className="detail-card">
          <h3>📝 Case Information</h3>
          <div className="detail-row">
            <span className="label">Case Number:</span>
            <span className="value">{caseData.case_number || caseData.title}</span>
          </div>
          <div className="detail-row">
            <span className="label">Nature of Case:</span>
            <span className="value">{caseData.nature_of_case || caseData.case_type}</span>
          </div>
          <div className="detail-row">
            <span className="label">Date Filed:</span>
            <span className="value">{caseData.date_filed || caseData.filed_date || 'N/A'}</span>
          </div>
          {caseData.amount_in_claim && (
            <div className="detail-row">
              <span className="label">Amount in Claim:</span>
              <span className="value">TZS {parseFloat(caseData.amount_in_claim).toLocaleString()}</span>
            </div>
          )}
        </div>

        {/* Status & Hearing */}
        <div className="detail-card">
          <h3>⚖️ Status & Schedule</h3>
          <div className="detail-row">
            <span className="label">Current Status:</span>
            <span className="value">{caseData.current_status || caseData.status}</span>
          </div>
          {caseData.next_hearing_date && (
            <div className="detail-row">
              <span className="label">Next Hearing:</span>
              <span className="value highlight">{caseData.next_hearing_date}</span>
            </div>
          )}
          {caseData.any_appeal && (
            <div className="detail-row">
              <span className="label">Appeal Status:</span>
              <span className={`value ${caseData.any_appeal !== 'No' ? 'warning' : ''}`}>
                {caseData.any_appeal}
              </span>
            </div>
          )}
        </div>

        {/* Assigned Information */}
        <div className="detail-card">
          <h3>👤 Assignment</h3>
          {caseData.assigned_lawyer && (
            <div className="detail-row">
              <span className="label">Assigned Lawyer:</span>
              <span className="value">{typeof caseData.assigned_lawyer === 'object' ? (caseData.assigned_lawyer.fname + ' ' + caseData.assigned_lawyer.lname) : String(caseData.assigned_lawyer || 'Not assigned')}</span>
            </div>
          )}
        </div>

        {/* Dates */}
        <div className="detail-card">
          <h3>📅 Important Dates</h3>
          <div className="detail-row">
            <span className="label">Created:</span>
            <span className="value">{caseData.created_at ? new Date(caseData.created_at).toLocaleDateString() : 'N/A'}</span>
          </div>
          <div className="detail-row">
            <span className="label">Last Updated:</span>
            <span className="value">{caseData.updated_at ? new Date(caseData.updated_at).toLocaleDateString() : 'N/A'}</span>
          </div>
        </div>

        {/* Remarks */}
        {caseData.remarks && (
          <div className="detail-card full-width">
            <h3>📄 Remarks</h3>
            <p className="remarks-text">{caseData.remarks}</p>
          </div>
        )}

        {caseData.description && (
          <div className="detail-card full-width">
            <h3>📄 Description</h3>
            <p className="remarks-text">{caseData.description}</p>
          </div>
        )}

        {/* Documents */}
        {caseData.documents && caseData.documents.length > 0 && (
          <div className="detail-card full-width">
            <h3>📎 Attached Documents ({caseData.documents.length})</h3>
            <div style={{display: 'flex', flexDirection: 'column', gap: '10px', marginTop: '15px'}}>
              {caseData.documents.map((doc, index) => (
                <div key={doc.id || index} style={{
                  padding: '15px',
                  border: '1px solid #e5e7eb',
                  borderRadius: '8px',
                  backgroundColor: '#f9fafb',
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center'
                }}>
                  <div style={{flex: 1}}>
                    <div style={{fontWeight: '600', marginBottom: '5px'}}>
                      📄 {doc.document_name || doc.file_name}
                    </div>
                    <div style={{fontSize: '14px', color: '#6b7280'}}>
                      <span>Type: {doc.document_type || 'N/A'}</span>
                      {doc.file_size && (
                        <span style={{marginLeft: '15px'}}>
                          Size: {(doc.file_size / 1024).toFixed(2)} KB
                        </span>
                      )}
                      {doc.created_at && (
                        <span style={{marginLeft: '15px'}}>
                          Uploaded: {new Date(doc.created_at).toLocaleDateString()}
                        </span>
                      )}
                    </div>
                  </div>
                  <button 
                    onClick={() => window.open(`http://localhost:8000/api/documents/${doc.id}/download`, '_blank')}
                    className="btn btn-info"
                    style={{
                      padding: '8px 16px',
                      fontSize: '14px',
                      marginLeft: '15px'
                    }}
                  >
                    📥 Download
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Fallback for single document (legacy) */}
        {!caseData.documents && caseData.document_name && (
          <div className="detail-card">
            <h3>📎 Case Document</h3>
            <div className="detail-row">
              <span className="label">Document:</span>
              <span className="value">{caseData.document_name}</span>
            </div>
            {caseData.document_path && (
              <button 
                onClick={() => legalApi.cases.downloadDocument(id)}
                className="btn btn-info" 
                style={{marginTop: '10px', width: '100%'}}
              >
                📥 Download Document
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default ViewCase;
