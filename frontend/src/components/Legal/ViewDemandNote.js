import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import './ViewDemandNote.css';

const ViewDemandNote = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [demandNote, setDemandNote] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [paymentData, setPaymentData] = useState({
    payment_amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    receipt_number: '',
    notes: ''
  });

  useEffect(() => {
    fetchDemandNote();
  }, [id]);

  const fetchDemandNote = async () => {
    try {
      setLoading(true);
      setError('');
      
      const response = await legalApi.demandNotes.getById(id);
      
      if (response.success) {
        setDemandNote(response.data);
      }
    } catch (err) {
      setError('Failed to fetch demand note: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  const handlePaymentSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const response = await legalApi.demandNotes.addPayment(id, paymentData);
      
      if (response.success) {
        alert('Payment added successfully! Payment history updated.');
        setShowPaymentForm(false);
        setPaymentData({
          payment_amount: '',
          payment_date: new Date().toISOString().split('T')[0],
          payment_method: '',
          receipt_number: '',
          notes: ''
        });
        // Refresh the demand note data to show updated payments and balance
        await fetchDemandNote();
      }
    } catch (err) {
      alert('Failed to add payment: ' + err.message);
    }
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-TZ', {
      style: 'currency',
      currency: 'TZS'
    }).format(amount);
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
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

  if (loading) {
    return <div className="loading-spinner">Loading demand note details...</div>;
  }

  if (error) {
    return <div className="error-message">{error}</div>;
  }

  if (!demandNote) {
    return <div className="error-message">Demand note not found</div>;
  }

  return (
    <div className="view-demand-note-container">
      <div className="view-header">
        <div>
          <h1>📋 Demand Note Details</h1>
          <p className="demand-note-number">{demandNote.demand_note_number || 'No number assigned'}</p>
        </div>
        <div className="header-actions">
          <button onClick={() => navigate('/legal/demand-notes')} className="btn-back">
            ← Back to List
          </button>
          <button onClick={() => navigate(`/legal/demand-notes/edit/${id}`)} className="btn-edit">
            ✏️ Edit
          </button>
          {demandNote.current_status !== 'paid' && demandNote.current_status !== 'cancelled' && (
            <button onClick={() => setShowPaymentForm(!showPaymentForm)} className="btn-payment">
              💵 {showPaymentForm ? 'Cancel Payment' : 'Add Payment'}
            </button>
          )}
        </div>
      </div>

      <div className="status-banner">
        <span className={`status-badge-large ${getStatusBadgeClass(demandNote.current_status)}`}>
          {demandNote.current_status ? demandNote.current_status.replace('_', ' ').toUpperCase() : 'PENDING'}
        </span>
      </div>

      {/* Payment Form */}
      {showPaymentForm && (
        <div className="payment-form-section">
          <h3>Add Payment</h3>
          <form onSubmit={handlePaymentSubmit} className="payment-form">
            <div className="form-row">
              <div className="form-group">
                <label>Payment Amount (TZS) *</label>
                <input
                  type="number"
                  value={paymentData.payment_amount}
                  onChange={(e) => setPaymentData({...paymentData, payment_amount: e.target.value})}
                  required
                  min="0.01"
                  step="0.01"
                  placeholder="Enter amount"
                />
              </div>
              
              <div className="form-group">
                <label>Payment Date *</label>
                <input
                  type="date"
                  value={paymentData.payment_date}
                  onChange={(e) => setPaymentData({...paymentData, payment_date: e.target.value})}
                  required
                />
              </div>
            </div>
            
            <div className="form-row">
              <div className="form-group">
                <label>Payment Method</label>
                <input
                  type="text"
                  value={paymentData.payment_method}
                  onChange={(e) => setPaymentData({...paymentData, payment_method: e.target.value})}
                  placeholder="e.g., Bank Transfer, Cash, Check"
                />
              </div>
              
              <div className="form-group">
                <label>Receipt Number</label>
                <input
                  type="text"
                  value={paymentData.receipt_number}
                  onChange={(e) => setPaymentData({...paymentData, receipt_number: e.target.value})}
                  placeholder="Receipt/Transaction number"
                />
              </div>
            </div>
            
            <div className="form-group">
              <label>Notes</label>
              <textarea
                value={paymentData.notes}
                onChange={(e) => setPaymentData({...paymentData, notes: e.target.value})}
                rows="2"
                placeholder="Additional payment notes..."
              />
            </div>
            
            <div className="form-actions">
              <button type="button" onClick={() => setShowPaymentForm(false)} className="btn-cancel">
                Cancel
              </button>
              <button type="submit" className="btn-submit">
                ✓ Add Payment
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="details-grid">
        {/* Financial Summary */}
        <div className="detail-card financial-summary">
          <h3>💰 Financial Summary</h3>
          <div className="summary-row">
            <span>Amount Claimed:</span>
            <strong className="amount-claimed">{formatCurrency(demandNote.amount_claimed)}</strong>
          </div>
          <div className="summary-row">
            <span>Late Fee:</span>
            <strong>{formatCurrency(demandNote.late_fee || 0)}</strong>
          </div>
          <div className="summary-row summary-divider">
            <span>Total Due:</span>
            <strong>{formatCurrency(parseFloat(demandNote.amount_claimed) + parseFloat(demandNote.late_fee || 0))}</strong>
          </div>
          <div className="summary-row">
            <span>Amount Paid:</span>
            <strong className="amount-paid">{formatCurrency(demandNote.amount_paid || 0)}</strong>
          </div>
          <div className="summary-row summary-highlight">
            <span>Balance Due:</span>
            <strong className="balance-due">{formatCurrency(demandNote.balance_due || 0)}</strong>
          </div>
        </div>

        {/* Client Information */}
        <div className="detail-card">
          <h3>👤 Client Information</h3>
          <div className="detail-row">
            <label>Client Name:</label>
            <span>{demandNote.client_name || 'N/A'}</span>
          </div>
          <div className="detail-row">
            <label>Client Number:</label>
            <span>{demandNote.client_number || 'N/A'}</span>
          </div>
          <div className="detail-row">
            <label>Claim Reference:</label>
            <span>{demandNote.claim_reference || 'N/A'}</span>
          </div>
        </div>

        {/* Claim Details */}
        <div className="detail-card">
          <h3>📝 Claim Details</h3>
          <div className="detail-row">
            <label>Nature of Claim:</label>
            <span className="nature-badge">{demandNote.nature_of_claim || 'N/A'}</span>
          </div>
          <div className="detail-row">
            <label>Agency of Claim:</label>
            <span>{demandNote.agency_of_claim || 'N/A'}</span>
          </div>
          <div className="detail-row">
            <label>Due Date:</label>
            <span className="due-date">{formatDate(demandNote.due_date)}</span>
          </div>
          <div className="detail-row">
            <label>Issued Date:</label>
            <span>{formatDate(demandNote.issued_date)}</span>
          </div>
        </div>

        {/* Legal Information */}
        <div className="detail-card full-width">
          <h3>⚖️ Legal Notice & Settlement</h3>
          <div className="detail-row">
            <label>Notice to Institute Suit:</label>
            <p>{demandNote.notice_to_institute_suit || 'No notice recorded'}</p>
          </div>
          <div className="detail-row">
            <label>Time Given to Settle:</label>
            <span>{demandNote.time_given_to_settle || 'N/A'}</span>
          </div>
          <div className="detail-row">
            <label>Settlement/Action Taken:</label>
            <p>{demandNote.settlement_action_taken || 'No actions recorded'}</p>
          </div>
        </div>

        {/* Remarks */}
        {demandNote.remarks && (
          <div className="detail-card full-width">
            <h3>📌 Remarks</h3>
            <p>{demandNote.remarks}</p>
          </div>
        )}

        {/* Payment History */}
        <div className="detail-card full-width">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }}>
            <h3>💳 Payment History</h3>
            <div style={{ fontSize: '14px', color: '#666' }}>
              Total Payments: {demandNote.payments ? demandNote.payments.length : 0}
            </div>
          </div>
          {demandNote.payments && demandNote.payments.length > 0 ? (
            <div className="payments-list">
              <div style={{ marginBottom: '15px', padding: '10px', backgroundColor: '#f8f9fa', borderRadius: '5px' }}>
                <strong>Payment Summary:</strong> {formatCurrency(demandNote.amount_paid || 0)} paid out of {formatCurrency(demandNote.amount_claimed || 0)}
                {demandNote.balance_due > 0 && (
                  <span style={{ color: '#dc3545', marginLeft: '10px' }}>
                    (Balance: {formatCurrency(demandNote.balance_due)})
                  </span>
                )}
              </div>
              <table className="payments-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Receipt No.</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  {demandNote.payments.map((payment, index) => (
                    <tr key={payment.id || index}>
                      <td>{index + 1}</td>
                      <td>{formatDate(payment.payment_date)}</td>
                      <td className="payment-amount">{formatCurrency(payment.payment_amount)}</td>
                      <td>{payment.payment_method || 'N/A'}</td>
                      <td>{payment.receipt_number || 'N/A'}</td>
                      <td>{payment.notes || '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div style={{ textAlign: 'center', padding: '20px', color: '#666' }}>
              <p className="no-payments">No payments recorded yet</p>
              <p style={{ fontSize: '14px', marginTop: '10px' }}>
                Use the "Add Payment" button above to record the first payment.
              </p>
            </div>
          )}
        </div>

        {/* System Information */}
        <div className="detail-card full-width system-info">
          <h3>ℹ️ System Information</h3>
          <div className="info-grid">
            <div className="detail-row">
              <label>Created By:</label>
              <span>{demandNote.creator ? `${String(demandNote.creator.fname || '')} ${String(demandNote.creator.lname || '')}`.trim() : 'N/A'}</span>
            </div>
            <div className="detail-row">
              <label>Created At:</label>
              <span>{formatDate(demandNote.created_at)}</span>
            </div>
            <div className="detail-row">
              <label>Last Updated:</label>
              <span>{formatDate(demandNote.updated_at)}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ViewDemandNote;
