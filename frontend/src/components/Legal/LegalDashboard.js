import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import ChartVisualization from '../ChartVisualization';
import '../../styles/LegalDashboard.css';

const LegalDashboard = () => {
  const navigate = useNavigate();
  const [caseStats, setCaseStats] = useState(null);
  const [clientStats, setClientStats] = useState(null);
  const [analytics, setAnalytics] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      const [cases, clients, analyticsData] = await Promise.all([
        legalApi.cases.getStatistics(),
        legalApi.clients.getStatistics(),
        legalApi.analytics.getDashboard()
      ]);

      setCaseStats(cases.data);
      setClientStats(clients.data);
      setAnalytics(analyticsData.data);
      setLoading(false);
    } catch (error) {
      console.error('Error loading dashboard:', error);
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="legal-dashboard">
        <div className="loading">Loading Legal Dashboard...</div>
      </div>
    );
  }

  return (
    <div className="legal-dashboard">
      <div className="dashboard-header">
        <h1>⚖️ Legal Management System</h1>
        <p></p>
      </div>

      {/* Quick Actions */}
      <div className="quick-actions">
        <div className="action-card" onClick={() => navigate('/legal/cases')}>
          <div className="icon">📋</div>
          <h3>Case Management</h3>
          <p>View and manage legal cases</p>
        </div>
        
        <div className="action-card" onClick={() => navigate('/legal/clients')}>
          <div className="icon">👥</div>
          <h3>Client Management</h3>
          <p>Manage client information</p>
        </div>
        
        <div className="action-card" onClick={() => navigate('/legal/contracts')}>
          <div className="icon">📄</div>
          <h3>Contract Register</h3>
          <p>Manage tenders and contracts</p>
        </div>
        
        <div className="action-card" onClick={() => navigate('/legal/court-schedule')}>
          <div className="icon">📅</div>
          <h3>Court Schedule</h3>
          <p>Manage hearings and deadlines</p>
        </div>
        
        <div className="action-card" onClick={() => navigate('/legal/analytics')}>
          <div className="icon">📊</div>
          <h3>Analytics</h3>
          <p>View performance metrics</p>
        </div>
        
        <div className="action-card" onClick={() => navigate('/legal/demand-notes')}>
          <div className="icon">📋</div>
          <h3>Demand Notes</h3>
          <p>Manage payment demand notes</p>
        </div>
      </div>

      {/* Quick Stats */}
      <div className="stats-grid">
        <div className="stat-card primary">
          <div className="stat-icon">📂</div>
          <div className="stat-content">
            <h3>{caseStats?.total_cases || 0}</h3>
            <p>Total Cases</p>
          </div>
        </div>

        <div className="stat-card success">
          <div className="stat-icon">👥</div>
          <div className="stat-content">
            <h3>{clientStats?.total_clients || 0}</h3>
            <p>Active Clients</p>
          </div>
        </div>

        <div className="stat-card warning">
          <div className="stat-icon">⚠️</div>
          <div className="stat-content">
            <h3>{caseStats?.urgent_cases || 0}</h3>
            <p>Urgent Cases</p>
          </div>
        </div>

      </div>

      {/* Cases by Status - Pie Chart */}
      <ChartVisualization
        title="📊 Cases by Status Distribution"
        data={[
          { label: 'Active', count: analytics?.cases_by_status?.Active || 0 },
          { label: 'Pending', count: analytics?.cases_by_status?.Pending || 0 },
          { label: 'Completed', count: analytics?.cases_by_status?.Completed || 0 },
          { label: 'On Hold', count: analytics?.cases_by_status?.['On Hold'] || 0 }
        ].filter(item => item.count > 0)}
        defaultType="pie"
      />

      {/* Lawyer Performance - Histogram */}
      <ChartVisualization
        title="👨‍⚖️ Lawyer Performance by Cases"
        data={analytics?.lawyer_performance?.map(lawyer => ({
          label: lawyer.name,
          count: lawyer.cases || 0
        })) || []}
        defaultType="histogram"
      />

      {/* Revenue Analysis - Pie Chart */}
      <ChartVisualization
        title="💰 Revenue Distribution by Lawyer"
        data={analytics?.lawyer_performance?.map(lawyer => ({
          label: lawyer.name,
          count: lawyer.revenue || 0
        })).filter(item => item.count > 0) || []}
        defaultType="pie"
      />

      {/* Case Types Analysis - Histogram */}
      <ChartVisualization
        title="📋 Case Types Distribution"
        data={analytics?.case_types ? Object.entries(analytics.case_types).map(([type, count]) => ({
          label: type.charAt(0).toUpperCase() + type.slice(1),
          count: count
        })) : [
          { label: 'Civil', count: Math.floor(Math.random() * 20) + 5 },
          { label: 'Criminal', count: Math.floor(Math.random() * 15) + 3 },
          { label: 'Corporate', count: Math.floor(Math.random() * 25) + 8 },
          { label: 'Family', count: Math.floor(Math.random() * 12) + 2 },
          { label: 'Contract', count: Math.floor(Math.random() * 18) + 6 }
        ]}
        defaultType="histogram"
      />

      {/* Quick Actions */}
      <div className="dashboard-section">
        <h2>🚀 Quick Actions</h2>
        <div className="quick-actions">
          <a href="/legal/cases" className="action-button">
            📂 View All Cases
          </a>
          <a href="/legal/clients" className="action-button">
            👥 Manage Clients
          </a>
          <a href="/legal/court-schedule" className="action-button">
            ⚖️ Court Schedule
          </a>
        </div>
      </div>
    </div>
  );
};

export default LegalDashboard;
