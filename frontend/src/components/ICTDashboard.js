import React, { useState, useEffect } from 'react';
import API from '../services/api';
import ChartVisualization from './ChartVisualization';
import './ICTDashboard.css';

const ICTDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('overview');
  const [reportType, setReportType] = useState('summary');

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await API.get('/ict-dashboard');
      
      if (response.data.success) {
        setDashboardData(response.data.data);
      } else {
        setError('Failed to fetch dashboard data');
      }
    } catch (err) {
      setError('Error loading dashboard: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchReport = async (type) => {
    try {
      const response = await API.get(`/ict-dashboard/reports?type=${type}`);
      return response.data.data;
    } catch (err) {
      console.error('Failed to fetch report:', err);
      return null;
    }
  };

  const StatCard = ({ title, value, subtitle, color = 'blue', icon }) => (
    <div className={`stat-card ${color}`}>
      <div className="stat-icon">{icon}</div>
      <div className="stat-content">
        <div className="stat-value">{value}</div>
        <div className="stat-title">{title}</div>
        {subtitle && <div className="stat-subtitle">{subtitle}</div>}
      </div>
    </div>
  );

  const AlertCard = ({ title, count, items, color = 'warning' }) => (
    <div className={`alert-card ${color}`}>
      <div className="alert-header">
        <h3>{title}</h3>
        <span className="alert-count">{count}</span>
      </div>
      <div className="alert-items">
        {items && items.slice(0, 5).map((item, index) => (
          <div key={index} className="alert-item">
            <span className="alert-item-name">{item.asset_number || item.description}</span>
            <span className="alert-item-status">{item.status || item.condition}</span>
          </div>
        ))}
        {items && items.length > 5 && (
          <div className="alert-more">+{items.length - 5} more</div>
        )}
      </div>
    </div>
  );

  const ChartCard = ({ title, data, type = 'bar' }) => (
    <div className="chart-card">
      <h3>{title}</h3>
      <div className="chart-content">
        {data && data.map((item, index) => (
          <div key={index} className="chart-item">
            <div className="chart-label">{item.category || item.condition || item.department || item.brand}</div>
            <div className="chart-bar">
              <div 
                className="chart-fill" 
                style={{ width: `${item.percentage || (item.count / Math.max(...data.map(d => d.count)) * 100)}%` }}
              ></div>
            </div>
            <div className="chart-value">{item.count}</div>
          </div>
        ))}
      </div>
    </div>
  );

  if (loading) {
    return (
      <div className="ict-dashboard-container">
        <div className="loading-spinner">
          <div className="spinner"></div>
          <p>Loading ICT Dashboard...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="ict-dashboard-container">
        <div className="error-message">
          <h2>Error Loading Dashboard</h2>
          <p>{error}</p>
          <button onClick={fetchDashboardData} className="retry-button">
            Retry
          </button>
        </div>
      </div>
    );
  }

  if (!dashboardData) {
    return (
      <div className="ict-dashboard-container">
        <div className="no-data">
          <h2>No Dashboard Data Available</h2>
          <p>Please add some assets to see the dashboard.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="ict-dashboard-container">
      <div className="dashboard-header">
        <h1>ICT Asset Management Dashboard</h1>
        <div className="dashboard-actions">
          <button onClick={fetchDashboardData} className="refresh-button">
            🔄 Refresh
          </button>
        </div>
      </div>

      {/* Navigation Tabs */}
      <div className="dashboard-tabs">
        <button 
          className={`tab-button ${activeTab === 'overview' ? 'active' : ''}`}
          onClick={() => setActiveTab('overview')}
        >
          📊 Overview
        </button>
        <button 
          className={`tab-button ${activeTab === 'alerts' ? 'active' : ''}`}
          onClick={() => setActiveTab('alerts')}
        >
          ⚠️ Alerts
        </button>
        <button 
          className={`tab-button ${activeTab === 'reports' ? 'active' : ''}`}
          onClick={() => setActiveTab('reports')}
        >
          📋 Reports
        </button>
        <button 
          className={`tab-button ${activeTab === 'analytics' ? 'active' : ''}`}
          onClick={() => setActiveTab('analytics')}
        >
          📈 Analytics
        </button>
      </div>

      {/* Overview Tab */}
      {activeTab === 'overview' && (
        <div className="dashboard-content">
          {/* Key Statistics */}
          <div className="stats-grid">
            <StatCard
              title="Total Assets"
              value={dashboardData.overview?.total_assets || 0}
              subtitle={`${dashboardData.overview?.active_percentage || 0}% active`}
              color="blue"
              icon="💻"
            />
            <StatCard
              title="Critical Assets"
              value={dashboardData.overview?.critical_assets || 0}
              subtitle="Require attention"
              color="red"
              icon="🚨"
            />
            <StatCard
              title="Maintenance Due"
              value={dashboardData.overview?.maintenance_due || 0}
              subtitle="Next 30 days"
              color="orange"
              icon="🔧"
            />
          </div>

          {/* Charts Row */}
          <div className="charts-row">
            {dashboardData.condition_analysis && (
              <ChartVisualization
                title="Condition Analysis"
                data={dashboardData.condition_analysis.map(item => ({
                  label: item.condition || item.category,
                  count: item.count,
                  percentage: item.percentage
                }))}
                defaultType="pie"
              />
            )}
          </div>

          {/* Department and Brand Analysis */}
          <div className="analysis-row">
            {dashboardData.department_distribution && (
              <ChartVisualization
                title="Department Distribution"
                data={dashboardData.department_distribution.map(item => ({
                  label: item.department || item.category,
                  count: item.count
                }))}
                defaultType="histogram"
              />
            )}
            
            {dashboardData.brand_analysis && (
              <ChartVisualization
                title="Brand Analysis"
                data={dashboardData.brand_analysis.map(item => ({
                  label: item.brand || item.category,
                  count: item.count
                }))}
                defaultType="histogram"
              />
            )}
          </div>

          {/* Recent Activities */}
          <div className="recent-activities">
            <h3>Recent Activities</h3>
            <div className="activities-list">
              {dashboardData.recent_activities?.map((activity, index) => (
                <div key={index} className="activity-item">
                  <div className="activity-icon">
                    {activity.action === 'Created' ? '➕' : '✏️'}
                  </div>
                  <div className="activity-content">
                    <div className="activity-title">
                      {activity.action} {activity.asset_number}
                    </div>
                    <div className="activity-description">
                      {activity.description}
                    </div>
                    <div className="activity-meta">
                      by {activity.updated_by} • {new Date(activity.updated_at).toLocaleDateString()}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Alerts Tab */}
      {activeTab === 'alerts' && (
        <div className="dashboard-content">
          <div className="alerts-grid">
            <AlertCard
              title="Critical Assets"
              count={dashboardData.critical_assets?.length || 0}
              items={dashboardData.critical_assets}
              color="red"
            />
            <AlertCard
              title="Maintenance Alerts"
              count={dashboardData.maintenance_alerts?.total_alerts || 0}
              items={[]}
              color="orange"
            />
            <AlertCard
              title="Network Assets"
              count={dashboardData.network_assets?.length || 0}
              items={dashboardData.network_assets}
              color="blue"
            />
          </div>
        </div>
      )}

      {/* Reports Tab */}
      {activeTab === 'reports' && (
        <div className="dashboard-content">
          <div className="reports-section">
            <div className="report-filters">
              <h3>Generate Reports</h3>
              <div className="report-buttons">
                <button 
                  className={`report-button ${reportType === 'summary' ? 'active' : ''}`}
                  onClick={() => setReportType('summary')}
                >
                  📊 Summary Report
                </button>
                <button 
                  className={`report-button ${reportType === 'maintenance' ? 'active' : ''}`}
                  onClick={() => setReportType('maintenance')}
                >
                  🔧 Maintenance Report
                </button>
                <button 
                  className={`report-button ${reportType === 'department' ? 'active' : ''}`}
                  onClick={() => setReportType('department')}
                >
                  🏢 Department Report
                </button>
              </div>
            </div>
            
            <div className="report-content">
              <p>Select a report type above to generate detailed reports.</p>
              <p>Reports can be exported to CSV or PDF format.</p>
            </div>
          </div>
        </div>
      )}

      {/* Analytics Tab */}
      {activeTab === 'analytics' && (
        <div className="dashboard-content">
          <div className="analytics-grid">
            {/* Status Distribution Chart */}
            {dashboardData.status_distribution && (
              <ChartVisualization
                title="Status Distribution"
                data={Object.entries(dashboardData.status_distribution).map(([status, count]) => ({
                  label: status.charAt(0).toUpperCase() + status.slice(1),
                  count: count
                }))}
                defaultType="pie"
              />
            )}

            {/* Condition Analysis Chart */}
            {dashboardData.condition_analysis && (
              <ChartVisualization
                title="Condition Analysis"
                data={dashboardData.condition_analysis.map(item => ({
                  label: item.condition || item.category,
                  count: item.count,
                  percentage: item.percentage
                }))}
                defaultType="histogram"
              />
            )}

            <div className="status-overview">
              <h3>Status Overview</h3>
              <div className="status-grid">
                {Object.entries(dashboardData.status_distribution || {}).map(([status, count]) => (
                  <div key={status} className="status-item">
                    <span className="status-label">{status}</span>
                    <span className="status-count">{count}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="condition-overview">
              <h3>Condition Overview</h3>
              <div className="condition-grid">
                {dashboardData.condition_analysis?.map((item, index) => (
                  <div key={index} className="condition-item">
                    <span className="condition-label">{item.condition}</span>
                    <span className="condition-count">{item.count}</span>
                    <span className="condition-percentage">({item.percentage}%)</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ICTDashboard;
