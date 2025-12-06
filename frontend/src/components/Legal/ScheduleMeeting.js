import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import legalApi from '../../services/legalApi';
import '../../styles/CreateCase.css';

const ScheduleMeeting = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  const [formData, setFormData] = useState({
    title: '',
    client_name: '',
    meeting_date: '',
    meeting_time: '',
    duration: '60',
    location: '',
    meeting_type: 'Meeting',
    attendees: '',
    agenda: '',
    notes: ''
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      if (!formData.title || !formData.meeting_date || !formData.meeting_time) {
        setError('Title, Date, and Time are required');
        setLoading(false);
        return;
      }

      // Prepare data for backend
      const scheduleData = {
        event_type: formData.meeting_type,
        title: formData.title,  // Backend expects 'title', not 'event_title'
        event_date: formData.meeting_date,
        event_time: formData.meeting_time,
        duration_minutes: parseInt(formData.duration),  // Backend expects 'duration_minutes'
        location: formData.location || null,
        description: formData.agenda || null,  // Use agenda as description
        notes: `${formData.notes || ''}\nClient: ${formData.client_name || 'N/A'}\nAttendees: ${formData.attendees || 'N/A'}`,
        status: 'Scheduled'
      };

      // Call real API
      const response = await legalApi.courtSchedules.create(scheduleData);
      
      if (response.success) {
        setSuccess(`Meeting "${formData.title}" scheduled successfully!`);
        setLoading(false);
        
        setTimeout(() => {
          navigate('/legal/court-schedule');
        }, 1500);
      } else {
        setError(response.message || 'Failed to schedule meeting');
        setLoading(false);
      }

    } catch (err) {
      console.error('Error scheduling meeting:', err);
      setError(err.message || 'An error occurred while scheduling the meeting');
      setLoading(false);
    }
  };

  const handleCancel = () => {
    navigate(-1);
  };

  return (
    <div className="create-case-container">
      <div className="create-case-header">
        <h1>📅 Schedule Meeting</h1>
        <p>Create a new meeting or consultation</p>
      </div>

      {error && (
        <div className="alert alert-error">
          ❌ {error}
        </div>
      )}

      {success && (
        <div className="alert alert-success">
          ✅ {success}
        </div>
      )}

      <form onSubmit={handleSubmit} className="case-form">
        {/* Title */}
        <div className="form-group">
          <label>Meeting Title <span className="required">*</span></label>
          <input
            type="text"
            name="title"
            value={formData.title}
            onChange={handleChange}
            placeholder="e.g., Client Consultation - Property Case"
            required
          />
          <small>Brief description of the meeting</small>
        </div>

        {/* Client and Type */}
        <div className="form-row">
          <div className="form-group">
            <label>Client Name</label>
            <input
              type="text"
              name="client_name"
              value={formData.client_name}
              onChange={handleChange}
              placeholder="e.g., John Smith"
            />
          </div>

          <div className="form-group">
            <label>Meeting Type</label>
            <select
              name="meeting_type"
              value={formData.meeting_type}
              onChange={handleChange}
            >
              <option value="Meeting">Meeting</option>
              <option value="Hearing">Hearing</option>
              <option value="Court Appearance">Court Appearance</option>
              <option value="Mediation">Mediation</option>
              <option value="Arbitration">Arbitration</option>
              <option value="Filing Deadline">Filing Deadline</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        {/* Date and Time */}
        <div className="form-row">
          <div className="form-group">
            <label>Meeting Date <span className="required">*</span></label>
            <input
              type="date"
              name="meeting_date"
              value={formData.meeting_date}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label>Meeting Time <span className="required">*</span></label>
            <input
              type="time"
              name="meeting_time"
              value={formData.meeting_time}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        {/* Duration and Location */}
        <div className="form-row">
          <div className="form-group">
            <label>Duration (minutes)</label>
            <select
              name="duration"
              value={formData.duration}
              onChange={handleChange}
            >
              <option value="30">30 minutes</option>
              <option value="60">1 hour</option>
              <option value="90">1.5 hours</option>
              <option value="120">2 hours</option>
              <option value="180">3 hours</option>
            </select>
          </div>

          <div className="form-group">
            <label>Location</label>
            <input
              type="text"
              name="location"
              value={formData.location}
              onChange={handleChange}
              placeholder="e.g., Conference Room A, Zoom"
            />
          </div>
        </div>

        {/* Attendees */}
        <div className="form-group">
          <label>Attendees</label>
          <input
            type="text"
            name="attendees"
            value={formData.attendees}
            onChange={handleChange}
            placeholder="e.g., Sarah Johnson, Mike Davis, Client"
          />
          <small>Comma-separated list of attendees</small>
        </div>

        {/* Agenda */}
        <div className="form-group">
          <label>Agenda</label>
          <textarea
            name="agenda"
            value={formData.agenda}
            onChange={handleChange}
            placeholder="Meeting agenda and topics to discuss..."
            rows="4"
          />
          <small>Main topics and objectives</small>
        </div>

        {/* Notes */}
        <div className="form-group">
          <label>Notes</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            placeholder="Additional notes or preparation required..."
            rows="3"
          />
        </div>

        {/* Form Actions */}
        <div className="form-actions">
          <button
            type="button"
            onClick={handleCancel}
            className="btn btn-secondary"
            disabled={loading}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? '⏳ Scheduling...' : '✅ Schedule Meeting'}
          </button>
        </div>
      </form>

      {/* Help Section */}
      <div className="help-section">
        <h3>📌 Tips for Scheduling</h3>
        <ul>
          <li><strong>Title:</strong> Be clear and specific about the meeting purpose</li>
          <li><strong>Duration:</strong> Allow buffer time for discussions</li>
          <li><strong>Location:</strong> Specify physical address or virtual link</li>
          <li><strong>Agenda:</strong> Prepare topics in advance for productive meetings</li>
          <li><strong>Attendees:</strong> Notify all participants in advance</li>
        </ul>
      </div>
    </div>
  );
};

export default ScheduleMeeting;
