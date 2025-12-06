import React, { useState, useEffect } from 'react';
import legalApi from '../../services/legalApi';

const BillingDashboard = () => {
  const [invoices, setInvoices] = useState([]);
  const [summary, setSummary] = useState(null);
  const [timeEntries, setTimeEntries] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadBillingData();
  }, []);

  const loadBillingData = async () => {
    try {
      const [invoicesRes, summaryRes, timeRes] = await Promise.all([
        legalApi.billing.getInvoices(),
        legalApi.billing.getSummary(),
        legalApi.billing.getTimeEntries()
      ]);
      setInvoices(invoicesRes.data || []);
      setSummary(summaryRes.data);
      setTimeEntries(timeRes.data || []);
      setLoading(false);
    } catch (error) {
      console.error('Error:', error);
      setLoading(false);
    }
  };

  if (loading) return <div className="loading">Loading...</div>;

  return (
    <div className="billing-dashboard" style={{padding: '20px'}}>
      <h1>💰 Billing & Finance</h1>
      
      <div style={{display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '20px', margin: '20px 0'}}>
        <div style={{padding: '20px', background: '#f0f9ff', borderRadius: '8px'}}>
          <h3>${summary?.monthly_revenue?.toLocaleString()}</h3>
          <p>Monthly Revenue</p>
        </div>
        <div style={{padding: '20px', background: '#fff7ed', borderRadius: '8px'}}>
          <h3>${summary?.outstanding_balance?.toLocaleString()}</h3>
          <p>Outstanding</p>
        </div>
        <div style={{padding: '20px', background: '#f0fdf4', borderRadius: '8px'}}>
          <h3>${summary?.collected_this_month?.toLocaleString()}</h3>
          <p>Collected</p>
        </div>
        <div style={{padding: '20px', background: '#faf5ff', borderRadius: '8px'}}>
          <h3>{summary?.total_billable_hours}</h3>
          <p>Billable Hours</p>
        </div>
      </div>

      <h2>Recent Invoices</h2>
      <table style={{width: '100%', borderCollapse: 'collapse', marginTop: '20px'}}>
        <thead>
          <tr style={{background: '#f3f4f6'}}>
            <th style={{padding: '12px', textAlign: 'left'}}>Invoice #</th>
            <th style={{padding: '12px', textAlign: 'left'}}>Client</th>
            <th style={{padding: '12px', textAlign: 'right'}}>Amount</th>
            <th style={{padding: '12px', textAlign: 'center'}}>Status</th>
            <th style={{padding: '12px', textAlign: 'left'}}>Due Date</th>
          </tr>
        </thead>
        <tbody>
          {invoices.map(invoice => (
            <tr key={invoice.id} style={{borderBottom: '1px solid #e5e7eb'}}>
              <td style={{padding: '12px'}}>{invoice.invoice_number}</td>
              <td style={{padding: '12px'}}>{invoice.client_name}</td>
              <td style={{padding: '12px', textAlign: 'right'}}>${invoice.amount?.toLocaleString()}</td>
              <td style={{padding: '12px', textAlign: 'center'}}>
                <span style={{padding: '4px 12px', borderRadius: '12px', background: invoice.status === 'Paid' ? '#d1fae5' : '#fed7aa'}}>
                  {invoice.status}
                </span>
              </td>
              <td style={{padding: '12px'}}>{invoice.due_date}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <h2 style={{marginTop: '40px'}}>Today's Time Entries</h2>
      <div style={{marginTop: '20px'}}>
        {timeEntries.map((entry, idx) => (
          <div key={idx} style={{padding: '15px', background: '#f9fafb', borderRadius: '8px', marginBottom: '10px'}}>
            <strong>{entry.lawyer}</strong> - {entry.case} ({entry.hours} hours)
            <p style={{margin: '5px 0 0 0', color: '#6b7280'}}>{entry.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default BillingDashboard;
