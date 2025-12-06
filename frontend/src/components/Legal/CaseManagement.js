import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import authService from '../../services/authService';
import '../../styles/LegalComponents.css';
import '../../styles/PrintReport.css';

const CaseManagement = () => {
  const navigate = useNavigate();
  const [cases, setCases] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [selectedCase, setSelectedCase] = useState(null);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');
  const [showReport, setShowReport] = useState(false);
  const [reportMonth, setReportMonth] = useState(() => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
  });
  const [reportData, setReportData] = useState(null);
  const [loadingReport, setLoadingReport] = useState(false);
  const [pendingDeletions, setPendingDeletions] = useState([]);
  const [showPendingApprovals, setShowPendingApprovals] = useState(false);
  const [deleteConfirm, setDeleteConfirm] = useState(null);
  const [deletionReason, setDeletionReason] = useState('');
  const [reviewComment, setReviewComment] = useState('');
  const [reviewingRequest, setReviewingRequest] = useState(null);

  useEffect(() => {
    loadCases();
    loadStatistics();
    loadPendingDeletions();
  }, []);

  const loadCases = async () => {
    try {
      console.log('🔄 Starting to load cases...');
      const response = await legalApi.cases.getAll();
      console.log('📦 Cases API raw response:', response);
      console.log('📦 Response type:', typeof response);
      console.log('📦 Response.data type:', typeof response.data);
      console.log('📦 Response.data:', response.data);
      
      // Handle both paginated and non-paginated responses
      let casesData = [];
      
      // Check if response.data is already an array
      if (Array.isArray(response.data)) {
        console.log('✅ response.data is array, using directly');
        casesData = response.data;
      } 
      // Check if it's Laravel pagination format: { data: [...], current_page, ... }
      else if (response.data && Array.isArray(response.data.data)) {
        console.log('✅ Laravel pagination format detected');
        casesData = response.data.data;
      } 
      // Check if response is an array directly
      else if (Array.isArray(response)) {
        console.log('✅ response itself is array');
        casesData = response;
      }
      // Check if it's wrapped: { success: true, data: [...] } and data is array
      else if (response.data) {
        console.log('⚠️ response.data exists but not array, checking further...');
        console.log('📋 response.data content:', response.data);
        if (Array.isArray(response.data)) {
          casesData = response.data;
        } else {
          console.warn('⚠️ Unexpected response structure');
        }
      }
      
      console.log('✅ Processed cases data:', casesData);
      console.log('✅ Number of cases:', casesData.length);
      
      if (casesData.length === 0) {
        console.warn('⚠️ No cases found in response!');
      }
      
      setCases(casesData);
      setLoading(false);
    } catch (error) {
      console.error('❌ Error loading cases:', error);
      console.error('❌ Error stack:', error.stack);
      setCases([]);
      setLoading(false);
    }
  };

  const loadStatistics = async () => {
    try {
      const response = await legalApi.cases.getStatistics();
      console.log('Statistics API response:', response);
      
      // Calculate urgent cases from the statistics
      const stats = response.data;
      if (stats && !stats.urgent_cases) {
        // If urgent_cases is not provided, calculate from cases_by_priority or cases_by_status
        const urgentFromPriority = stats.cases_by_priority?.find(p => p.priority === 'Urgent')?.count || 0;
        const urgentFromStatus = stats.cases_by_status?.find(s => s.status === 'Urgent')?.count || 0;
        stats.urgent_cases = Math.max(urgentFromPriority, urgentFromStatus);
        stats.upcoming_hearings = stats.upcoming_hearings || 0;
      }
      
      setStatistics(stats);
    } catch (error) {
      console.error('Error loading statistics:', error);
    }
  };

  const loadPendingDeletions = async () => {
    try {
      const response = await legalApi.caseDeletionRequests.getPending();
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
      const response = await legalApi.caseDeletionRequests.create(deleteConfirm.id, deletionReason);
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
      const response = await legalApi.caseDeletionRequests.approve(request.id, reviewComment);
      if (response.success) {
        alert('✅ ' + response.message);
        setReviewingRequest(null);
        setReviewComment('');
        
        // Reload data and get updated pending list
        const updatedPending = await loadPendingDeletions();
        loadCases();
        loadStatistics();
        
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
      const response = await legalApi.caseDeletionRequests.reject(request.id, reviewComment);
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

  // Check user role - use authService directly
  const [userRole, setUserRole] = useState(null);
  
  useEffect(() => {
    // Try both 'user' and 'userInfo' for backward compatibility
    let user = authService.getCurrentUser();
    
    if (!user) {
      // Try 'userInfo' as fallback
      const userInfoStr = localStorage.getItem('userInfo');
      if (userInfoStr) {
        try {
          user = JSON.parse(userInfoStr);
        } catch (e) {
          console.error('Failed to parse userInfo:', e);
        }
      }
    }
    
    if (user && user.role) {
      console.log('User from authService:', user);
      setUserRole(user.role);
    } else {
      console.log('No user found in authService or localStorage');
      console.log('Available localStorage keys:', Object.keys(localStorage));
    }
  }, []);

  const canCreateCase = userRole && (
    userRole === 'admin' || 
    userRole === 'lawyer' || 
    userRole === 'developer'
  );
  
  console.log('User role:', userRole);
  console.log('Can create case:', canCreateCase);


  const getStatusColor = (status) => {
    const colors = {
      'Active': 'success',
      'Urgent': 'danger',
      'Pending': 'warning',
      'Completed': 'info',
      'Closed': 'secondary'
    };
    return colors[status] || 'default';
  };

  const getPriorityColor = (priority) => {
    const colors = {
      'Urgent': 'danger',
      'High': 'warning',
      'Medium': 'info',
      'Low': 'success'
    };
    return colors[priority] || 'default';
  };

  const generateMonthlyReport = async () => {
    setLoadingReport(true);
    try {
      const [year, month] = reportMonth.split('-');
      const response = await legalApi.cases.getAll();
      
      let casesData = [];
      if (Array.isArray(response.data)) {
        casesData = response.data;
      } else if (response.data && Array.isArray(response.data.data)) {
        casesData = response.data.data;
      }

      // Filter cases by month
      const monthCases = casesData.filter(c => {
        if (!c.created_at && !c.filing_date) return false;
        const caseDate = new Date(c.created_at || c.filing_date);
        return caseDate.getFullYear() === parseInt(year) && 
               (caseDate.getMonth() + 1) === parseInt(month);
      });

      // Calculate statistics
      const stats = {
        totalCases: monthCases.length,
        byStatus: {},
        byType: {},
        byPriority: {},
        totalAmount: 0
      };

      monthCases.forEach(c => {
        // By status
        const status = c.status || c.current_status || 'Unknown';
        stats.byStatus[status] = (stats.byStatus[status] || 0) + 1;

        // By type
        const type = c.case_type || 'Unknown';
        stats.byType[type] = (stats.byType[type] || 0) + 1;

        // By priority
        const priority = c.priority || 'Unknown';
        stats.byPriority[priority] = (stats.byPriority[priority] || 0) + 1;

        // Total amount
        if (c.amount_in_claim) {
          stats.totalAmount += parseFloat(c.amount_in_claim) || 0;
        }
      });

      setReportData({ cases: monthCases, stats });
      setShowReport(true);
    } catch (error) {
      console.error('Error generating report:', error);
      alert('Failed to generate report');
    } finally {
      setLoadingReport(false);
    }
  };

  const printReport = () => {
    window.print();
  };

  const formatMonthYear = (monthStr) => {
    const [year, month] = monthStr.split('-');
    const date = new Date(year, month - 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  };

  // Ensure cases is an array before filtering
  const casesArray = Array.isArray(cases) ? cases : [];
  const filteredCases = filter === 'all' ? casesArray : casesArray.filter(c => c.status === filter);

  if (loading) {
    return <div className="loading">Loading cases...</div>;
  }

  return (
    <div className="case-management" style={{padding: '30px'}}>
      <div className="page-header" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '30px'}}>
        <h1 style={{margin: 0}}>Case Management</h1>
        <div style={{display: 'flex', gap: '12px', alignItems: 'center'}}>
          {/* Monthly Report Controls */}
          <div style={{display: 'flex', gap: '8px', alignItems: 'center', background: '#f8fafc', padding: '8px 12px', borderRadius: '8px'}}>
            <label style={{fontSize: '14px', fontWeight: '500'}}>📊 Monthly Report:</label>
            <input
              type="month"
              value={reportMonth}
              onChange={(e) => setReportMonth(e.target.value)}
              style={{
                padding: '6px 10px',
                border: '1px solid #e2e8f0',
                borderRadius: '6px',
                fontSize: '14px'
              }}
            />
            <button
              onClick={generateMonthlyReport}
              disabled={loadingReport}
              style={{
                padding: '8px 16px',
                background: '#8b5cf6',
                color: 'white',
                border: 'none',
                borderRadius: '6px',
                cursor: loadingReport ? 'not-allowed' : 'pointer',
                fontSize: '14px',
                fontWeight: '500',
                opacity: loadingReport ? 0.6 : 1
              }}
            >
              {loadingReport ? '⏳ Loading...' : '📄 View Report'}
            </button>
          </div>
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
          {canCreateCase && (
            <button 
              className="btn btn-primary" 
              onClick={() => navigate('/legal/cases/create')}
              style={{
                padding: '12px 24px',
                fontSize: '16px',
                fontWeight: '600',
                backgroundColor: '#3b82f6',
                color: 'white',
                border: 'none',
                borderRadius: '8px',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                gap: '8px'
              }}
            >
              ➕ Create New Case
            </button>
          )}
        </div>
      </div>

      {/* Statistics */}
      {statistics && (
        <div className="stats-row">
          <div className="stat-box">
            <h3>{statistics.total_cases}</h3>
            <p>Total Cases</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.active_cases}</h3>
            <p>Active Cases</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.urgent_cases}</h3>
            <p>Urgent Cases</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.upcoming_hearings}</h3>
            <p>Upcoming Hearings</p>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="filters">
        <button 
          className={`filter-btn ${filter === 'all' ? 'active' : ''}`}
          onClick={() => setFilter('all')}
        >
          All Cases ({casesArray.length})
        </button>
        <button 
          className={`filter-btn ${filter === 'Active' ? 'active' : ''}`}
          onClick={() => setFilter('Active')}
        >
          Active
        </button>
        <button 
          className={`filter-btn ${filter === 'Urgent' ? 'active' : ''}`}
          onClick={() => setFilter('Urgent')}
        >
          Urgent
        </button>
        <button 
          className={`filter-btn ${filter === 'Pending' ? 'active' : ''}`}
          onClick={() => setFilter('Pending')}
        >
          Pending
        </button>
      </div>

      {/* Cases List */}
      <div className="cases-list">
        {filteredCases.length === 0 ? (
          <div style={{
            padding: '60px 20px',
            textAlign: 'center',
            background: '#f9fafb',
            borderRadius: '8px',
            border: '2px dashed #d1d5db'
          }}>
            <h3 style={{color: '#6b7280', marginBottom: '10px'}}>No Cases Found</h3>
            <p style={{color: '#9ca3af', marginBottom: '20px'}}>
              {filter === 'all' 
                ? 'There are no cases in the system yet.' 
                : `No ${filter} cases found.`}
            </p>
            {canCreateCase && filter === 'all' && (
              <button 
                className="btn btn-primary" 
                onClick={() => navigate('/legal/cases/create')}
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
                ➕ Create Your First Case
              </button>
            )}
          </div>
        ) : (
          filteredCases.map(c => (
          <div key={c.id} className="case-card">
            <div className="case-header">
              <h3>{c.case_number || c.title}</h3>
              <div style={{display: 'flex', gap: '10px', alignItems: 'center'}}>
                <span className={`badge badge-${getStatusColor(c.status || c.current_status)}`}>
                  {c.status || c.current_status}
                </span>
                {canCreateCase && (
                  <>
                    <button 
                      onClick={() => navigate(`/legal/cases/edit/${c.id}`)}
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
                      onClick={() => setDeleteConfirm(c)}
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
                  </>
                )}
              </div>
            </div>
            {c.parties && <p><strong>Parties:</strong> {c.parties}</p>}
            <p><strong>Type:</strong> {c.case_type || c.nature_of_case}</p>
            {c.amount_in_claim && (
              <p><strong>Amount:</strong> TZS {parseFloat(c.amount_in_claim).toLocaleString()}</p>
            )}
            <p><strong>Filed:</strong> {c.filed_date || c.date_filed || 'N/A'}</p>
            {c.assigned_lawyer && <p><strong>Lawyer:</strong> {typeof c.assigned_lawyer === 'object' ? (c.assigned_lawyer.fname + ' ' + c.assigned_lawyer.lname) : String(c.assigned_lawyer)}</p>}
            {c.next_hearing_date && <p><strong>Next Hearing:</strong> {c.next_hearing_date}</p>}
            {c.any_appeal && c.any_appeal !== 'No' && (
              <p><strong>Appeal:</strong> <span style={{color: '#f59e0b'}}>{c.any_appeal}</span></p>
            )}
            {c.description && <p className="case-description">{c.description}</p>}
            
            <div style={{display: 'flex', gap: '8px', marginTop: '15px', paddingTop: '15px', borderTop: '1px solid #e5e7eb'}}>
              <button 
                onClick={() => navigate(`/legal/cases/view/${c.id}`)}
                style={{
                  flex: 1,
                  padding: '8px',
                  background: '#6366f1',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  fontSize: '14px'
                }}
              >
                👁️ View
              </button>
              <button 
                onClick={() => navigate(`/legal/cases/${c.id}/proceedings`)}
                style={{
                  flex: 1,
                  padding: '8px',
                  background: '#8b5cf6',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  fontSize: '14px'
                }}
              >
                ⚖️ Proceedings
              </button>
            </div>
          </div>
        ))
        )}
      </div>

      {/* Monthly Report Modal */}
      {showReport && reportData && (
        <div className="modal-overlay" onClick={() => setShowReport(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{maxWidth: '1000px', maxHeight: '90vh', overflow: 'auto'}}>
            <div className="modal-header" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '2px solid #e5e7eb', paddingBottom: '15px'}}>
              <div>
                <h2 style={{margin: 0}}>📊 Monthly Case Report</h2>
                <p style={{margin: '5px 0 0 0', color: '#6b7280'}}>{formatMonthYear(reportMonth)}</p>
              </div>
              <div style={{display: 'flex', gap: '10px'}}>
                <button 
                  onClick={printReport}
                  style={{
                    padding: '8px 16px',
                    background: '#10b981',
                    color: 'white',
                    border: 'none',
                    borderRadius: '6px',
                    cursor: 'pointer',
                    fontSize: '14px'
                  }}
                >
                  🖨️ Print
                </button>
                <button className="close-btn" onClick={() => setShowReport(false)}>×</button>
              </div>
            </div>
            
            <div className="modal-body" id="monthly-report" style={{padding: '20px'}}>
              {/* Summary Statistics */}
              <div style={{marginBottom: '30px'}}>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>Summary</h3>
                <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '15px'}}>
                  <div style={{background: '#dbeafe', padding: '15px', borderRadius: '8px', borderLeft: '4px solid #3b82f6'}}>
                    <div style={{fontSize: '28px', fontWeight: '700', color: '#1e40af'}}>{reportData.stats.totalCases}</div>
                    <div style={{fontSize: '14px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.05em'}}>Total Cases</div>
                  </div>
                  <div style={{background: '#dcfce7', padding: '15px', borderRadius: '8px', borderLeft: '4px solid #10b981'}}>
                    <div style={{fontSize: '20px', fontWeight: '700', color: '#065f46'}}>TZS {reportData.stats.totalAmount.toLocaleString()}</div>
                    <div style={{fontSize: '14px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.05em'}}>Total Amount in Claim</div>
                  </div>
                </div>
              </div>

              {/* By Status */}
              <div style={{marginBottom: '30px'}}>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>Cases by Status</h3>
                <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '10px'}}>
                  {Object.entries(reportData.stats.byStatus).map(([status, count]) => (
                    <div key={status} style={{background: '#f8fafc', padding: '10px', borderRadius: '6px', textAlign: 'center'}}>
                      <div style={{fontSize: '24px', fontWeight: '600', color: '#1e293b'}}>{count}</div>
                      <div style={{fontSize: '13px', color: '#64748b'}}>{status}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* By Type */}
              <div style={{marginBottom: '30px'}}>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>Cases by Type</h3>
                <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '10px'}}>
                  {Object.entries(reportData.stats.byType).map(([type, count]) => (
                    <div key={type} style={{background: '#f8fafc', padding: '10px', borderRadius: '6px', textAlign: 'center'}}>
                      <div style={{fontSize: '24px', fontWeight: '600', color: '#1e293b'}}>{count}</div>
                      <div style={{fontSize: '13px', color: '#64748b'}}>{type}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Case List */}
              <div>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>All Cases ({reportData.cases.length})</h3>
                <table style={{width: '100%', borderCollapse: 'collapse', fontSize: '14px'}}>
                  <thead>
                    <tr style={{background: '#f8fafc', borderBottom: '2px solid #e5e7eb'}}>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Case No</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Type</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Status</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Amount (TZS)</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Filed Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    {reportData.cases.map((c, idx) => (
                      <tr key={c.id} style={{borderBottom: '1px solid #f1f5f9'}}>
                        <td style={{padding: '10px'}}>{c.case_number || 'N/A'}</td>
                        <td style={{padding: '10px'}}>{c.case_type || 'N/A'}</td>
                        <td style={{padding: '10px'}}>
                          <span style={{
                            padding: '4px 8px',
                            borderRadius: '4px',
                            fontSize: '12px',
                            background: c.status === 'Active' ? '#dcfce7' : '#f1f5f9',
                            color: c.status === 'Active' ? '#065f46' : '#64748b'
                          }}>
                            {c.status || c.current_status || 'N/A'}
                          </span>
                        </td>
                        <td style={{padding: '10px'}}>{c.amount_in_claim ? parseFloat(c.amount_in_claim).toLocaleString() : '-'}</td>
                        <td style={{padding: '10px'}}>{c.filing_date || c.filed_date || new Date(c.created_at).toLocaleDateString()}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <div style={{marginTop: '30px', paddingTop: '20px', borderTop: '2px solid #e5e7eb', textAlign: 'center', color: '#9ca3af', fontSize: '12px'}}>
                <p>Generated on {new Date().toLocaleString()}</p>
                <p>Legal Management System - Ministry of Infrastructure</p>
              </div>
            </div>
          </div>
        </div>
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
            <h3 style={{marginTop: 0}}>🗑️ Request Case Deletion</h3>
            <p>Case: <strong>{deleteConfirm.case_number || deleteConfirm.title}</strong></p>
            <p style={{color: '#f59e0b', fontSize: '14px'}}>
              ⚠️ This deletion requires approval from another lawyer before the case is permanently deleted.
            </p>
            <div style={{marginBottom: '20px'}}>
              <label style={{display: 'block', marginBottom: '8px', fontWeight: '500'}}>
                Reason for Deletion: <span style={{color: 'red'}}>*</span>
              </label>
              <textarea
                value={deletionReason}
                onChange={(e) => setDeletionReason(e.target.value)}
                placeholder="Please provide a reason for deleting this case..."
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
                className="btn btn-danger"
                style={{flex: 1, background: '#ef4444'}}
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
              <h3 style={{margin: 0}}>⏳ Pending Case Deletion Approvals ({pendingDeletions.length})</h3>
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
                        <strong>Case:</strong> {request.case?.case_number || request.case?.title || 'N/A'}
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
              <p><strong>Case:</strong> {reviewingRequest.case?.case_number || reviewingRequest.case?.title}</p>
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
                className="btn btn-success"
                style={{flex: 1, background: '#10b981'}}
              >
                ✓ Approve & Delete
              </button>
              <button 
                onClick={() => handleRejectRequest(reviewingRequest)}
                className="btn btn-danger"
                style={{flex: 1, background: '#ef4444'}}
              >
                ✗ Reject
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Case Detail Modal */}
      {selectedCase && (
        <div className="modal-overlay" onClick={() => setSelectedCase(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>{selectedCase.case_number}</h2>
              <button className="close-btn" onClick={() => setSelectedCase(null)}>×</button>
            </div>
            <div className="modal-body">
              <h3>{selectedCase.title}</h3>
              <div className="detail-grid">
                <div><strong>Client:</strong> {selectedCase.client_name}</div>
                <div><strong>Type:</strong> {selectedCase.case_type}</div>
                <div><strong>Status:</strong> {selectedCase.status}</div>
                <div><strong>Priority:</strong> {selectedCase.priority}</div>
                <div><strong>Filed:</strong> {selectedCase.filed_date}</div>
                <div><strong>Lawyer:</strong> {typeof selectedCase.assigned_lawyer === 'object' ? (selectedCase.assigned_lawyer.fname + ' ' + selectedCase.assigned_lawyer.lname) : String(selectedCase.assigned_lawyer || 'Not assigned')}</div>
                <div><strong>Next Hearing:</strong> {selectedCase.next_hearing}</div>
              </div>
              <div className="description-section">
                <strong>Description:</strong>
                <p>{selectedCase.description}</p>
              </div>
            </div>
            <div className="modal-footer">
              <button className="btn btn-secondary" onClick={() => setSelectedCase(null)}>Close</button>
              <button className="btn btn-primary">Edit Case</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default CaseManagement;
