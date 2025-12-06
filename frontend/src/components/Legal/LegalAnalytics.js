import React, { useState, useEffect } from 'react';
import legalApi from '../../services/legalApi';

const LegalAnalytics = () => {
  const [analytics, setAnalytics] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadAnalytics();
  }, []);

  const loadAnalytics = async () => {
    try {
      const response = await legalApi.analytics.getDashboard();
      setAnalytics(response.data);
      setLoading(false);
    } catch (error) {
      console.error('Error:', error);
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div style={{padding: '20px'}}>
      <h1>📊 Legal Analytics</h1>

      <div style={{display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '30px', marginTop: '30px'}}>
        <div style={{padding: '20px', background: '#fff', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)'}}>
          <h2>Cases by Status</h2>
          {Object.entries(analytics?.cases_by_status || {}).map(([status, count]) => (
            <div key={status} style={{display: 'flex', justifyContent: 'space-between', padding: '10px 0', borderBottom: '1px solid #e5e7eb'}}>
              <span>{status}</span>
              <strong>{count}</strong>
            </div>
          ))}
        </div>

        <div style={{padding: '20px', background: '#fff', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)'}}>
          <h2>Cases by Type</h2>
          {Object.entries(analytics?.cases_by_type || {}).map(([type, count]) => (
            <div key={type} style={{display: 'flex', justifyContent: 'space-between', padding: '10px 0', borderBottom: '1px solid #e5e7eb'}}>
              <span>{type}</span>
              <strong>{count}</strong>
            </div>
          ))}
        </div>
      </div>

      <div style={{marginTop: '30px', padding: '20px', background: '#fff', borderRadius: '8px', boxShadow: '0 2px 4px rgba(0,0,0,0.1)'}}>
        <h2>👨‍⚖️ Lawyer Performance</h2>
        <table style={{width: '100%', borderCollapse: 'collapse', marginTop: '20px'}}>
          <thead>
            <tr style={{background: '#f3f4f6'}}>
              <th style={{padding: '12px', textAlign: 'left'}}>Lawyer</th>
              <th style={{padding: '12px', textAlign: 'center'}}>Hours</th>
              <th style={{padding: '12px', textAlign: 'center'}}>Cases</th>
              <th style={{padding: '12px', textAlign: 'right'}}>Revenue</th>
            </tr>
          </thead>
          <tbody>
            {analytics?.lawyer_performance?.map((lawyer, idx) => (
              <tr key={idx} style={{borderBottom: '1px solid #e5e7eb'}}>
                <td style={{padding: '12px'}}>{lawyer.name}</td>
                <td style={{padding: '12px', textAlign: 'center'}}>{lawyer.hours}</td>
                <td style={{padding: '12px', textAlign: 'center'}}>{lawyer.cases}</td>
                <td style={{padding: '12px', textAlign: 'right'}}>Tsh {lawyer.revenue?.toLocaleString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default LegalAnalytics;
