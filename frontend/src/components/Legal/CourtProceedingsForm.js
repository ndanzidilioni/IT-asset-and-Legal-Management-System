import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import '../../styles/CreateCase.css';

const CourtProceedingsForm = () => {
  const { caseId } = useParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [caseDetails, setCaseDetails] = useState(null);
  const [proceedings, setProceedings] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState(null);

  const [formData, setFormData] = useState({
    case_id: caseId,
    hearing_date: '',
    name_of_court: '',
    parties: '',
    case_number: '',
    court_judge: '',
    clerk_karani: '',
    advocate_for_opponent: '',
    advocate_for_moi: '',
    proceedings: '',
    court_order: '',
    next_date: '',
    remarks: ''
  });

  useEffect(() => {
    loadCaseDetails();
    loadProceedings();
  }, [caseId]);

  const loadCaseDetails = async () => {
    try {
      console.log('🔍 CourtProceedingsForm loading case details for ID:', caseId);
      const response = await fetch(`http://localhost/cases-api.php?endpoint=case&id=${caseId}`);
      
      if (!response.ok) {
        throw new Error(`Failed to load case: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('📋 CourtProceedingsForm case details loaded:', data);
      
      if (data.success) {
        setCaseDetails(data.data);
      } else {
        throw new Error(data.error || 'Failed to load case details');
      }
      
      // Pre-fill case information
      setFormData(prev => ({
        ...prev,
        parties: data.parties || '',
        case_number: data.case_number || '',
        name_of_court: data.court_name || ''
      }));
    } catch (error) {
      console.error('Error loading case:', error);
    }
  };

  const loadProceedings = async () => {
    try {
      console.log('🔍 CourtProceedingsForm loading proceedings for case:', caseId);
      const response = await fetch(`http://localhost/court-proceedings-api.php/court-proceedings/case/${caseId}`);
      
      if (!response.ok) {
        throw new Error(`Failed to load proceedings: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('📋 CourtProceedingsForm proceedings loaded:', data);
      
      if (data.success) {
        setProceedings(data.data || []);
      } else {
        console.error('Failed to load proceedings:', data.error);
        setProceedings([]);
      }
    } catch (error) {
      console.error('🚨 CourtProceedingsForm error loading proceedings:', error);
      setProceedings([]);
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
    setLoading(true);

    try {
      console.log('🔄 CourtProceedingsForm submitting:', { editingId, formData });
      
      const url = editingId 
        ? `http://localhost/court-proceedings-api.php/court-proceedings/${editingId}`
        : 'http://localhost/court-proceedings-api.php';
      
      const method = editingId ? 'PUT' : 'POST';
      
      const response = await fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      console.log('📋 CourtProceedingsForm response status:', response.status);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }

      const data = await response.json();
      console.log('✅ CourtProceedingsForm response:', data);

      if (data.success) {
        setSuccess(editingId ? 'Court proceeding updated successfully!' : 'Court proceeding recorded successfully!');
        setShowForm(false);
        setEditingId(null);
        loadProceedings();
        
        // Reset form
        setFormData({
          case_id: caseId,
          hearing_date: '',
          name_of_court: caseDetails?.court_name || '',
          parties: caseDetails?.parties || '',
          case_number: caseDetails?.case_number || '',
          court_judge: '',
          clerk_karani: '',
          advocate_for_opponent: '',
          advocate_for_moi: '',
          proceedings: '',
          court_order: '',
          next_date: '',
          remarks: ''
        });
      } else {
        setError(data.error || data.message || 'Failed to save court proceeding');
      }
    } catch (err) {
      console.error('🚨 CourtProceedingsForm error:', err);
      setError('An error occurred while saving: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (proc) => {
    setEditingId(proc.id);
    setFormData({
      case_id: caseId,
      hearing_date: proc.hearing_date || '',
      name_of_court: proc.name_of_court || '',
      parties: proc.parties || '',
      case_number: proc.case_number || '',
      court_judge: proc.court_judge || '',
      clerk_karani: proc.clerk_karani || '',
      advocate_for_opponent: proc.advocate_for_opponent || '',
      advocate_for_moi: proc.advocate_for_moi || '',
      proceedings: proc.proceedings || '',
      court_order: proc.court_order || '',
      next_date: proc.next_date || '',
      remarks: proc.remarks || ''
    });
    setShowForm(true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleCancelEdit = () => {
    setEditingId(null);
    setShowForm(false);
    setFormData({
      case_id: caseId,
      hearing_date: '',
      name_of_court: caseDetails?.court_name || '',
      parties: caseDetails?.parties || '',
      case_number: caseDetails?.case_number || '',
      court_judge: '',
      clerk_karani: '',
      advocate_for_opponent: '',
      advocate_for_moi: '',
      proceedings: '',
      court_order: '',
      next_date: '',
      remarks: ''
    });
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this proceeding?')) return;

    try {
      console.log('🗑️ CourtProceedingsForm deleting proceeding:', id);
      const response = await fetch(`http://localhost/court-proceedings-api.php/court-proceedings/${id}`, {
        method: 'DELETE'
      });

      console.log('📋 CourtProceedingsForm delete response status:', response.status);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`HTTP ${response.status}: ${errorText}`);
      }

      const data = await response.json();
      console.log('✅ CourtProceedingsForm delete response:', data);

      if (data.success) {
        setSuccess('Proceeding deleted successfully');
        loadProceedings();
      } else {
        setError(data.error || 'Failed to delete proceeding');
      }
    } catch (error) {
      console.error('🚨 CourtProceedingsForm error deleting proceeding:', error);
      setError('Failed to delete proceeding: ' + error.message);
    }
  };

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>⚖️ Court Proceedings - {caseDetails?.case_number}</h1>
        <p>Track all hearing activities and court proceedings</p>
      </div>

      {caseDetails && (
        <div style={{
          background: '#f3f4f6',
          padding: '15px',
          borderRadius: '8px',
          marginBottom: '20px'
        }}>
          <h3 style={{margin: '0 0 10px 0'}}>Case Information</h3>
          <p><strong>Case No:</strong> {caseDetails.case_number}</p>
          <p><strong>Parties:</strong> {caseDetails.parties}</p>
          <p><strong>Case Type:</strong> {caseDetails.case_type}</p>
        </div>
      )}

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

      <div style={{marginBottom: '20px'}}>
        <button
          onClick={() => {
            if (showForm && !editingId) {
              setShowForm(false);
            } else if (showForm && editingId) {
              handleCancelEdit();
            } else {
              setShowForm(true);
              setEditingId(null);
            }
          }}
          className="btn btn-primary"
          style={{
            padding: '12px 24px',
            fontSize: '16px',
            fontWeight: '600',
            backgroundColor: '#3b82f6',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            cursor: 'pointer'
          }}
        >
          {showForm ? '✖️ Cancel' : '➕ Add New Court Proceeding'}
        </button>
      </div>

      {showForm && (
        <form onSubmit={handleSubmit} className="case-form">
          <h2>{editingId ? '✏️ Edit Court Proceeding' : '➕ New Court Proceeding'}</h2>

          {/* Hearing Date */}
          <div className="form-group">
            <label>Hearing Date <span className="required">*</span></label>
            <input
              type="date"
              name="hearing_date"
              value={formData.hearing_date}
              onChange={handleChange}
              required
            />
          </div>

          {/* Name of Court and Case Number */}
          <div className="form-row">
            <div className="form-group">
              <label>Name of Court</label>
              <input
                type="text"
                name="name_of_court"
                value={formData.name_of_court}
                onChange={handleChange}
                placeholder="e.g., High Court of Tanzania"
              />
            </div>

            <div className="form-group">
              <label>Case No</label>
              <input
                type="text"
                name="case_number"
                value={formData.case_number}
                onChange={handleChange}
                placeholder="Official case number"
              />
            </div>
          </div>

          {/* Parties */}
          <div className="form-group">
            <label>Parties</label>
            <input
              type="text"
              name="parties"
              value={formData.parties}
              onChange={handleChange}
              placeholder="All parties involved"
            />
          </div>

          {/* Court/Judge and Clerk/Karani */}
          <div className="form-row">
            <div className="form-group">
              <label>Court/Judge</label>
              <input
                type="text"
                name="court_judge"
                value={formData.court_judge}
                onChange={handleChange}
                placeholder="Judge name"
              />
            </div>

            <div className="form-group">
              <label>Clerk/Karani</label>
              <input
                type="text"
                name="clerk_karani"
                value={formData.clerk_karani}
                onChange={handleChange}
                placeholder="Court clerk name"
              />
            </div>
          </div>

          {/* Advocates */}
          <div className="form-row">
            <div className="form-group">
              <label>Advocate for Opponent</label>
              <input
                type="text"
                name="advocate_for_opponent"
                value={formData.advocate_for_opponent}
                onChange={handleChange}
                placeholder="Opponent's advocate"
              />
            </div>

            <div className="form-group">
              <label>Advocate for MOI</label>
              <input
                type="text"
                name="advocate_for_moi"
                value={formData.advocate_for_moi}
                onChange={handleChange}
                placeholder="MOI's advocate"
              />
            </div>
          </div>

          {/* Proceedings */}
          <div className="form-group">
            <label>Proceedings</label>
            <textarea
              name="proceedings"
              value={formData.proceedings}
              onChange={handleChange}
              placeholder="Describe what happened in court..."
              rows="4"
            />
            <small>Details of the court proceedings</small>
          </div>

          {/* Order */}
          <div className="form-group">
            <label>Order</label>
            <textarea
              name="court_order"
              value={formData.court_order}
              onChange={handleChange}
              placeholder="Court order or ruling..."
              rows="3"
            />
            <small>Court's order or decision</small>
          </div>

          {/* Next Date */}
          <div className="form-group">
            <label>Next Date</label>
            <input
              type="date"
              name="next_date"
              value={formData.next_date}
              onChange={handleChange}
            />
            <small>Next hearing/appearance date</small>
          </div>

          {/* Remarks */}
          <div className="form-group">
            <label>Remarks</label>
            <textarea
              name="remarks"
              value={formData.remarks}
              onChange={handleChange}
              placeholder="Additional remarks or notes..."
              rows="2"
            />
          </div>

          {/* Form Actions */}
          <div className="form-actions">
            <button
              type="button"
              onClick={handleCancelEdit}
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
              {loading ? '⏳ Saving...' : (editingId ? '✅ Update Proceeding' : '✅ Save Proceeding')}
            </button>
          </div>
        </form>
      )}

      {/* List of Proceedings */}
      <div style={{marginTop: '30px'}}>
        <h2>Court Proceedings History</h2>
        {!Array.isArray(proceedings) || proceedings.length === 0 ? (
          <div style={{
            padding: '40px',
            textAlign: 'center',
            background: '#f9fafb',
            borderRadius: '8px',
            border: '2px dashed #d1d5db'
          }}>
            <p style={{color: '#6b7280'}}>No court proceedings recorded yet</p>
          </div>
        ) : (
          <div className="cases-list">
            {Array.isArray(proceedings) && proceedings.map((proc) => (
              <div key={proc.id} className="case-card" style={{marginBottom: '15px'}}>
                <div className="case-header">
                  <h3>📅 {new Date(proc.hearing_date).toLocaleDateString()}</h3>
                  <div style={{display: 'flex', gap: '10px'}}>
                    <button
                      onClick={() => handleEdit(proc)}
                      style={{
                        padding: '6px 12px',
                        background: '#10b981',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontSize: '14px'
                      }}
                    >
                      ✏️ Edit
                    </button>
                    <button
                      onClick={() => handleDelete(proc.id)}
                      style={{
                        padding: '6px 12px',
                        background: '#ef4444',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontSize: '14px'
                      }}
                    >
                      🗑️ Delete
                    </button>
                  </div>
                </div>
                {proc.name_of_court && <p><strong>Court:</strong> {proc.name_of_court}</p>}
                {proc.court_judge && <p><strong>Judge:</strong> {proc.court_judge}</p>}
                {proc.clerk_karani && <p><strong>Clerk:</strong> {proc.clerk_karani}</p>}
                {proc.advocate_for_opponent && (
                  <p><strong>Advocate (Opponent):</strong> {proc.advocate_for_opponent}</p>
                )}
                {proc.advocate_for_moi && (
                  <p><strong>Advocate (MOI):</strong> {proc.advocate_for_moi}</p>
                )}
                {proc.proceedings && (
                  <div>
                    <strong>Proceedings:</strong>
                    <p style={{whiteSpace: 'pre-wrap', marginTop: '5px'}}>{proc.proceedings}</p>
                  </div>
                )}
                {proc.court_order && (
                  <div>
                    <strong>Order:</strong>
                    <p style={{whiteSpace: 'pre-wrap', marginTop: '5px'}}>{proc.court_order}</p>
                  </div>
                )}
                {proc.next_date && (
                  <p><strong>Next Date:</strong> {new Date(proc.next_date).toLocaleDateString()}</p>
                )}
                {proc.remarks && (
                  <p><strong>Remarks:</strong> {proc.remarks}</p>
                )}
                <p style={{fontSize: '12px', color: '#6b7280', marginTop: '10px'}}>
                  Recorded: {new Date(proc.created_at).toLocaleString()}
                </p>
              </div>
            ))}
          </div>
        )}
      </div>

      <div style={{marginTop: '20px'}}>
        <button
          onClick={() => navigate('/legal/cases')}
          className="btn btn-secondary"
        >
          ← Back to Cases
        </button>
      </div>
    </div>
  );
};

export default CourtProceedingsForm;
