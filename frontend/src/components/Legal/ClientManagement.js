import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/LegalComponents.css';

const ClientManagement = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [clients, setClients] = useState([]);
  const [statistics, setStatistics] = useState(null);
  const [selectedClient, setSelectedClient] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadClients();
    loadStatistics();
  }, []);

  // Refresh when location changes (e.g., navigating back from create page)
  useEffect(() => {
    console.log('🔄 ClientManagement: Location changed, refreshing clients...', location.pathname);
    if (location.pathname === '/legal/clients') {
      loadClients();
      loadStatistics();
    }
  }, [location]);

  // Add a window focus listener to refresh data when user returns
  useEffect(() => {
    const handleFocus = () => {
      console.log('🔄 ClientManagement: Window focused, refreshing clients...');
      loadClients();
      loadStatistics();
    };

    window.addEventListener('focus', handleFocus);
    
    // Also refresh when component becomes visible (for navigation)
    const handleVisibilityChange = () => {
      if (!document.hidden) {
        console.log('🔄 ClientManagement: Page visible, refreshing clients...');
        loadClients();
        loadStatistics();
      }
    };
    
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      window.removeEventListener('focus', handleFocus);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, []);

  const loadClients = async () => {
    try {
      console.log('🔍 ClientManagement loading clients...');
      const response = await legalApi.clients.getAll();
      console.log('📋 ClientManagement clients response:', response);
      
      // Handle paginated response - backend returns {data: [...], current_page, ...}
      const clientsData = response.data?.data || response.data || [];
      console.log('✅ ClientManagement clients data:', clientsData);
      
      setClients(Array.isArray(clientsData) ? clientsData : []);
      setLoading(false);
    } catch (error) {
      console.error('🚨 ClientManagement error loading clients:', error);
      setClients([]);
      setLoading(false);
    }
  };

  const loadStatistics = async () => {
    try {
      console.log('📊 ClientManagement loading statistics...');
      const response = await legalApi.clients.getStatistics();
      console.log('📊 ClientManagement statistics response:', response);
      setStatistics(response.data);
    } catch (error) {
      console.error('🚨 ClientManagement error loading statistics:', error);
    }
  };

  const getStatusColor = (status) => {
    const colors = {
      'Active': 'success',
      'VIP': 'primary',
      'Inactive': 'secondary'
    };
    return colors[status] || 'default';
  };

  if (loading) {
    return <div className="loading">Loading clients...</div>;
  }

  return (
    <div className="client-management">
      <div className="page-header">
        <h1>👥 Client Management</h1>
        <div style={{display: 'flex', gap: '10px'}}>
          <button 
            className="btn btn-secondary"
            onClick={() => {
              setLoading(true);
              loadClients();
              loadStatistics();
            }}
            disabled={loading}
            title="Refresh client list"
          >
            {loading ? '⏳' : '🔄'} Refresh
          </button>
          <button 
            className="btn btn-primary"
            onClick={() => navigate('/legal/clients/create')}
          >
            + New Client
          </button>
        </div>
      </div>

      {/* Statistics */}
      {statistics && (
        <div className="stats-row">
          <div className="stat-box">
            <h3>{statistics.total_clients}</h3>
            <p>Total Clients</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.active_clients}</h3>
            <p>Active Clients</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.vip_clients}</h3>
            <p>VIP Clients</p>
          </div>
          <div className="stat-box">
            <h3>{statistics.new_this_month}</h3>
            <p>New This Month</p>
          </div>
        </div>
      )}

      {/* Clients Grid */}
      <div className="clients-grid">
        {clients.map(client => (
          <div key={client.id} className="client-card">
            <div className="client-header">
              <h3>{client.name}</h3>
              <div style={{display: 'flex', gap: '8px'}}>
                <span className={`badge badge-${getStatusColor(client.status)}`}>{client.status}</span>
              </div>
            </div>
            <p><strong>Email:</strong> {client.email}</p>
            <p><strong>Phone:</strong> {client.phone}</p>
            {client.client_since && <p><strong>Client Since:</strong> {client.client_since}</p>}
            {client.active_cases !== undefined && <p><strong>Active Cases:</strong> {client.active_cases}</p>}
            {client.outstanding_balance && (
              <p><strong>Outstanding:</strong> Tsh {typeof client.outstanding_balance === 'number' ? client.outstanding_balance.toLocaleString() : parseFloat(client.outstanding_balance || 0).toLocaleString()}</p>
            )}
            
            <div style={{display: 'flex', gap: '8px', marginTop: '15px', paddingTop: '15px', borderTop: '1px solid #e5e7eb'}}>
              <button 
                onClick={() => navigate(`/legal/clients/edit/${client.id}`)}
                style={{
                  flex: 1,
                  padding: '8px',
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
                onClick={() => navigate('/legal/schedule-meeting')}
                style={{
                  flex: 1,
                  padding: '8px',
                  background: '#3b82f6',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  fontSize: '14px'
                }}
              >
                📅 Schedule
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Client Detail Modal */}
      {selectedClient && (
        <div className="modal-overlay" onClick={() => setSelectedClient(null)}>
          <div className="modal-content large" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <div>
                <h2>{selectedClient.name}</h2>
                <span className={`badge badge-${getStatusColor(selectedClient.status)}`}>
                  {selectedClient.status}
                </span>
              </div>
              <button className="close-btn" onClick={() => setSelectedClient(null)}>×</button>
            </div>
            <div className="modal-body">
              <div className="client-detail-grid">
                <div className="detail-section">
                  <h4>Contact Information</h4>
                  <p><strong>Email:</strong> {selectedClient.email}</p>
                  <p><strong>Phone:</strong> {selectedClient.phone}</p>
                  <p><strong>Address:</strong> {selectedClient.address}</p>
                  <p><strong>Client Since:</strong> {selectedClient.client_since}</p>
                </div>
                <div className="detail-section">
                  <h4>Case Information</h4>
                  <p><strong>Active Cases:</strong> {selectedClient.active_cases}</p>
                  <p><strong>Total Cases:</strong> {selectedClient.total_cases}</p>
                  <p><strong>Outstanding Balance:</strong> ${selectedClient.outstanding_balance?.toLocaleString()}</p>
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button className="btn btn-secondary" onClick={() => setSelectedClient(null)}>Close</button>
              <button className="btn btn-outline">View Cases</button>
              <button className="btn btn-outline">Schedule Meeting</button>
              <button className="btn btn-primary">Edit Client</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ClientManagement;
