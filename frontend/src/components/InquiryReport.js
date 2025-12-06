import React, { useState, useEffect } from 'react';
import inquiryService from '../services/inquiryService';
import './InquiryReport.css';

const InquiryReport = () => {
  const [inquiries, setInquiries] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchInquiries();
  }, []);

  const fetchInquiries = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await inquiryService.getInquiries();
      
      if (response.success) {
        setInquiries(response.data);
      } else {
        setError('Failed to fetch inquiries');
      }
    } catch (err) {
      setError('Error loading inquiries: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="inquiry-report-container">
        <div className="loading-spinner">
          <div className="spinner"></div>
          <p>Loading inquiries...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="inquiry-report-container">
      <div className="report-header">
        <h1 className="report-title">Inquiry Report</h1>
      </div>

      {/* Error Message */}
      {error && (
        <div className="error-message">
          <strong>Error:</strong> {error}
        </div>
      )}

      {/* Inquiry Table */}
      <div className="table-container">
        <table className="inquiry-table">
          <thead>
            <tr>
              <th>Client Name</th>
              <th>Company</th>
              <th>Inquiry Description</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {inquiries.length === 0 ? (
              <tr>
                <td colSpan="4" className="no-data">
                  No inquiries found.
                </td>
              </tr>
            ) : (
              inquiries.map((inquiry) => (
                <tr key={inquiry.id}>
                  <td className="client-name">{inquiry.client_name}</td>
                  <td className="company">{inquiry.company}</td>
                  <td className="description">{inquiry.description}</td>
                  <td className="status">
                    <span className={`status-badge ${inquiry.status}`}>
                      {inquiry.status}
                    </span>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default InquiryReport;
