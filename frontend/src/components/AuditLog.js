import React, { useState, useEffect } from 'react';
import API from '../services/api';
import './AuditLog.css';

const AuditLog = () => {
  const [logs, setLogs] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  
  // Filter states
  const [filters, setFilters] = useState({
    user_id: '',
    action: '',
    start_date: '',
    end_date: '',
    search: '',
    per_page: 50
  });
  
  // Pagination
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    total: 0
  });
  
  const [activeView, setActiveView] = useState('logs'); // 'logs' or 'statistics'

  useEffect(() => {
    if (activeView === 'logs') {
      fetchLogs();
    } else {
      fetchStatistics();
    }
  }, [activeView, filters, pagination.current_page]);

  const fetchLogs = async () => {
    try {
      setLoading(true);
      setError('');
      
      const params = {
        ...filters,
        page: pagination.current_page
      };
      
      // Remove empty filters
      Object.keys(params).forEach(key => {
        if (!params[key]) delete params[key];
      });
      
      const response = await API.get('/audit-logs', { params });
      
      if (response.data.success) {
        setLogs(response.data.data);
        setPagination(response.data.pagination);
      }
    } catch (err) {
      setError('Failed to fetch audit logs: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      setError('');
      
      const params = {
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined
      };
      
      const response = await API.get('/audit-logs/statistics', { params });
      
      if (response.data.success) {
        setStatistics(response.data.data);
      }
    } catch (err) {
      setError('Failed to fetch statistics: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSearch = () => {
    setPagination(prev => ({ ...prev, current_page: 1 }));
    fetchLogs();
  };

  const handleClearFilters = () => {
    setFilters({
      user_id: '',
      action: '',
      start_date: '',
      end_date: '',
      search: '',
      per_page: 50
    });
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  const handleExport = async () => {
    try {
      const params = { ...filters };
      Object.keys(params).forEach(key => {
        if (!params[key]) delete params[key];
      });
      
      const response = await API.get('/audit-logs/export/csv', {
        params,
        responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `audit_logs_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err) {
      setError('Failed to export: ' + (err.response?.data?.message || err.message));
    }
  };

  const getActionBadgeClass = (action) => {
    if (action.includes('login')) return 'badge-success';
    if (action.includes('logout')) return 'badge-info';
    if (action.includes('create')) return 'badge-primary';
    if (action.includes('update') || action.includes('edit')) return 'badge-warning';
    if (action.includes('delete')) return 'badge-danger';
    return 'badge-default';
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
  };

  return (
    <div className="audit-log-container">
      <div className="audit-header">
        <h1>🔍 Audit Trail</h1>
        <p>Track user activities and system events</p>
      </div>

      {error && <div className="error-message">{error}</div>}

      {/* View Toggle */}
      <div className="view-toggle">
        <button
          className={`toggle-btn ${activeView === 'logs' ? 'active' : ''}`}
          onClick={() => setActiveView('logs')}
        >
          📋 Activity Logs
        </button>
        <button
          className={`toggle-btn ${activeView === 'statistics' ? 'active' : ''}`}
          onClick={() => setActiveView('statistics')}
        >
          📊 Statistics
        </button>
      </div>

      {activeView === 'logs' ? (
        <>
          {/* Filters */}
          <div className="filters-section">
            <div className="filters-row">
              <input
                type="text"
                name="search"
                placeholder="Search username, action, description..."
                value={filters.search}
                onChange={handleFilterChange}
                className="filter-input search-input"
              />
              
              <select
                name="action"
                value={filters.action}
                onChange={handleFilterChange}
                className="filter-select"
              >
                <option value="">All Actions</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="view">View</option>
                <option value="export">Export</option>
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
              
              <button onClick={handleSearch} className="btn-primary">
                🔍 Search
              </button>
              
              <button onClick={handleClearFilters} className="btn-secondary">
                ✖ Clear
              </button>
              
              <button onClick={handleExport} className="btn-success">
                📥 Export CSV
              </button>
            </div>
          </div>

          {/* Logs Table */}
          {loading ? (
            <div className="loading-spinner">Loading audit logs...</div>
          ) : (
            <>
              <div className="audit-table-container">
                <table className="audit-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Timestamp</th>
                      <th>Username</th>
                      <th>Action</th>
                      <th>Description</th>
                      <th>IP Address</th>
                      <th>User Agent</th>
                    </tr>
                  </thead>
                  <tbody>
                    {logs.length === 0 ? (
                      <tr>
                        <td colSpan="7" className="no-data">No audit logs found</td>
                      </tr>
                    ) : (
                      logs.map((log) => (
                        <tr key={log.id}>
                          <td>{log.id}</td>
                          <td className="timestamp">{formatDate(log.created_at)}</td>
                          <td className="username">
                            <strong>{log.username || 'N/A'}</strong>
                          </td>
                          <td>
                            <span className={`action-badge ${getActionBadgeClass(log.action)}`}>
                              {log.action}
                            </span>
                          </td>
                          <td className="description">{log.description || '-'}</td>
                          <td className="ip-address">{log.ip_address}</td>
                          <td className="user-agent" title={log.user_agent}>
                            {log.user_agent?.substring(0, 50)}...
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
                    Page {pagination.current_page} of {pagination.last_page} 
                    ({pagination.total} total records)
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
        </>
      ) : (
        // Statistics View
        <div className="statistics-section">
          {loading ? (
            <div className="loading-spinner">Loading statistics...</div>
          ) : statistics ? (
            <>
              <div className="stats-grid">
                <div className="stat-card">
                  <div className="stat-icon">📊</div>
                  <div className="stat-value">{statistics.total_activities}</div>
                  <div className="stat-label">Total Activities</div>
                </div>
                
                <div className="stat-card">
                  <div className="stat-icon">👥</div>
                  <div className="stat-value">{statistics.unique_users}</div>
                  <div className="stat-label">Unique Users</div>
                </div>
                
                <div className="stat-card">
                  <div className="stat-icon">🔐</div>
                  <div className="stat-value">{statistics.total_logins}</div>
                  <div className="stat-label">Total Logins</div>
                </div>
              </div>

              <div className="stats-tables">
                <div className="stats-table-container">
                  <h3>Top Activities</h3>
                  <table className="stats-table">
                    <thead>
                      <tr>
                        <th>Action</th>
                        <th>Count</th>
                      </tr>
                    </thead>
                    <tbody>
                      {statistics.activities_by_action.map((item, index) => (
                        <tr key={index}>
                          <td>{item.action}</td>
                          <td><strong>{item.count}</strong></td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <div className="stats-table-container">
                  <h3>Most Active Users</h3>
                  <table className="stats-table">
                    <thead>
                      <tr>
                        <th>Username</th>
                        <th>Activities</th>
                      </tr>
                    </thead>
                    <tbody>
                      {statistics.activities_by_user.map((item, index) => (
                        <tr key={index}>
                          <td>{item.username}</td>
                          <td><strong>{item.count}</strong></td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <div className="stats-table-container">
                  <h3>Recent Logins</h3>
                  <table className="stats-table">
                    <thead>
                      <tr>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>Time</th>
                      </tr>
                    </thead>
                    <tbody>
                      {statistics.recent_logins.map((item, index) => (
                        <tr key={index}>
                          <td>{item.username}</td>
                          <td>{item.ip_address}</td>
                          <td>{formatDate(item.created_at)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          ) : null}
        </div>
      )}
    </div>
  );
};

export default AuditLog;
