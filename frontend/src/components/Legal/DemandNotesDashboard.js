import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import './DemandNotesDashboard.css';

const DemandNotesDashboard = () => {
  const navigate = useNavigate();
  const [demandNotes, setDemandNotes] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [pendingDeletions, setPendingDeletions] = useState([]);
  const [showPendingApprovals, setShowPendingApprovals] = useState(false);
  const [deleteConfirm, setDeleteConfirm] = useState(null);
  const [deletionReason, setDeletionReason] = useState('');
  const [reviewComment, setReviewComment] = useState('');
  const [reviewingRequest, setReviewingRequest] = useState(null);
  
  // Filters
  const [filters, setFilters] = useState({
    status: '',
    client_name: '',
    nature_of_claim: '',
    start_date: '',
    end_date: '',
    search: ''
  });
  
  // Pagination
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    total: 0
  });

  useEffect(() => {
    fetchDemandNotes();
    fetchStatistics();
    loadPendingDeletions();
  }, [filters, pagination.current_page]);

  const fetchDemandNotes = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await legalApi.demandNotes.getAll();
      
      if (response.success) {
        setDemandNotes(response.data || []);
        // Handle pagination if provided by the API
        if (response.pagination) {
          setPagination(response.pagination);
        }
      } else {
        setDemandNotes([]);
      }
    } catch (err) {
      console.error('Failed to fetch demand notes:', err);
      setError('Failed to fetch demand notes: ' + err.message);
      setDemandNotes([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await legalApi.demandNotes.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (err) {
      console.error('Failed to fetch statistics:', err);
    }
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  const handleClearFilters = () => {
    setFilters({
      status: '',
      client_name: '',
      nature_of_claim: '',
      start_date: '',
      end_date: '',
      search: ''
    });
  };

  const loadPendingDeletions = async () => {
    try {
      const response = await legalApi.demandNoteDeletionRequests.getPending();
      if (response.success) {
        const data = response.data || [];
        setPendingDeletions(data);
        return data;
      }
      return [];
    } catch (error) {
      console.error('Error loading pending deletions:', error);
      return [];
    }
  };

  const handleDeleteRequest = async () => {
    if (!deletionReason.trim()) {
      alert('Please provide a reason for deletion');
      return;
    }

    try {
      const response = await legalApi.demandNoteDeletionRequests.create(deleteConfirm.id, deletionReason);
      if (response.success) {
        alert('✅ ' + response.message);
        setDeleteConfirm(null);
        setDeletionReason('');
      } else {
        alert('❌ ' + (response.message || 'Failed to submit deletion request'));
      }
    } catch (err) {
      console.error('Error submitting deletion request:', err);
      alert('❌ Failed to submit deletion request');
    }
  };

  const handleApproveRequest = async (request) => {
    try {
      const response = await legalApi.demandNoteDeletionRequests.approve(request.id, reviewComment);
      if (response.success) {
        alert('✅ ' + response.message);
        setReviewingRequest(null);
        setReviewComment('');
        
        // Reload data and get updated pending list
        const updatedPending = await loadPendingDeletions();
        fetchDemandNotes();
        fetchStatistics();
        
        // Close modal if no more pending approvals
        if (updatedPending.length === 0) {
          setShowPendingApprovals(false);
        }
      } else {
        alert('❌ ' + (response.message || 'Failed to approve deletion'));
      }
    } catch (err) {
      console.error('Error approving deletion:', err);
      alert('❌ ' + (err.message || 'Failed to approve deletion'));
    }
  };

  const handleRejectRequest = async (request) => {
    if (!reviewComment.trim()) {
      alert('Please provide a reason for rejection');
      return;
    }

    try {
      const response = await legalApi.demandNoteDeletionRequests.reject(request.id, reviewComment);
      if (response.success) {
        alert('✅ ' + response.message);
        setReviewingRequest(null);
        setReviewComment('');
        
        // Reload data and get updated pending list
        const updatedPending = await loadPendingDeletions();
        
        // Close modal if no more pending approvals
        if (updatedPending.length === 0) {
          setShowPendingApprovals(false);
        }
      } else {
        alert('❌ ' + (response.message || 'Failed to reject deletion'));
      }
    } catch (err) {
      console.error('Error rejecting deletion:', err);
      alert('❌ Failed to reject deletion');
    }
  };

  const handleDelete = async (note) => {
    setDeleteConfirm(note);
  };

  const handleExport = async () => {
    try {
      const blob = await legalApi.demandNotes.exportCsv();
      
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `demand_notes_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Export failed:', err);
      alert('Failed to export demand notes');
    }
  };

  const getStatusBadgeClass = (status) => {
    const statusClasses = {
      'pending': 'status-warning',
      'paid': 'status-success',
      'partially_paid': 'status-info',
      'overdue': 'status-danger',
      'cancelled': 'status-secondary'
    };
    return statusClasses[status] || 'status-secondary';
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-TZ', {
      style: 'currency',
      currency: 'TZS'
    }).format(amount);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  return (
    <div className="demand-notes-dashboard">
      <div className="dashboard-header">
        <div>
          <h1>📋 Demand Notes Management</h1>
          <p>Track and manage payment demand notes</p>
        </div>
        <div className="header-actions">
          <button onClick={handleExport} className="btn-export">
            📥 Export CSV
          </button>
          {pendingDeletions.length > 0 && (
            <button 
              onClick={() => setShowPendingApprovals(true)}
              style={{
                padding: '10px 18px',
                background: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                color: 'white',
                border: 'none',
                borderRadius: '8px',
                cursor: 'pointer',
                fontSize: '14px',
                fontWeight: '600',
                boxShadow: '0 2px 4px rgba(245, 158, 11, 0.3)',
                display: 'flex',
                alignItems: 'center',
                gap: '6px'
              }}
            >
              ⏳ Pending Approvals ({pendingDeletions.length})
            </button>
          )}
          <Link to="/legal/demand-notes/create" className="btn-primary">
            + Create Demand Note
          </Link>
        </div>
      </div>

      {error && <div className="error-message">{error}</div>}

      {/* Statistics Cards */}
      {statistics && (
        <div className="stats-grid">
          <div className="stat-card">
            <div className="stat-icon">📊</div>
            <div className="stat-value">{statistics.total_demand_notes || 0}</div>
            <div className="stat-label">Total Notes</div>
          </div>
          
          <div className="stat-card stat-warning">
            <div className="stat-icon">⏳</div>
            <div className="stat-value">{statistics.pending || 0}</div>
            <div className="stat-label">Pending</div>
          </div>
          
          <div className="stat-card stat-danger">
            <div className="stat-icon">⚠️</div>
            <div className="stat-value">{statistics.overdue || 0}</div>
            <div className="stat-label">Overdue</div>
          </div>
          
          <div className="stat-card stat-success">
            <div className="stat-icon">✅</div>
            <div className="stat-value">{statistics.paid || 0}</div>
            <div className="stat-label">Paid</div>
          </div>
          
          <div className="stat-card stat-primary">
            <div className="stat-icon">💰</div>
            <div className="stat-value">
              TSh {new Intl.NumberFormat('en-TZ').format(statistics.total_claimed || 0)}
            </div>
            <div className="stat-label">Total Claimed</div>
          </div>
          
          <div className="stat-card stat-info">
            <div className="stat-icon">💵</div>
            <div className="stat-value">
              TSh {new Intl.NumberFormat('en-TZ').format(statistics.total_collected || 0)}
            </div>
            <div className="stat-label">Total Collected</div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="filters-section">
        <div className="filters-row">
          <input
            type="text"
            name="search"
            placeholder="Search by note number, client name, or reference..."
            value={filters.search}
            onChange={handleFilterChange}
            className="filter-input filter-search"
          />
          
          <select
            name="status"
            value={filters.status}
            onChange={handleFilterChange}
            className="filter-select"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="partially_paid">Partially Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
          
          <select
            name="nature_of_claim"
            value={filters.nature_of_claim}
            onChange={handleFilterChange}
            className="filter-select"
          >
            <option value="">All Nature</option>
            <option value="Works">Works</option>
            <option value="Supply of Goods">Supply of Goods</option>
            <option value="Services">Services</option>
            <option value="Consultancy">Consultancy</option>
            <option value="Other">Other</option>
          </select>
          
          <input
            type="date"
            name="start_date"
            value={filters.start_date}
            onChange={handleFilterChange}
            className="filter-date"
            placeholder="Start Date"
          />
          
          <input
            type="date"
            name="end_date"
            value={filters.end_date}
            onChange={handleFilterChange}
            className="filter-date"
            placeholder="End Date"
          />
          
          <button onClick={handleClearFilters} className="btn-secondary">
            Clear
          </button>
        </div>
      </div>

      {/* Demand Notes Table */}
      {loading ? (
        <div className="loading-spinner">Loading demand notes...</div>
      ) : (
        <>
          <div className="table-container">
            <table className="demand-notes-table">
              <thead>
                <tr>
                  <th>Note Number</th>
                  <th>Client Name</th>
                  <th>Amount Claimed</th>
                  <th>Amount Paid</th>
                  <th>Balance</th>
                  <th>Due Date</th>
                  <th>Nature of Claim</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {demandNotes.length === 0 ? (
                  <tr>
                    <td colSpan="9" className="no-data">No demand notes found</td>
                  </tr>
                ) : (
                  demandNotes.map((note) => (
                    <tr key={note.id}>
                      <td>
                        <strong>{note.demand_note_number}</strong>
                      </td>
                      <td>{note.client_name}</td>
                      <td className="amount">{formatCurrency(note.amount_claimed)}</td>
                      <td className="amount">{formatCurrency(note.amount_paid || 0)}</td>
                      <td className="amount balance-due">
                        {formatCurrency(note.balance_due || 0)}
                      </td>
                      <td>{formatDate(note.due_date)}</td>
                      <td>
                        <span className="nature-badge">{note.nature_of_claim}</span>
                      </td>
                      <td>
                        <span className={`status-badge ${getStatusBadgeClass(note.current_status)}`}>
                          {note.current_status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="actions">
                        <button
                          onClick={() => navigate(`/legal/demand-notes/view/${note.id}`)}
                          className="btn-action btn-view"
                          title="View"
                        >
                          👁️
                        </button>
                        <button
                          onClick={() => navigate(`/legal/demand-notes/edit/${note.id}`)}
                          className="btn-action btn-edit"
                          title="Edit"
                        >
                          ✏️
                        </button>
                        <button
                          onClick={() => handleDelete(note)}
                          className="btn-action btn-delete"
                          title="Delete"
                        >
                          🗑️
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {pagination.last_page > 1 && (
            <div className="pagination">
              <button
                onClick={() => setPagination(prev => ({ ...prev, current_page: Math.max(1, prev.current_page - 1) }))}
                disabled={pagination.current_page === 1}
                className="pagination-btn"
              >
                ← Previous
              </button>
              
              <span className="pagination-info">
                Page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)
              </span>
              
              <button
                onClick={() => setPagination(prev => ({ ...prev, current_page: Math.min(prev.last_page, prev.current_page + 1) }))}
                disabled={pagination.current_page === pagination.last_page}
                className="pagination-btn"
              >
                Next →
              </button>
            </div>
          )}
        </>
      )}

      {/* Delete Confirmation Modal */}
      {deleteConfirm && (
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
            <h3 style={{marginTop: 0}}>🗑️ Request Demand Note Deletion</h3>
            <p>Demand Note: <strong>{deleteConfirm.demand_note_number}</strong></p>
            <p>Client: <strong>{deleteConfirm.client_name}</strong></p>
            <p style={{color: '#f59e0b', fontSize: '14px'}}>
              ⚠️ This deletion requires approval from another lawyer before the demand note is permanently deleted.
            </p>
            <div style={{marginBottom: '20px'}}>
              <label style={{display: 'block', marginBottom: '8px', fontWeight: '500'}}>
                Reason for Deletion: <span style={{color: 'red'}}>*</span>
              </label>
              <textarea
                value={deletionReason}
                onChange={(e) => setDeletionReason(e.target.value)}
                placeholder="Please provide a reason for deleting this demand note..."
                style={{
                  width: '100%',
                  minHeight: '100px',
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '6px',
                  fontSize: '14px',
                  resize: 'vertical'
                }}
              />
            </div>
            <div style={{display: 'flex', gap: '10px'}}>
              <button 
                onClick={() => {
                  setDeleteConfirm(null);
                  setDeletionReason('');
                }}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#6b7280',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              >
                Cancel
              </button>
              <button 
                onClick={handleDeleteRequest}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#ef4444',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  opacity: !deletionReason.trim() ? 0.5 : 1
                }}
                disabled={!deletionReason.trim()}
              >
                Submit Request
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Pending Approvals Modal */}
      {showPendingApprovals && (
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
            maxWidth: '800px',
            width: '90%',
            maxHeight: '80vh',
            overflow: 'auto',
            boxShadow: '0 4px 20px rgba(0,0,0,0.2)'
          }}>
            <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
              <h3 style={{margin: 0}}>⏳ Pending Demand Note Deletion Approvals ({pendingDeletions.length})</h3>
              <button 
                onClick={() => setShowPendingApprovals(false)}
                style={{background: 'none', border: 'none', fontSize: '24px', cursor: 'pointer', color: '#64748b'}}
              >
                ×
              </button>
            </div>

            {pendingDeletions.length === 0 ? (
              <p style={{textAlign: 'center', color: '#6b7280', padding: '40px'}}>No pending approvals</p>
            ) : (
              <div style={{display: 'flex', flexDirection: 'column', gap: '15px'}}>
                {pendingDeletions.map(request => (
                  <div key={request.id} style={{
                    border: '1px solid #e5e7eb',
                    borderRadius: '8px',
                    padding: '15px',
                    background: '#f9fafb'
                  }}>
                    <div style={{display: 'flex', justifyContent: 'space-between', marginBottom: '10px'}}>
                      <div>
                        <strong>Demand Note:</strong> {request.demand_note?.demand_note_number || 'N/A'}
                      </div>
                      <span style={{
                        padding: '4px 12px',
                        background: '#fef3c7',
                        color: '#92400e',
                        borderRadius: '12px',
                        fontSize: '12px',
                        fontWeight: '600'
                      }}>
                        Pending
                      </span>
                    </div>
                    <p style={{margin: '8px 0'}}><strong>Client:</strong> {request.demand_note?.client_name || 'N/A'}</p>
                    <p style={{margin: '8px 0'}}><strong>Requested by:</strong> {request.requester_name}</p>
                    <p style={{margin: '8px 0'}}><strong>Reason:</strong> {request.reason || 'No reason provided'}</p>
                    <p style={{margin: '8px 0', fontSize: '12px', color: '#6b7280'}}>
                      <strong>Requested:</strong> {new Date(request.created_at).toLocaleString()}
                    </p>

                    <button 
                      onClick={() => setReviewingRequest(request)}
                      style={{
                        marginTop: '10px',
                        padding: '8px 16px',
                        background: '#3b82f6',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontSize: '14px',
                        fontWeight: '500'
                      }}
                    >
                      Review Request
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Review Request Modal */}
      {reviewingRequest && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0,0,0,0.6)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1001
        }}>
          <div style={{
            background: 'white',
            padding: '30px',
            borderRadius: '12px',
            maxWidth: '600px',
            width: '90%'
          }}>
            <h3 style={{marginTop: 0}}>⚖️ Review Deletion Request</h3>
            
            <div style={{marginBottom: '20px', padding: '15px', background: '#f3f4f6', borderRadius: '8px'}}>
              <p><strong>Demand Note:</strong> {reviewingRequest.demand_note?.demand_note_number}</p>
              <p><strong>Client:</strong> {reviewingRequest.demand_note?.client_name}</p>
              <p><strong>Requested by:</strong> {reviewingRequest.requester_name}</p>
              <p><strong>Reason:</strong> {reviewingRequest.reason}</p>
              <p style={{fontSize: '12px', color: '#6b7280'}}>
                <strong>Date:</strong> {new Date(reviewingRequest.created_at).toLocaleString()}
              </p>
            </div>

            <div style={{marginBottom: '20px'}}>
              <label style={{display: 'block', marginBottom: '8px', fontWeight: '500'}}>
                Your Comment:
              </label>
              <textarea
                value={reviewComment}
                onChange={(e) => setReviewComment(e.target.value)}
                placeholder="Add your review comment..."
                style={{
                  width: '100%',
                  minHeight: '80px',
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '6px',
                  fontSize: '14px'
                }}
              />
            </div>

            <div style={{display: 'flex', gap: '10px'}}>
              <button 
                onClick={() => {
                  setReviewingRequest(null);
                  setReviewComment('');
                }}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#6b7280',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              >
                Cancel
              </button>
              <button 
                onClick={() => handleApproveRequest(reviewingRequest)}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#10b981',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              >
                ✓ Approve & Delete
              </button>
              <button 
                onClick={() => handleRejectRequest(reviewingRequest)}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#ef4444',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              >
                ✗ Reject
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DemandNotesDashboard;
