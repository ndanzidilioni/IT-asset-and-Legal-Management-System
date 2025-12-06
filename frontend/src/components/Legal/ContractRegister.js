import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/LegalComponents.css';
import '../../styles/ContractRegister.css';
import '../../styles/PrintReport.css';

const ContractRegister = () => {
  const navigate = useNavigate();
  const [contracts, setContracts] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [years, setYears] = useState([]);
  const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());
  const [filterStatus, setFilterStatus] = useState('all');
  const [loading, setLoading] = useState(true);
  const [deleteConfirm, setDeleteConfirm] = useState(null);
  const [pendingDeletions, setPendingDeletions] = useState([]);
  const [showPendingApprovals, setShowPendingApprovals] = useState(false);
  const [deletionReason, setDeletionReason] = useState('');
  const [reviewComment, setReviewComment] = useState('');
  const [reviewingRequest, setReviewingRequest] = useState(null);
  const [showReport, setShowReport] = useState(false);
  const [reportMonth, setReportMonth] = useState(() => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
  });
  const [reportData, setReportData] = useState(null);
  const [loadingReport, setLoadingReport] = useState(false);

  useEffect(() => {
    loadYears();
  }, []);

  useEffect(() => {
    if (selectedYear) {
      loadContracts();
      loadStatistics();
    }
  }, [selectedYear, filterStatus]);

  useEffect(() => {
    loadPendingDeletions();
  }, []);

  const loadPendingDeletions = async () => {
    try {
      const response = await legalApi.contractDeletionRequests.getPending();
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

  const loadYears = async () => {
    try {
      const response = await legalApi.contracts.getYears();
      if (response.success) {
        setYears(response.data);
        if (response.data.length === 0) {
          setYears([new Date().getFullYear()]);
        }
      }
    } catch (error) {
      console.error('Error loading years:', error);
      setYears([new Date().getFullYear()]);
    }
  };

  const loadContracts = async () => {
    try {
      const response = await legalApi.contracts.getAll(selectedYear, filterStatus !== 'all' ? filterStatus : null);
      console.log('Contracts response:', response);
      console.log('Sample contract data:', response.data?.[0] || response[0]);
      
      // Handle both paginated and non-paginated responses
      if (response.data) {
        const contractsData = response.data.data || response.data;
        setContracts(Array.isArray(contractsData) ? contractsData : []);
      } else if (Array.isArray(response)) {
        setContracts(response);
      }
      setLoading(false);
    } catch (error) {
      console.error('Error loading contracts:', error);
      setLoading(false);
    }
  };

  const loadStatistics = async () => {
    try {
      console.log('📊 ContractRegister loading statistics for year:', selectedYear);
      const response = await legalApi.contracts.getStatistics(selectedYear);
      console.log('📊 ContractRegister statistics response:', response);
      
      if (response.success) {
        // Extract the overview data from the nested structure
        const statsData = response.data.overview || response.data;
        console.log('📊 ContractRegister setting statistics:', statsData);
        setStatistics(statsData);
      }
    } catch (error) {
      console.error('Error loading statistics:', error);
    }
  };

  const getStatusColor = (status) => {
    const colors = {
      'Draft': 'secondary',
      'Under Review': 'warning',
      'Vetted': 'info',
      'Signed': 'primary',
      'Active': 'success',
      'Completed': 'success',
      'Terminated': 'danger'
    };
    return colors[status] || 'default';
  };

  const formatCurrency = (amount) => {
    return 'Tsh ' + new Intl.NumberFormat('en-TZ', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  const handleDeleteRequest = async () => {
    if (!deletionReason.trim()) {
      alert('Please provide a reason for deletion');
      return;
    }

    try {
      const response = await legalApi.contractDeletionRequests.create(deleteConfirm.id, deletionReason);
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
      const response = await legalApi.contractDeletionRequests.approve(request.id, reviewComment);
      if (response.success) {
        alert('✅ ' + response.message);
        setReviewingRequest(null);
        setReviewComment('');
        
        // Reload data and get updated pending list
        const updatedPending = await loadPendingDeletions();
        loadContracts();
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
      const response = await legalApi.contractDeletionRequests.reject(request.id, reviewComment);
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

  const generateMonthlyReport = async () => {
    setLoadingReport(true);
    try {
      const [year, month] = reportMonth.split('-');
      const response = await legalApi.contracts.getAll();
      
      let contractsData = [];
      if (response.data) {
        contractsData = response.data.data || response.data;
      } else if (Array.isArray(response)) {
        contractsData = response;
      }
      contractsData = Array.isArray(contractsData) ? contractsData : [];

      // Filter contracts by signing date month
      const monthContracts = contractsData.filter(c => {
        if (!c.signing_date && !c.created_at) return false;
        const contractDate = new Date(c.signing_date || c.created_at);
        return contractDate.getFullYear() === parseInt(year) && 
               (contractDate.getMonth() + 1) === parseInt(month);
      });

      // Calculate statistics
      const stats = {
        totalContracts: monthContracts.length,
        byStatus: {},
        byCategory: {},
        totalValue: 0,
        avgValue: 0
      };

      monthContracts.forEach(c => {
        // By status
        const status = c.status || 'Unknown';
        stats.byStatus[status] = (stats.byStatus[status] || 0) + 1;

        // By category
        const category = c.description?.match(/Category: ([^\n]+)/)?.[1] || 'Unknown';
        stats.byCategory[category] = (stats.byCategory[category] || 0) + 1;

        // Total value
        if (c.contract_value) {
          stats.totalValue += parseFloat(c.contract_value) || 0;
        }
      });

      stats.avgValue = stats.totalContracts > 0 ? stats.totalValue / stats.totalContracts : 0;

      setReportData({ contracts: monthContracts, stats });
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

  if (loading) {
    return <div className="loading">Loading contracts...</div>;
  }

  return (
    <div className="contract-management">
      <div className="page-header" style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: '24px',
        padding: '20px 0',
        borderBottom: '2px solid #e2e8f0'
      }}>
        {/* Title Section */}
        <div>
          <h1 style={{
            fontSize: '28px',
            fontWeight: '700',
            color: '#1e293b',
            marginBottom: '6px',
            display: 'flex',
            alignItems: 'center',
            gap: '10px'
          }}>
            📋 Contract Register
          </h1>
          <p style={{
            fontSize: '14px',
            color: '#64748b',
            margin: 0
          }}>Manage tenders and contracts</p>
        </div>

        {/* Action Buttons Section */}
        <div style={{display: 'flex', gap: '12px', alignItems: 'center', flexWrap: 'wrap'}}>
          {/* Monthly Report Selector */}
          <div style={{
            display: 'flex',
            gap: '8px',
            alignItems: 'center',
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            padding: '10px 16px',
            borderRadius: '8px',
            boxShadow: '0 2px 4px rgba(102, 126, 234, 0.2)'
          }}>
            <label style={{fontSize: '14px', fontWeight: '600', color: 'white'}}>📊</label>
            <input
              type="month"
              value={reportMonth}
              onChange={(e) => setReportMonth(e.target.value)}
              style={{
                padding: '6px 10px',
                border: '1px solid rgba(255,255,255,0.3)',
                borderRadius: '6px',
                fontSize: '13px',
                background: 'rgba(255,255,255,0.95)',
                cursor: 'pointer'
              }}
            />
            <button
              onClick={generateMonthlyReport}
              disabled={loadingReport}
              style={{
                padding: '6px 14px',
                background: 'rgba(255,255,255,0.2)',
                color: 'white',
                border: '1px solid rgba(255,255,255,0.3)',
                borderRadius: '6px',
                cursor: loadingReport ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '600',
                opacity: loadingReport ? 0.6 : 1,
                transition: 'all 0.2s',
                backdropFilter: 'blur(10px)'
              }}
              onMouseOver={(e) => !loadingReport && (e.target.style.background = 'rgba(255,255,255,0.3)')}
              onMouseOut={(e) => e.target.style.background = 'rgba(255,255,255,0.2)'}
            >
              {loadingReport ? '⏳ Loading...' : '📄 View Report'}
            </button>
          </div>

          {/* Pending Approvals Button */}
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
                transition: 'all 0.2s',
                display: 'flex',
                alignItems: 'center',
                gap: '6px'
              }}
              onMouseOver={(e) => {
                e.target.style.transform = 'translateY(-2px)';
                e.target.style.boxShadow = '0 4px 8px rgba(245, 158, 11, 0.4)';
              }}
              onMouseOut={(e) => {
                e.target.style.transform = 'translateY(0)';
                e.target.style.boxShadow = '0 2px 4px rgba(245, 158, 11, 0.3)';
              }}
            >
              ⏳ Pending Approvals <span style={{
                background: 'rgba(255,255,255,0.3)',
                padding: '2px 8px',
                borderRadius: '12px',
                fontSize: '12px',
                fontWeight: '700'
              }}>{pendingDeletions.length}</span>
            </button>
          )}

          {/* New Contract Button */}
          <button 
            className="btn btn-primary"
            onClick={() => navigate('/legal/contracts/create')}
            style={{
              padding: '10px 20px',
              background: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
              color: 'white',
              border: 'none',
              borderRadius: '8px',
              cursor: 'pointer',
              fontSize: '14px',
              fontWeight: '600',
              boxShadow: '0 2px 4px rgba(59, 130, 246, 0.3)',
              transition: 'all 0.2s',
              display: 'flex',
              alignItems: 'center',
              gap: '6px'
            }}
            onMouseOver={(e) => {
              e.target.style.transform = 'translateY(-2px)';
              e.target.style.boxShadow = '0 4px 8px rgba(59, 130, 246, 0.4)';
            }}
            onMouseOut={(e) => {
              e.target.style.transform = 'translateY(0)';
              e.target.style.boxShadow = '0 2px 4px rgba(59, 130, 246, 0.3)';
            }}
          >
            ➕ New Contract
          </button>
        </div>
      </div>

      {/* Year Selector and Statistics */}
      <div className="year-selector-section">
        <div className="year-selector">
          <label>Select Year:</label>
          <select 
            value={selectedYear} 
            onChange={(e) => setSelectedYear(parseInt(e.target.value))}
            className="year-dropdown"
          >
            {years.map(year => (
              <option key={year} value={year}>{year}</option>
            ))}
          </select>
        </div>

        {statistics && (
          <div className="stats-row">
            <div className="stat-box">
              <h3>{statistics.total_contracts}</h3>
              <p>Total Contracts</p>
            </div>
            <div className="stat-box">
              <h3>{formatCurrency(statistics.total_value)}</h3>
              <p>Total Value</p>
            </div>
            <div className="stat-box">
              <h3>{statistics.active_contracts}</h3>
              <p>Active</p>
            </div>
            <div className="stat-box">
              <h3>{statistics.completed_contracts}</h3>
              <p>Completed</p>
            </div>
          </div>
        )}
      </div>

      {/* Filters */}
      <div className="filters">
        <button 
          className={`filter-btn ${filterStatus === 'all' ? 'active' : ''}`}
          onClick={() => setFilterStatus('all')}
        >
          All ({contracts.length})
        </button>
        <button 
          className={`filter-btn ${filterStatus === 'Active' ? 'active' : ''}`}
          onClick={() => setFilterStatus('Active')}
        >
          Active
        </button>
        <button 
          className={`filter-btn ${filterStatus === 'Under Review' ? 'active' : ''}`}
          onClick={() => setFilterStatus('Under Review')}
        >
          Under Review
        </button>
        <button 
          className={`filter-btn ${filterStatus === 'Completed' ? 'active' : ''}`}
          onClick={() => setFilterStatus('Completed')}
        >
          Completed
        </button>
      </div>

      {/* Contracts Table */}
      <div className="contracts-table-container">
        <table className="contracts-table">
          <thead>
            <tr>
              <th>Tender Number</th>
              <th>Category</th>
              <th>Project Name</th>
              <th>Supplier/Contractor</th>
              <th>Contract Amount</th>
              <th>Date Signed</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {contracts.length === 0 ? (
              <tr>
                <td colSpan="8" style={{textAlign: 'center', padding: '40px'}}>
                  No contracts found for {selectedYear}
                </td>
              </tr>
            ) : (
              contracts.map(contract => {
                // Extract tender number from tender_number field or description fallback
                const tenderNumber = contract.tender_number || contract.description?.match(/Tender Number: ([^\n]+)/)?.[1] || contract.contract_number;
                const category = contract.description?.match(/Category: ([^\n]+)/)?.[1] || '-';
                
                return (
                <tr key={contract.id}>
                  <td><strong>{tenderNumber}</strong></td>
                  <td>
                    <span className={`badge badge-${category === 'PMU' ? 'primary' : 'info'}`}>
                      {category}
                    </span>
                  </td>
                  <td>{contract.title || '-'}</td>
                  <td>{contract.client_name || '-'}</td>
                  <td>{contract.contract_value ? formatCurrency(contract.contract_value) : '-'}</td>
                  <td>{contract.start_date || '-'}</td>
                  <td>
                    <span className={`badge badge-${getStatusColor(contract.status)}`}>
                      {contract.status}
                    </span>
                  </td>
                  <td>
                    <div className="action-buttons">
                      <button 
                        onClick={() => navigate(`/legal/contracts/view/${contract.id}`)}
                        className="btn-sm btn-info"
                        title="View"
                      >
                        👁️
                      </button>
                      <button 
                        onClick={() => navigate(`/legal/contracts/edit/${contract.id}`)}
                        className="btn-sm btn-success"
                        title="Edit"
                      >
                        ✏️
                      </button>
                      <button 
                        onClick={() => setDeleteConfirm(contract)}
                        className="btn-sm btn-danger"
                        title="Delete"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {/* Monthly Report Modal */}
      {showReport && reportData && (
        <div className="modal-overlay" onClick={() => setShowReport(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{maxWidth: '1000px', maxHeight: '90vh', overflow: 'auto'}}>
            <div className="modal-header" style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '2px solid #e5e7eb', paddingBottom: '15px'}}>
              <div>
                <h2 style={{margin: 0}}>📊 Monthly Contract Report</h2>
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
                    <div style={{fontSize: '28px', fontWeight: '700', color: '#1e40af'}}>{reportData.stats.totalContracts}</div>
                    <div style={{fontSize: '14px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.05em'}}>Total Contracts</div>
                  </div>
                  <div style={{background: '#dcfce7', padding: '15px', borderRadius: '8px', borderLeft: '4px solid #10b981'}}>
                    <div style={{fontSize: '20px', fontWeight: '700', color: '#065f46'}}>{formatCurrency(reportData.stats.totalValue)}</div>
                    <div style={{fontSize: '14px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.05em'}}>Total Contract Value</div>
                  </div>
                  <div style={{background: '#fef3c7', padding: '15px', borderRadius: '8px', borderLeft: '4px solid #f59e0b'}}>
                    <div style={{fontSize: '20px', fontWeight: '700', color: '#92400e'}}>{formatCurrency(reportData.stats.avgValue)}</div>
                    <div style={{fontSize: '14px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.05em'}}>Average Value</div>
                  </div>
                </div>
              </div>

              {/* By Status */}
              <div style={{marginBottom: '30px'}}>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>Contracts by Status</h3>
                <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '10px'}}>
                  {Object.entries(reportData.stats.byStatus).map(([status, count]) => (
                    <div key={status} style={{background: '#f8fafc', padding: '10px', borderRadius: '6px', textAlign: 'center'}}>
                      <div style={{fontSize: '24px', fontWeight: '600', color: '#1e293b'}}>{count}</div>
                      <div style={{fontSize: '13px', color: '#64748b'}}>{status}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* By Category */}
              <div style={{marginBottom: '30px'}}>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>Contracts by Category</h3>
                <div style={{display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '10px'}}>
                  {Object.entries(reportData.stats.byCategory).map(([category, count]) => (
                    <div key={category} style={{background: '#f8fafc', padding: '10px', borderRadius: '6px', textAlign: 'center'}}>
                      <div style={{fontSize: '24px', fontWeight: '600', color: '#1e293b'}}>{count}</div>
                      <div style={{fontSize: '13px', color: '#64748b'}}>{category}</div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Contract List */}
              <div>
                <h3 style={{marginBottom: '15px', borderBottom: '2px solid #3b82f6', paddingBottom: '8px'}}>All Contracts ({reportData.contracts.length})</h3>
                <table style={{width: '100%', borderCollapse: 'collapse', fontSize: '14px'}}>
                  <thead>
                    <tr style={{background: '#f8fafc', borderBottom: '2px solid #e5e7eb'}}>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Tender No</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Category</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Project</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Contractor</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Value</th>
                      <th style={{padding: '12px', textAlign: 'left', fontWeight: '600', color: '#475569'}}>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {reportData.contracts.map((c) => {
                      const tenderNumber = c.tender_number || c.description?.match(/Tender Number: ([^\n]+)/)?.[1] || c.contract_number;
                      const category = c.description?.match(/Category: ([^\n]+)/)?.[1] || '-';
                      return (
                        <tr key={c.id} style={{borderBottom: '1px solid #f1f5f9'}}>
                          <td style={{padding: '10px'}}>{tenderNumber}</td>
                          <td style={{padding: '10px'}}>
                            <span style={{
                              padding: '4px 8px',
                              borderRadius: '4px',
                              fontSize: '12px',
                              background: category === 'PMU' ? '#dbeafe' : '#e0e7ff',
                              color: category === 'PMU' ? '#1e40af' : '#4338ca'
                            }}>
                              {category}
                            </span>
                          </td>
                          <td style={{padding: '10px'}}>{c.title || '-'}</td>
                          <td style={{padding: '10px'}}>{c.client_name || '-'}</td>
                          <td style={{padding: '10px'}}>{c.contract_value ? formatCurrency(c.contract_value) : '-'}</td>
                          <td style={{padding: '10px'}}>
                            <span style={{
                              padding: '4px 8px',
                              borderRadius: '4px',
                              fontSize: '12px',
                              background: c.status === 'Active' ? '#dcfce7' : '#f1f5f9',
                              color: c.status === 'Active' ? '#065f46' : '#64748b'
                            }}>
                              {c.status}
                            </span>
                          </td>
                        </tr>
                      );
                    })}
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

      {/* Delete Request Modal */}
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
            width: '90%',
            boxShadow: '0 4px 20px rgba(0,0,0,0.2)'
          }}>
            <h3 style={{marginTop: 0, color: '#dc2626'}}>⚠️ Request Contract Deletion</h3>
            <p>Contract: <strong>{deleteConfirm.contract_number || deleteConfirm.title}</strong></p>
            <p style={{color: '#64748b', fontSize: '14px', marginTop: '10px'}}>
              This deletion requires approval from another lawyer. Please provide a reason for deletion.
            </p>
            <textarea
              value={deletionReason}
              onChange={(e) => setDeletionReason(e.target.value)}
              placeholder="Reason for deletion request..."
              rows="4"
              style={{
                width: '100%',
                padding: '10px',
                border: '1px solid #e2e8f0',
                borderRadius: '6px',
                fontSize: '14px',
                marginTop: '15px',
                resize: 'vertical'
              }}
              required
            />
            <div style={{display: 'flex', gap: '10px', marginTop: '20px'}}>
              <button 
                onClick={() => {
                  setDeleteConfirm(null);
                  setDeletionReason('');
                }}
                className="btn btn-secondary"
                style={{flex: 1}}
              >
                Cancel
              </button>
              <button 
                onClick={handleDeleteRequest}
                className="btn btn-danger"
                style={{flex: 1}}
                disabled={!deletionReason.trim()}
              >
                📤 Submit Request
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
          zIndex: 1000,
          overflow: 'auto',
          padding: '20px'
        }}>
          <div style={{
            background: 'white',
            padding: '30px',
            borderRadius: '12px',
            maxWidth: '800px',
            width: '100%',
            maxHeight: '80vh',
            overflow: 'auto',
            boxShadow: '0 4px 20px rgba(0,0,0,0.2)'
          }}>
            <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px'}}>
              <h3 style={{margin: 0}}>⏳ Pending Deletion Approvals ({pendingDeletions.length})</h3>
              <button 
                onClick={() => setShowPendingApprovals(false)}
                style={{background: 'none', border: 'none', fontSize: '24px', cursor: 'pointer', color: '#64748b'}}
              >
                ×
              </button>
            </div>
            
            {pendingDeletions.length === 0 ? (
              <p style={{textAlign: 'center', color: '#64748b', padding: '40px'}}>No pending approvals</p>
            ) : (
              pendingDeletions.map(request => (
                <div key={request.id} style={{
                  border: '1px solid #e2e8f0',
                  borderRadius: '8px',
                  padding: '20px',
                  marginBottom: '15px',
                  background: '#f8fafc'
                }}>
                  <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'start', marginBottom: '12px'}}>
                    <div>
                      <h4 style={{margin: '0 0 5px 0'}}>{request.contract?.title || 'Contract'}</h4>
                      <p style={{margin: '0', fontSize: '13px', color: '#64748b'}}>
                        Contract No: {request.contract?.contract_number || 'N/A'}
                      </p>
                    </div>
                    <span style={{
                      padding: '4px 12px',
                      background: '#fef3c7',
                      color: '#92400e',
                      borderRadius: '12px',
                      fontSize: '12px',
                      fontWeight: '500'
                    }}>
                      Pending
                    </span>
                  </div>
                  
                  <div style={{marginBottom: '12px'}}>
                    <p style={{margin: '5px 0', fontSize: '14px'}}>
                      <strong>Requested by:</strong> {request.requester_name}
                    </p>
                    <p style={{margin: '5px 0', fontSize: '14px'}}>
                      <strong>Date:</strong> {new Date(request.created_at).toLocaleString()}
                    </p>
                    {request.reason && (
                      <div style={{marginTop: '10px'}}>
                        <strong style={{fontSize: '14px'}}>Reason:</strong>
                        <p style={{margin: '5px 0', padding: '10px', background: 'white', borderRadius: '6px', fontSize: '14px'}}>
                          {request.reason}
                        </p>
                      </div>
                    )}
                  </div>
                  
                  <div style={{display: 'flex', gap: '10px'}}>
                    <button
                      onClick={() => setReviewingRequest(request)}
                      style={{
                        flex: 1,
                        padding: '10px',
                        background: '#10b981',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontSize: '14px',
                        fontWeight: '500'
                      }}
                    >
                      ✅ Approve
                    </button>
                    <button
                      onClick={() => {
                        setReviewingRequest(request);
                        setReviewComment('');
                      }}
                      style={{
                        flex: 1,
                        padding: '10px',
                        background: '#ef4444',
                        color: 'white',
                        border: 'none',
                        borderRadius: '6px',
                        cursor: 'pointer',
                        fontSize: '14px',
                        fontWeight: '500'
                      }}
                    >
                      ❌ Reject
                    </button>
                  </div>
                </div>
              ))
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
            maxWidth: '500px',
            width: '90%',
            boxShadow: '0 4px 20px rgba(0,0,0,0.2)'
          }}>
            <h3 style={{marginTop: 0}}>Review Deletion Request</h3>
            <p><strong>Contract:</strong> {reviewingRequest.contract?.title}</p>
            <p><strong>Requested by:</strong> {reviewingRequest.requester_name}</p>
            
            <textarea
              value={reviewComment}
              onChange={(e) => setReviewComment(e.target.value)}
              placeholder="Add a comment (optional for approval, required for rejection)..."
              rows="3"
              style={{
                width: '100%',
                padding: '10px',
                border: '1px solid #e2e8f0',
                borderRadius: '6px',
                fontSize: '14px',
                marginTop: '15px',
                resize: 'vertical'
              }}
            />
            
            <div style={{display: 'flex', gap: '10px', marginTop: '20px'}}>
              <button 
                onClick={() => {
                  setReviewingRequest(null);
                  setReviewComment('');
                }}
                className="btn btn-secondary"
                style={{flex: 1}}
              >
                Cancel
              </button>
              <button 
                onClick={() => handleApproveRequest(reviewingRequest)}
                className="btn btn-success"
                style={{flex: 1, background: '#10b981'}}
              >
                ✅ Approve & Delete
              </button>
              <button 
                onClick={() => handleRejectRequest(reviewingRequest)}
                className="btn btn-danger"
                style={{flex: 1}}
                disabled={!reviewComment.trim()}
              >
                ❌ Reject
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ContractRegister;
